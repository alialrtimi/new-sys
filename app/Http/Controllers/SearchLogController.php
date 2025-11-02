<?php

namespace App\Http\Controllers;

use App\Models\SearchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SearchLogController extends Controller
{
    public function index(Request $request)
    {

        $search = $request->only([
            'partial_case_number',
            'criminal_case_number',
            'name',
            'mother_name',
            'date_of_birth',
            'job',
            'address',
            'charges',
            'judgment_date',
            'judgment_operative',
            'inserted_way',
        ]);

        // dd($search['criminal_case_number']);
        if (Auth::user()->type == 'admin'  || Auth::user()->type == 'super_admin') {
            $people = SearchLog::query()
                ->when($search['partial_case_number'] ?? null, fn($q) => $q->where('partial_case_number', 'like', '%' . $search['partial_case_number'] . '%'))
                ->when($search['criminal_case_number'] ?? null, fn($q) => $q->where('criminal_case_number', 'like', '%' . $search['criminal_case_number'] . '%'))
                ->when($search['name'] ?? null, fn($q) => $q->where('name', 'like', '%' . $search['name'] . '%'))
                ->when($search['mother_name'] ?? null, fn($q) => $q->where('mother_name', 'like', '%' . $search['mother_name'] . '%'))
                ->when($search['date_of_birth'] ?? null, fn($q) => $q->where('date_of_birth', 'like', '%' . $search['date_of_birth'] . '%'))
                ->when($search['job'] ?? null, fn($q) => $q->where('job', 'like', '%' . $search['job'] . '%'))
                ->when($search['address'] ?? null, fn($q) => $q->where('address', 'like', '%' . $search['address'] . '%'))
                ->when($search['charges'] ?? null, fn($q) => $q->where('charges', 'like', '%' . $search['charges'] . '%'))
                ->when($search['judgment_date'] ?? null, fn($q) => $q->where('judgment_date', 'like', '%' . $search['judgment_date'] . '%'))
                ->when($search['judgment_operative'] ?? null, fn($q) => $q->where('judgment_operative', 'like', '%' . $search['judgment_operative'] . '%'))
                ->when($search['inserted_way'] ?? null, fn($q) => $q->where('inserted_way', 'like', '%' . $search['inserted_way'] . '%'))
                ->with(['user'])
                ->paginate(10)
                ->appends($search);
        }







        return inertia('SearchLog', [
            'people' => $people,
            'search' => $search,
        ]);
    }
}
