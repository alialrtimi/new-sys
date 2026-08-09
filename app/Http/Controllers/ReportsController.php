<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        // 1. جلب البيانات المتاحة من قاعدة البيانات (استعلام سريع ولحظي)
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
            ->get();

        // جلب قائمة شاملة بجميع أسماء الأقسام الفريدة المتاحة في النظام لضمان تعبئة الأشهر الفارغة بها
        $allDepartments = DB::table('departments')->pluck('name')->unique()->values();

        // في حال كان جدول الأقسام فارغاً، نأخذ الأقسام التي ظهرت في الطلبات كخيار احتياطي
        if ($allDepartments->isEmpty()) {
            $allDepartments = $data->pluck('department_name')->unique()->values();
        }

        // تجميع البيانات المرتجعة بأسلوب المجموعات المتداخلة (مفاتيح تسهل البحث السريع جداً بـ O(1) )
        $groupedData = $data->groupBy(['year', 'month', 'department_name']);

        $grandTotal = 0; // متغير لحساب الإجمالي الكلي العام لكافة السنوات
        $finalYearlyResult = collect();

        // تحديد نطاق السنوات ثابت يبدأ من 2020 وينتهي عند السنة الحالية تلقائياً (2026)
        $startYear = 2020;
        $currentYear = (int) date('Y'); // سيجلب 2026 ديناميكياً ويتحدث تلقائياً في السنوات القادمة

        // 2. إعادة الهيكلة عبر حلقة السنوات لضمان توليد السنة الحالية حتى لو كانت أصفاراً
        for ($year = $startYear; $year <= $currentYear; $year++) {

            $fullMonths = [];
            $yearTotal = 0; // متغير لتجميع إجمالي السنة الحالية

            // حلقة ثابتة تدور على الأشهر الـ 12 بالكامل
            for ($month = 1; $month <= 12; $month++) {

                $monthTotal = 0; // متغير لتجميع إجمالي الشهر الحالي لكل الأقسام
                $departmentsData = [];

                // نمر على كل الأقسام المتاحة في النظام لضمان تعبئتها بالكامل
                foreach ($allDepartments as $deptName) {
                    $totalRequests = 0;

                    // التحقق المباشر والسريع إذا كانت هناك بيانات مسجلة لهذا القسم في هذا الشهر ولهذه السنة بالتحديد
                    if (isset($groupedData[$year][$month][$deptName])) {
                        $item = $groupedData[$year][$month][$deptName]->first();
                        $totalRequests = (int) $item->all_request;
                    }

                    $monthTotal += $totalRequests; // إضافة إنتاجية القسم إلى إجمالي الشهر

                    $departmentsData[] = [
                        'name' => $deptName,
                        'stats' => [
                            'total' => $totalRequests,
                        ]
                    ];
                }

                // تجميع إجمالي هذا الشهر في إجمالي السنة الكلي
                $yearTotal += $monthTotal;

                // إضافة بيانات الشهر بعد الحساب مع إجمالي الشهر والأقسام
                $fullMonths[] = [
                    'month'       => $month,
                    'total_month' => $monthTotal,
                    'departments' => $departmentsData
                ];
            }

            // تجميع إجمالي السنة الحالي في الإجمالي الكلي العام للنظام
            $grandTotal += $yearTotal;

            // دفع بيانات السنة المكتملة إلى المجموعة النهائية
            $finalYearlyResult->push([
                'year'       => $year,
                'total_year' => $yearTotal,
                'months'     => $fullMonths
            ]);
        }

        // ترتيب مصفوفة السنوات تنازلياً ليظهر العام الحالي (2026) في المقدمة دائماً بداخل قائمة الفلاتر والمخططات
        $monthlyAndyearlyResult = $finalYearlyResult->sortByDesc('year')->values();

        // 3. إرجاع النتيجة كـ JSON متناسق تماماً مع المخططات والجداول في الواجهة
        return Inertia::render('Reports', [
            'monthlyAndyearlyResult' => [
                'data' => $monthlyAndyearlyResult,
                'grandTotal' => $grandTotal
            ],
        ]);
    }
}
