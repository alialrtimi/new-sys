<?php

namespace App\Http\Controllers;

use App\Models\excelUpload;
use App\Models\Person;
use App\Models\SearchLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class peopleController extends Controller
{
    public function edit_person(Request $request)
    {
        // dd($request->all());

        $person = Person::where('id', '=', $request->id)->get()[0];
        $person->update(
            [
                'partial_case_number' => $request->partial_case_number,
                'criminal_case_number' => $request->criminal_case_number,
                'name' => $request->name,
                'mother_name' => $request->mother_name,
                'date_of_birth' => $request->date_of_birth,
                'job' => $request->job,
                'address' => $request->address,
                'charges' => $request->charges,
                'judgment_date' => $request->judgment_date,
                'judgment_operative' => $request->judgment_operative,
            ]
        );
        return redirect()->back()->with('success', 'Person updated successfully.');
    }

    public function uploadExcel(Request $request)
    {

        if (Auth::user()->type != 'super_admin')
            abort(404);
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv|max:2048', // Accept Excel or CSV files only
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Load the Excel file and convert its content to an array
            $data = Excel::toArray([], $file);
            $data = $data[0];
            $excelUpload = excelUpload::create([]);
            // dd($data);
            for ($i = 2; $i < count($data); $i++) {
                if ($data[$i][8] != "")
                    $custom_date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($data[$i][8])->format('Y-m-d');
                else
                    $custom_date = "";
                $person = Person::create([

                    'partial_case_number' => $data[$i][0],
                    'criminal_case_number' => $data[$i][1],
                    'name' => $data[$i][2],
                    'mother_name' => $data[$i][3],
                    'date_of_birth' => $data[$i][4],
                    'job' => $data[$i][5],
                    'address' => $data[$i][6],
                    'charges' => $data[$i][7],
                    'judgment_date' => $custom_date,
                    'judgment_operative' =>  $data[$i][9],
                    'inserted_way' => 'اكسل',
                    'user_id' => Auth::user()->id,
                    'excel_upload_id' => $excelUpload->id
                ]);

                // Return a success response to Inertia
            }
            return redirect()->back()->with('success', 'تمت الإضافة بنجاح');
        }
    }


    public function restore_person($id)
    {
        if (Auth::user()->type != 'super_admin')
            abort(404);
        // $id = $request->input('id'); // استلام الـ ID من الطلب
        // dd($id);
        // البحث عن السجل وحذفه
        $person = Person::onlyTrashed()->find($id);

        if ($person) {
            $person->restore();


            return redirect()->back()->with(['message' => 'تم الإسترجاع بنجاح']);
        }

        return redirect()->back()->with(['message' => 'العنصر غير موجود']);
    }
    public function delete_person($id)
    {
        if (Auth::user()->type == 'user')
            abort(404);
        // $id = $request->input('id'); // استلام الـ ID من الطلب
        // dd($id);
        // البحث عن السجل وحذفه
        $person = Person::find($id);

        if ($person) {
            $person->delete();


            return redirect()->back()->with(['message' => 'تم الحذف بنجاح']);
        }

        return redirect()->back()->with(['message' => 'العنصر غير موجود']);
    }

    public function deleted_files()
    {

        if (Auth::user()->type == 'user')
            abort(404);
        // Retrieve all people records from the database
        $people = Person::onlyTrashed()->paginate(10);

        // Pass the data to the Inertia view
        return Inertia::render('DeletedFiles', [
            'people' => $people,
        ]);
    }
    public function index(Request $request)
    {

        if ($request->page != 1 && Auth::user()->type == 'user')
            return redirect()->route('dashboard', array_merge(request()->all(), ['page' => 1]));
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
        if (Auth::user()->type == 'user'  &&  request()->name == null) {
            $people = Person::query()
                ->when($search['name'] ?? null, fn($q) => $q->where('name', 'like', '%' . $search['name'] . '%'))
                ->when($search['inserted_way'] ?? null, fn($q) => $q->where('inserted_way', 'like', '%' . $search['inserted_way'] . '%'))
                ->where('id', '=', null)

                ->limit(1)
                ->paginate(1)
                ->appends($search);
        } elseif (Auth::user()->type == 'user'  &&  request()->name != null) {
            $people = Person::query()
                ->when($search['name'] ?? null, fn($q) => $q->where('name', 'like', '%' . $search['name'] . '%'))

                ->limit(1)
                ->paginate(1)
                ->appends($search);
        } elseif (Auth::user()->type == 'admin'  || Auth::user()->type == 'super_admin') {
            $people = Person::query()
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



        if ($search != null) {
            SearchLog::create([
                'partial_case_number' => $search['partial_case_number'],
                'criminal_case_number' => $search['criminal_case_number'],
                'name' => $search['name'],
                'mother_name' => $search['mother_name'],
                'date_of_birth' => $search['date_of_birth'],
                'job' => $search['job'],
                'address' => $search['address'],
                'charges' => $search['charges'],
                'judgment_date' => $search['judgment_date'],
                'judgment_operative' => $search['judgment_operative'],

                'user_id' => Auth::user()->id,
                'result' => $people->toJson()
            ]);
        }

        // if (Auth::user()->type == 'user')

        return inertia('Dashboard', [
            'people' => $people,
            'search' => $search,
        ]);
    }
    public function store(Request $request)
    {
        if (Auth::user()->type == 'user')
            abort(404);
        // Validate incoming request data
        $validatedData = $request->validate([
            'partial_case_number' => 'nullable|string',
            'criminal_case_number' => 'nullable|string',
            'name' => 'nullable|string',
            'mother_name' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'job' => 'nullable|string',
            'address' => 'nullable|string',
            'charges' => 'nullable|string',
            'judgment_date' => 'nullable|date',
            'judgment_operative' => 'nullable|string',

        ]);

        // Save the validated data to the 'people' table
        $person = Person::create([
            'partial_case_number' => $request->partial_case_number,
            'criminal_case_number' => $request->criminal_case_number,
            'name' => $request->name,
            'mother_name' => $request->mother_name,
            'date_of_birth' => $request->date_of_birth,
            'job' => $request->job,
            'address' => $request->address,
            'charges' => $request->charges,
            'judgment_date' => $request->judgment_date,
            'judgment_operative' => $request->judgment_operative,
            'user_id' => Auth::user()->id,
        ]);

        // Return a success response to Inertia
        return redirect()->back()->with('success', 'Person added successfully.');
    }
}
