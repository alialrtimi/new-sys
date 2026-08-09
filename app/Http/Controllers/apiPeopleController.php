<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\excelUpload;
use App\Models\LibyanPerson;
use App\Models\LibyanPersonRequest;
use App\Models\Note;
use App\Models\Person;
use App\Models\SearchLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class apiPeopleController extends Controller
{

    public function reports(Request $request)
    {
        // 1. جلب البيانات المتاحة من قاعدة البيانات (استعلام واحد سريع جداً ولحظي)
        $data = DB::table('libyan_people_requests as r')
            ->join('libyan_people as p', 'r.libyan_person_id', '=', 'p.id')
            ->join('departments as d', 'r.department_id', '=', 'd.id')
            ->select(
                DB::raw('YEAR(r.l_req_date) as year'),
                DB::raw('MONTH(r.l_req_date) as month'),
                'd.name as department_name',

                DB::raw("COUNT(r.id) as all_request")
            )
            ->where('r.l_req_date', '>=', '2020-01-01')
            ->groupBy('year', 'month', 'department_name')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get();

        // جلب قائمة شاملة بجميع الأقسام الفريدة لتعبئتها في الأشهر الفارغة
        $allDepartments = $data->pluck('department_name')->unique()->values();

        // 2. إعادة الهيكلة لتعبئة الأشهر والأقسام الفارغة بالأصفار (Zero-Filling)
        $structuredData = $data->groupBy('year')->map(function ($yearItems, $year) use ($allDepartments) {

            // تجميع بيانات السنة الحالية حسب رقم الشهر
            $monthsWithData = $yearItems->groupBy('month');
            $fullMonths = [];

            // حلقة ثابتة تدور على الأشهر الـ 12 بالكامل
            for ($month = 1; $month <= 12; $month++) {

                if ($monthsWithData->has($month)) {
                    // إذا كان الشهر يحتوي على بيانات، نمر على كل الأقسام لضمان وجودها حتى لو كانت صفرية
                    $currentMonthDepts = $monthsWithData->get($month)->keyBy('department_name');

                    $departmentsData = $allDepartments->map(function ($deptName) use ($currentMonthDepts) {

                        // الحالة أ: القسم لديه بيانات في هذا الشهر
                        if ($currentMonthDepts->has($deptName)) {
                            $item = $currentMonthDepts->get($deptName);
                            return [
                                'name' => $item->department_name,
                                'data' => [

                                    'total'                     => (int) $item->all_request,
                                ]
                            ];
                        }

                        // الحالة ب: الشهر فيه بيانات، لكن هذا القسم بالتحديد ليس لديه طلبات (الحل هنا لمنع الـ null)
                        return [
                            'name' => $deptName,
                            'stats' => [

                                'total'                     => 0,
                            ]
                        ];
                    })->values();
                } else {
                    // الحالة ج: إذا كان الشهر فارغاً تماماً في قاعدة البيانات، نعبئ كل الأقسام بالأصفار
                    $departmentsData = $allDepartments->map(function ($deptName) {
                        return [
                            'name' => $deptName,
                            'stats' => [

                                'total'                     => 0,
                            ]
                        ];
                    })->values();
                }

                $fullMonths[] = [
                    'month' => $month,
                    'departments' => $departmentsData
                ];
            }

            return [
                'year' => (int) $year,
                'months' => $fullMonths
            ];
        })->values();

        // 3. إرجاع النتيجة كـ JSON متناسق تماماً
        return response()->json($structuredData);
    }

    public function api_rejected_personal_pictures_index2(Request $request)
    {
        // 1. جلب البيانات المتاحة من قاعدة البيانات (استعلام واحد سريع جداً ولحظي)
        $data = DB::table('libyan_people_requests as r')
            ->join('libyan_people as p', 'r.libyan_person_id', '=', 'p.id')
            ->join('departments as d', 'r.department_id', '=', 'd.id')
            ->select(
                DB::raw('YEAR(r.l_req_date) as year'),
                DB::raw('MONTH(r.l_req_date) as month'),
                'd.name as department_name',

                // تقسيم الذكور حسب الحالة
                DB::raw("COUNT(CASE WHEN p.sex = '1' AND r.state = '1' THEN 1 END) as male_s1"),
                DB::raw("COUNT(CASE WHEN p.sex = '1' AND r.state = '2' THEN 1 END) as male_s2"),

                // تقسيم الإناث حسب الحالة
                DB::raw("COUNT(CASE WHEN p.sex = '2' AND r.state = '1' THEN 1 END) as female_s1"),
                DB::raw("COUNT(CASE WHEN p.sex = '2' AND r.state = '2' THEN 1 END) as female_s2"),

                // إجمالي القسم المباشر من قاعدة البيانات
                DB::raw("COUNT(r.id) as total")
            )
            ->where('r.l_req_date', '>=', '2020-01-01')
            ->groupBy('year', 'month', 'department_name')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get();

        // جلب قائمة شاملة بجميع الأقسام الفريدة لتعبئتها في الأشهر الفارغة
        $allDepartments = $data->pluck('department_name')->unique()->values();

        // 2. إعادة الهيكلة لتعبئة الأشهر والأقسام الفارغة بالأصفار وحساب المجاميع (Zero-Filling & Aggregation)
        $structuredData = $data->groupBy('year')->map(function ($yearItems, $year) use ($allDepartments) {

            // تجميع بيانات السنة الحالية حسب رقم الشهر
            $monthsWithData = $yearItems->groupBy('month');
            $fullMonths = [];

            // حلقة ثابتة تدور على الأشهر الـ 12 بالكامل
            for ($month = 1; $month <= 12; $month++) {

                $monthTotal = 0; // لحساب الإجمالي الكلي للشهر بالكامل لكل الأقسام

                if ($monthsWithData->has($month)) {
                    // إذا كان الشهر يحتوي على بيانات، نأخذ الأقسام المتاحة ونرتبها بمفتاح الاسم
                    $currentMonthDepts = $monthsWithData->get($month)->keyBy('department_name');

                    $departmentsData = $allDepartments->map(function ($deptName) use ($currentMonthDepts, &$monthTotal) {

                        // الحالة أ: القسم لديه بيانات في هذا الشهر
                        if ($currentMonthDepts->has($deptName)) {
                            $item = $currentMonthDepts->get($deptName);

                            $m1 = (int) $item->male_s1;
                            $m2 = (int) $item->male_s2;
                            $f1 = (int) $item->female_s1;
                            $f2 = (int) $item->female_s2;
                            $deptTotal = (int) $item->total;

                            $monthTotal += $deptTotal; // تجميع إجمالي الشهر

                            return [
                                'name' => $item->department_name,
                                'stats' => [
                                    'male' => [
                                        'state_1' => $m1,
                                        'state_2' => $m2,
                                        'total'   => ($m1 + $m2) // إجمالي الذكور فقط بالقسم
                                    ],
                                    'female' => [
                                        'state_1' => $f1,
                                        'state_2' => $f2,
                                        'total'   => ($f1 + $f2) // إجمالي الإناث فقط بالقسم
                                    ],
                                    'total_department' => $deptTotal // الإجمالي الكلي للقسم
                                ]
                            ];
                        } else {
                            // الحالة ب: الشهر فيه بيانات، لكن هذا القسم بالتحديد خامل (تعبئة صفرية)
                            return [
                                'name' => $deptName,
                                'stats' => [
                                    'male' => ['state_1' => 0, 'state_2' => 0, 'total' => 0],
                                    'female' => ['state_1' => 0, 'state_2' => 0, 'total' => 0],
                                    'total_department' => 0
                                ]
                            ];
                        }
                    })->values();
                } else {
                    // الحالة ج: إذا كان الشهر فارغاً تماماً في قاعدة البيانات، نعبئ كل الأقسام بالأصفار
                    $departmentsData = $allDepartments->map(function ($deptName) {
                        return [
                            'name' => $deptName,
                            'stats' => [
                                'male' => ['state_1' => 0, 'state_2' => 0, 'total' => 0],
                                'female' => ['state_1' => 0, 'state_2' => 0, 'total' => 0],
                                'total_department' => 0
                            ]
                        ];
                    })->values();
                }

                // إضافة بيانات الشهر بعد الحساب للمصفوفة الرئيسية للسنوات
                $fullMonths[] = [
                    'month'       => $month,
                    'total_month' => $monthTotal, // المجموع الفعلي لكل الحالات بكل الأقسام داخل هذا الشهر
                    'departments' => $departmentsData
                ];
            }

            return [
                'year'   => (int) $year,
                'months' => $fullMonths
            ];
        })->values();

        // 3. إرجاع النتيجة كـ JSON متناسق تماماً
        return response()->json($structuredData);
    }

    public function api_rejected_personal_pictures_index(Request $request)
    {
        // 1. جلب البيانات المتاحة من قاعدة البيانات (استعلام واحد سريع جداً ولحظي)
        $data = DB::table('libyan_people_requests as r')
            ->join('libyan_people as p', 'r.libyan_person_id', '=', 'p.id')
            ->join('departments as d', 'r.department_id', '=', 'd.id')
            ->select(
                DB::raw('YEAR(r.l_req_date) as year'),
                DB::raw('MONTH(r.l_req_date) as month'),
                'd.name as department_name',

                DB::raw("COUNT(r.internal_state_id) as total"),
                DB::raw("COUNT(CASE WHEN r.internal_state_id = '0' THEN 1 END) as rejected_personal_picture"),
                DB::raw("COUNT(CASE WHEN r.internal_state_id >= '1' AND r.internal_state_id <= 6 THEN 1 END) as waiting"),
                DB::raw("COUNT(CASE WHEN r.internal_state_id >= '15' AND r.internal_state_id <= 17 THEN 1 END) as aproved")
            )
            ->where('r.l_req_date', '>=', '2020-01-01')
            ->groupBy('year', 'month', 'department_name')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get();

        // جلب قائمة شاملة بجميع الأقسام الفريدة لتعبئتها في الأشهر الفارغة
        $allDepartments = $data->pluck('department_name')->unique()->values();

        // 2. إعادة الهيكلة لتعبئة الأشهر والأقسام الفارغة بالأصفار (Zero-Filling)
        $structuredData = $data->groupBy('year')->map(function ($yearItems, $year) use ($allDepartments) {

            // تجميع بيانات السنة الحالية حسب رقم الشهر
            $monthsWithData = $yearItems->groupBy('month');
            $fullMonths = [];

            // حلقة ثابتة تدور على الأشهر الـ 12 بالكامل
            for ($month = 1; $month <= 12; $month++) {

                if ($monthsWithData->has($month)) {
                    // إذا كان الشهر يحتوي على بيانات، نمر على كل الأقسام لضمان وجودها حتى لو كانت صفرية
                    $currentMonthDepts = $monthsWithData->get($month)->keyBy('department_name');

                    $departmentsData = $allDepartments->map(function ($deptName) use ($currentMonthDepts) {

                        // الحالة أ: القسم لديه بيانات في هذا الشهر
                        if ($currentMonthDepts->has($deptName)) {
                            $item = $currentMonthDepts->get($deptName);
                            return [
                                'name' => $item->department_name,
                                'stats' => [
                                    'rejected_personal_picture' => (int) $item->rejected_personal_picture,
                                    'waiting'                   => (int) $item->waiting,
                                    'aproved'                   => (int) $item->aproved,
                                    'total'                     => (int) $item->total,
                                ]
                            ];
                        }

                        // الحالة ب: الشهر فيه بيانات، لكن هذا القسم بالتحديد ليس لديه طلبات (الحل هنا لمنع الـ null)
                        return [
                            'name' => $deptName,
                            'stats' => [
                                'rejected_personal_picture' => 0,
                                'waiting'                   => 0,
                                'aproved'                   => 0,
                                'total'                     => 0,
                            ]
                        ];
                    })->values();
                } else {
                    // الحالة ج: إذا كان الشهر فارغاً تماماً في قاعدة البيانات، نعبئ كل الأقسام بالأصفار
                    $departmentsData = $allDepartments->map(function ($deptName) {
                        return [
                            'name' => $deptName,
                            'stats' => [
                                'rejected_personal_picture' => 0,
                                'waiting'                   => 0,
                                'aproved'                   => 0,
                                'total'                     => 0,
                            ]
                        ];
                    })->values();
                }

                $fullMonths[] = [
                    'month' => $month,
                    'departments' => $departmentsData
                ];
            }

            return [
                'year' => (int) $year,
                'months' => $fullMonths
            ];
        })->values();

        // 3. إرجاع النتيجة كـ JSON متناسق تماماً
        return response()->json($structuredData);
    }

    public function ReportOnAgeGroupCasesByMonthAndYear(Request $request)
    {
        // 1. جلب البيانات المتاحة من قاعدة البيانات (استعلام واحد شامل وسريع)
        $data = DB::table('libyan_people_requests as r')
            ->join('libyan_people as p', 'r.libyan_person_id', '=', 'p.id')
            ->join('departments as d', 'r.department_id', '=', 'd.id')
            ->select(
                DB::raw('YEAR(r.l_req_date) as year'),
                DB::raw('MONTH(r.l_req_date) as month'),
                'd.name as department_name',

                // مواليد 1920 - 1929
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1920 AND 1929 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_20_29_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1920 AND 1929 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_20_29_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1920 AND 1929 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_20_29_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1920 AND 1929 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_20_29_f_s2"),

                // مواليد 1930 - 1939
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1930 AND 1939 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_30_39_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1930 AND 1939 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_30_39_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1930 AND 1939 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_30_39_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1930 AND 1939 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_30_39_f_s2"),

                // مواليد 1940 - 1949
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1940 AND 1949 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_40_49_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1940 AND 1949 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_40_49_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1940 AND 1949 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_40_49_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1940 AND 1949 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_40_49_f_s2"),

                // مواليد 1950 - 1959
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1950 AND 1959 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_50_59_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1950 AND 1959 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_50_59_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1950 AND 1959 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_50_59_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1950 AND 1959 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_50_59_f_s2"),

                // مواليد 1960 - 1969
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1960 AND 1969 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_60_69_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1960 AND 1969 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_60_69_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1960 AND 1969 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_60_69_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1960 AND 1969 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_60_69_f_s2"),

                // مواليد 1970 - 1979
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1970 AND 1979 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_70_79_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1970 AND 1979 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_70_79_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1970 AND 1979 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_70_79_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1970 AND 1979 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_70_79_f_s2"),

                // مواليد 1980 - 1989
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1980 AND 1989 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_80_89_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1980 AND 1989 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_80_89_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1980 AND 1989 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_80_89_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1980 AND 1989 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_80_89_f_s2"),

                // مواليد 1990 - 1999
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1990 AND 1999 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_90_99_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1990 AND 1999 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_90_99_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1990 AND 1999 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_90_99_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 1990 AND 1999 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_90_99_f_s2"),

                // مواليد 2000 - 2009
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2000 AND 2009 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_00_09_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2000 AND 2009 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_00_09_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2000 AND 2009 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_00_09_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2000 AND 2009 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_00_09_f_s2"),

                // مواليد 2010 - 2019
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2010 AND 2019 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_10_19_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2010 AND 2019 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_10_19_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2010 AND 2019 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_10_19_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) BETWEEN 2010 AND 2019 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_10_19_f_s2"),

                // مواليد 2020 فما فوق
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) >= 2020 AND p.sex = '1' AND r.state = '1' THEN 1 END) as b_20_plus_m_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) >= 2020 AND p.sex = '1' AND r.state = '2' THEN 1 END) as b_20_plus_m_s2"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) >= 2020 AND p.sex = '2' AND r.state = '1' THEN 1 END) as b_20_plus_f_s1"),
                DB::raw("COUNT(CASE WHEN YEAR(p.date_of_b) >= 2020 AND p.sex = '2' AND r.state = '2' THEN 1 END) as b_20_plus_f_s2"),

                // إجمالي الطلبات الكلي
                DB::raw("COUNT(r.id) as total")
            )
            ->where('r.l_req_date', '>=', '2020-01-01')
            ->groupBy('year', 'month', 'department_name')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'asc')
            ->get();

        // جلب قائمة شاملة بجميع الأقسام الفريدة لتعبئتها في الأشهر الفارغة
        $allDepartments = $data->pluck('department_name')->unique()->values();

        // 2. إعادة الهيكلة لتعبئة الأشهر والأقسام الفارغة بالأصفار (Zero-Filling)
        $structuredData = $data->groupBy('year')->map(function ($yearItems, $year) use ($allDepartments) {

            $monthsWithData = $yearItems->groupBy('month');
            $fullMonths = [];

            for ($month = 1; $month <= 12; $month++) {

                if ($monthsWithData->has($month)) {
                    $currentMonthDepts = $monthsWithData->get($month)->keyBy('department_name');

                    $departmentsData = $allDepartments->map(function ($deptName) use ($currentMonthDepts) {

                        // الحالة أ: القسم لديه بيانات في هذا الشهر
                        if ($currentMonthDepts->has($deptName)) {
                            $item = $currentMonthDepts->get($deptName);
                            return [
                                'name' => $item->department_name,
                                'yearsGroup' => [
                                    'birth_1920_1929' => [
                                        'male'   => ['state_1' => (int) $item->b_20_29_m_s1, 'state_2' => (int) $item->b_20_29_m_s2],
                                        'female' => ['state_1' => (int) $item->b_20_29_f_s1, 'state_2' => (int) $item->b_20_29_f_s2]
                                    ],
                                    'birth_1930_1939' => [
                                        'male'   => ['state_1' => (int) $item->b_30_39_m_s1, 'state_2' => (int) $item->b_30_39_m_s2],
                                        'female' => ['state_1' => (int) $item->b_30_39_f_s1, 'state_2' => (int) $item->b_30_39_f_s2]
                                    ],
                                    'birth_1940_1949' => [
                                        'male'   => ['state_1' => (int) $item->b_40_49_m_s1, 'state_2' => (int) $item->b_40_49_m_s2],
                                        'female' => ['state_1' => (int) $item->b_40_49_f_s1, 'state_2' => (int) $item->b_40_49_f_s2]
                                    ],
                                    'birth_1950_1959' => [
                                        'male'   => ['state_1' => (int) $item->b_50_59_m_s1, 'state_2' => (int) $item->b_50_59_m_s2],
                                        'female' => ['state_1' => (int) $item->b_50_59_f_s1, 'state_2' => (int) $item->b_50_59_f_s2]
                                    ],
                                    'birth_1960_1969' => [
                                        'male'   => ['state_1' => (int) $item->b_60_69_m_s1, 'state_2' => (int) $item->b_60_69_m_s2],
                                        'female' => ['state_1' => (int) $item->b_60_69_f_s1, 'state_2' => (int) $item->b_60_69_f_s2]
                                    ],
                                    'birth_1970_1979' => [
                                        'male'   => ['state_1' => (int) $item->b_70_79_m_s1, 'state_2' => (int) $item->b_70_79_m_s2],
                                        'female' => ['state_1' => (int) $item->b_70_79_f_s1, 'state_2' => (int) $item->b_70_79_f_s2]
                                    ],
                                    'birth_1980_1989' => [
                                        'male'   => ['state_1' => (int) $item->b_80_89_m_s1, 'state_2' => (int) $item->b_80_89_m_s2],
                                        'female' => ['state_1' => (int) $item->b_80_89_f_s1, 'state_2' => (int) $item->b_80_89_f_s2]
                                    ],
                                    'birth_1990_1999' => [
                                        'male'   => ['state_1' => (int) $item->b_90_99_m_s1, 'state_2' => (int) $item->b_90_99_m_s2],
                                        'female' => ['state_1' => (int) $item->b_90_99_f_s1, 'state_2' => (int) $item->b_90_99_f_s2]
                                    ],
                                    'birth_2000_2009' => [
                                        'male'   => ['state_1' => (int) $item->b_00_09_m_s1, 'state_2' => (int) $item->b_00_09_m_s2],
                                        'female' => ['state_1' => (int) $item->b_00_09_f_s1, 'state_2' => (int) $item->b_00_09_f_s2]
                                    ],
                                    'birth_2010_2019' => [
                                        'male'   => ['state_1' => (int) $item->b_10_19_m_s1, 'state_2' => (int) $item->b_10_19_m_s2],
                                        'female' => ['state_1' => (int) $item->b_10_19_f_s1, 'state_2' => (int) $item->b_10_19_f_s2]
                                    ],
                                    'birth_2020_plus' => [
                                        'male'   => ['state_1' => (int) $item->b_20_plus_m_s1, 'state_2' => (int) $item->b_20_plus_m_s2],
                                        'female' => ['state_1' => (int) $item->b_20_plus_f_s1, 'state_2' => (int) $item->b_20_plus_f_s2]
                                    ],
                                    'total'           => (int) $item->total,
                                ]
                            ];
                        }

                        // الحالة ب: الشهر فيه بيانات، لكن هذا القسم بالتحديد خامل
                        return [
                            'name' => $deptName,
                            'yearsGroup' => [
                                'birth_1920_1929' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1930_1939' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1940_1949' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1950_1959' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1960_1969' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1970_1979' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1980_1989' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1990_1999' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_2000_2009' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_2010_2019' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_2020_plus' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'total'           => 0,
                            ]
                        ];
                    })->values();
                } else {
                    // الحالة ج: إذا كان الشهر فارغاً تماماً
                    $departmentsData = $allDepartments->map(function ($deptName) {
                        return [
                            'name' => $deptName,
                            'yearsGroup' => [
                                'birth_1920_1929' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1930_1939' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1940_1949' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1950_1959' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1960_1969' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1970_1979' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1980_1989' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_1990_1999' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_2000_2009' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_2010_2019' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'birth_2020_plus' => ['male' => ['state_1' => 0, 'state_2' => 0], 'female' => ['state_1' => 0, 'state_2' => 0]],
                                'total'           => 0,
                            ]
                        ];
                    })->values();
                }

                $fullMonths[] = [
                    'month' => $month,
                    'departments' => $departmentsData
                ];
            }

            return [
                'year' => (int) $year,
                'months' => $fullMonths
            ];
        })->values();

        return response()->json($structuredData);
    }
    // public function ReportOnAgeGroupCasesByMonthAndYear(Request $request)
    // {
    //     // 1. جلب البيانات المتاحة من قاعدة البيانات (استعلام واحد سريع جداً ولحظي)
    //     $data = DB::table('libyan_people_requests as r')
    //         ->join('libyan_people as p', 'r.libyan_person_id', '=', 'p.id')
    //         ->join('departments as d', 'r.department_id', '=', 'd.id')
    //         ->select(
    //             DB::raw('YEAR(r.l_req_date) as year'),
    //             DB::raw('MONTH(r.l_req_date) as month'),
    //             'd.name as department_name',
    //             //// هنا اريد صفوف لكل فئة عمرية مقسمة على 10 سنوات p.date_of_b
    //         )
    //         ->where('r.l_req_date', '>=', '2020-01-01')
    //         ->groupBy('year', 'month', 'department_name')
    //         ->orderBy('year', 'desc')
    //         ->orderBy('month', 'asc')
    //         ->get();

    //     // جلب قائمة شاملة بجميع الأقسام الفريدة لتعبئتها في الأشهر الفارغة
    //     $allDepartments = $data->pluck('department_name')->unique()->values();

    //     // 2. إعادة الهيكلة لتعبئة الأشهر والأقسام الفارغة بالأصفار (Zero-Filling)
    //     $structuredData = $data->groupBy('year')->map(function ($yearItems, $year) use ($allDepartments) {

    //         // تجميع بيانات السنة الحالية حسب رقم الشهر
    //         $monthsWithData = $yearItems->groupBy('month');
    //         $fullMonths = [];

    //         // حلقة ثابتة تدور على الأشهر الـ 12 بالكامل
    //         for ($month = 1; $month <= 12; $month++) {

    //             if ($monthsWithData->has($month)) {
    //                 // إذا كان الشهر يحتوي على بيانات، نمر على كل الأقسام لضمان وجودها حتى لو كانت صفرية
    //                 $currentMonthDepts = $monthsWithData->get($month)->keyBy('department_name');

    //                 $departmentsData = $allDepartments->map(function ($deptName) use ($currentMonthDepts) {

    //                     // الحالة أ: القسم لديه بيانات في هذا الشهر
    //                     if ($currentMonthDepts->has($deptName)) {
    //                         $item = $currentMonthDepts->get($deptName);
    //                         return [
    //                             'name' => $item->department_name,

    //                         ];
    //                     }

    //                     // الحالة ب: الشهر فيه بيانات، لكن هذا القسم بالتحديد ليس لديه طلبات (الحل هنا لمنع الـ null)
    //                     return [
    //                         'name' => $deptName,

    //                     ];
    //                 })->values();
    //             } else {
    //                 // الحالة ج: إذا كان الشهر فارغاً تماماً في قاعدة البيانات، نعبئ كل الأقسام بالأصفار
    //                 $departmentsData = $allDepartments->map(function ($deptName) {
    //                     return [
    //                         'name' => $deptName,

    //                     ];
    //                 })->values();
    //             }

    //             $fullMonths[] = [
    //                 'month' => $month,
    //                 'departments' => $departmentsData
    //             ];
    //         }

    //         return [
    //             'year' => (int) $year,
    //             'months' => $fullMonths
    //         ];
    //     })->values();

    //     // 3. إرجاع النتيجة كـ JSON متناسق تماماً
    //     return response()->json($structuredData);
    // }
}
