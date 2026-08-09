<?php

namespace App\Http\Controllers;

use App\Models\excelUpload;
use App\Models\LibyanPerson;
use App\Models\LibyanPersonRequest;
use App\Models\Note;
use App\Models\Person;
use App\Models\SearchLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class apiReportsController extends Controller
{




    public function api_rejected_personal_pictures_index(Request $request)
    {
        dd();
        $alldata = [];


        for ($year = 2020; $year <= now()->year; $year++) {

            $months = [];

            for ($month = 1; $month <= 12; $month++) {

                $from = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                $to = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

                $maleCount = LibyanPersonRequest::whereBetween('l_req_date', [$from, $to])
                    ->whereHas('libyanPerson', function ($q) {
                        $q->where('sex', '1');
                    })
                    ->count();

                $femaleCount = LibyanPersonRequest::whereBetween('l_req_date', [$from, $to])
                    ->whereHas('libyanPerson', function ($q) {
                        $q->where('sex', '2');
                    })
                    ->count();

                $months[] = [
                    'month' => $month,
                    'data' => [
                        'male' => $maleCount,
                        'female' => $femaleCount,
                    ],
                ];
            }

            $alldata[] = [
                'year' => $year,
                'months' => $months,
            ];
        }

        $result = [
            'alldata' => $alldata,
        ];

        // $reportResult = LibyanPersonRequest::where('internal_state', '>', '');
        return $result;
    }
}
