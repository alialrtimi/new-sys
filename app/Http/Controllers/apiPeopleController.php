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

class apiPeopleController extends Controller
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
                'note' => $request->note,
                'note_id' => $request->note_id,

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

    public function deleted_files(Request $request)
    {

        if ($request->page != 1 && Auth::user()->type == 'user')
            return redirect()->route('deleted-files', array_merge(request()->all(), ['page' => 1]));
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
                ->onlyTrashed()
                ->paginate(1)
                ->appends($search);
        } elseif (Auth::user()->type == 'user'  &&  request()->name != null) {
            $people = Person::query()
                ->when($search['name'] ?? null, fn($q) => $q->where('name', 'like', '%' . $search['name'] . '%'))

                ->limit(1)
                ->onlyTrashed()
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
                ->onlyTrashed()
                ->paginate(10)

                ->appends($search);
        }





        // if (Auth::user()->type == 'user')

        return inertia('DeletedFiles', [
            'people' => $people,
            'search' => $search,
        ]);
    }
    public function index(Request $request)
    {

        // if (Auth::user()->type == 'user')

        return inertia('Dashboard', [
            'people' => '',
            'search' => '',
            'notes' => ''
        ]);
    }

    public function ready_requests(Request $request)
    {




        // i ndeed fast query with eager loading and pagination
        // $rejected_personal_pictures = LibyanPersonRequest::with([
        //     'libyanPerson' => function ($query) {
        //         $query->select(
        //             'id',
        //             'ssn',
        //             'full_name',
        //             'last_name',
        //             'mother_full_name',
        //             'date_of_b',
        //             'place_of_b',
        //             'job',
        //             'doc_type',
        //             'place_of_card_id',
        //             'passport_no',
        //             'sex',
        //             // 'picture',

        //         );
        //     },
        //     'department' => function ($query) {
        //         $query->select('name');
        //     }
        // ])


        //     ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])
        //     ->where('internal_state_id', 0) // Assuming 0 is the ID for 'rejected' state

        //     ->paginate(10);

        $data = LibyanPersonRequest::query()
            ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])

            ->where('internal_state_id', 15)

            ->with([
                'libyanPerson:id,ssn,full_name,last_name,doc_type,mother_full_name,date_of_b,place_of_b,job,document_issue_place_id,passport_no,sex,address_id,document_issue_place_id',
                'libyanPerson.address:id,name',
                'libyanPerson.document_issued_place:id,name',
                'department:id,name'
            ])

            ->paginate(10);
        // dd($rejected_personal_pictures);

        return inertia('ReadyRequests', [
            'people' => $data,
            'search' => '',
            'notes' => ''
        ]);
    }

    public function waiting_for_approval(Request $request)
    {




        // i ndeed fast query with eager loading and pagination
        // $rejected_personal_pictures = LibyanPersonRequest::with([
        //     'libyanPerson' => function ($query) {
        //         $query->select(
        //             'id',
        //             'ssn',
        //             'full_name',
        //             'last_name',
        //             'mother_full_name',
        //             'date_of_b',
        //             'place_of_b',
        //             'job',
        //             'doc_type',
        //             'place_of_card_id',
        //             'passport_no',
        //             'sex',
        //             // 'picture',

        //         );
        //     },
        //     'department' => function ($query) {
        //         $query->select('name');
        //     }
        // ])


        //     ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])
        //     ->where('internal_state_id', 0) // Assuming 0 is the ID for 'rejected' state

        //     ->paginate(10);

        $data = LibyanPersonRequest::query()
            ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])

            ->where('internal_state_id', 6)

            ->with([
                'libyanPerson:id,ssn,full_name,last_name,doc_type,mother_full_name,date_of_b,place_of_b,job,document_issue_place_id,passport_no,sex,address_id,document_issue_place_id',
                'libyanPerson.address:id,name',
                'libyanPerson.document_issued_place:id,name',
                'department:id,name'
            ])

            ->paginate(10);
        // dd($rejected_personal_pictures);

        return inertia('WaitingForApproval', [
            'people' => $data,
            'search' => '',
            'notes' => ''
        ]);
    }

    public function criminal_record_office(Request $request)
    {




        // i ndeed fast query with eager loading and pagination
        // $rejected_personal_pictures = LibyanPersonRequest::with([
        //     'libyanPerson' => function ($query) {
        //         $query->select(
        //             'id',
        //             'ssn',
        //             'full_name',
        //             'last_name',
        //             'mother_full_name',
        //             'date_of_b',
        //             'place_of_b',
        //             'job',
        //             'doc_type',
        //             'place_of_card_id',
        //             'passport_no',
        //             'sex',
        //             // 'picture',

        //         );
        //     },
        //     'department' => function ($query) {
        //         $query->select('name');
        //     }
        // ])


        //     ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])
        //     ->where('internal_state_id', 0) // Assuming 0 is the ID for 'rejected' state

        //     ->paginate(10);

        $data = LibyanPersonRequest::query()
            ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])

            ->where('internal_state_id', 3)

            ->with([
                'libyanPerson:id,ssn,full_name,last_name,doc_type,mother_full_name,date_of_b,place_of_b,job,document_issue_place_id,passport_no,sex,address_id,document_issue_place_id',
                'libyanPerson.address:id,name',
                'libyanPerson.document_issued_place:id,name',
                'department:id,name'
            ])

            ->paginate(10);
        // dd($rejected_personal_pictures);

        return inertia('CriminalRecordOffice', [
            'people' => $data,
            'search' => '',
            'notes' => ''
        ]);
    }
    public function booked_for_searchers(Request $request)
    {




        // i ndeed fast query with eager loading and pagination
        // $rejected_personal_pictures = LibyanPersonRequest::with([
        //     'libyanPerson' => function ($query) {
        //         $query->select(
        //             'id',
        //             'ssn',
        //             'full_name',
        //             'last_name',
        //             'mother_full_name',
        //             'date_of_b',
        //             'place_of_b',
        //             'job',
        //             'doc_type',
        //             'place_of_card_id',
        //             'passport_no',
        //             'sex',
        //             // 'picture',

        //         );
        //     },
        //     'department' => function ($query) {
        //         $query->select('name');
        //     }
        // ])


        //     ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])
        //     ->where('internal_state_id', 0) // Assuming 0 is the ID for 'rejected' state

        //     ->paginate(10);

        $data = LibyanPersonRequest::query()
            ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])

            ->where('internal_state_id', 2)

            ->with([
                'libyanPerson:id,ssn,full_name,last_name,doc_type,mother_full_name,date_of_b,place_of_b,job,document_issue_place_id,passport_no,sex,address_id,document_issue_place_id',
                'libyanPerson.address:id,name',
                'libyanPerson.document_issued_place:id,name',
                'department:id,name'
            ])

            ->paginate(10);
        // dd($rejected_personal_pictures);

        return inertia('BookedForSearchers', [
            'people' => $data,
            'search' => '',
            'notes' => ''
        ]);
    }


    public function waiting_for_search(Request $request)
    {


        $data = LibyanPersonRequest::query()
            ->select(['id', 'libyan_person_id', 'department_id', 'internal_state_id'])

            ->where('internal_state_id', 1)

            ->with([
                'libyanPerson:id,ssn,full_name,last_name,doc_type,mother_full_name,date_of_b,place_of_b,job,document_issue_place_id,passport_no,sex,address_id,document_issue_place_id',
                'libyanPerson.address:id,name',
                'libyanPerson.document_issued_place:id,name',
                'department:id,name'
            ])

            ->simplePaginate(10);


        return inertia('WaitingForSearch', [
            'people' => $data,
            'search' => '',
            'notes' => ''
        ]);
    }
    public function api_rejected_personal_pictures_index(Request $request)
    {
        // dd();

        // return response()->json(['ok' => true]);


        // dd(strlen($request->request_search_key));
        $search = trim($request->request_search_key);





        $rejected_personal_pictures = LibyanPersonRequest::query()

            ->select([
                'id',
                'libyan_person_id',
                'department_id',
                'internal_state_id'
            ])

            ->where('internal_state_id', 0)

            ->when(
                $request->department_search_key &&
                    $request->department_search_key !== 'all',
                fn($q) => $q->where('department_id', $request->department_search_key)
            )

            ->when($search, function ($q) use ($search) {

                if ($search !== '' && strlen($search) < 9) {

                    $q->where('id', (int) $search);
                } elseif (strlen($search) === 12) {

                    $q->whereExists(function ($sub) use ($search) {
                        $sub->select(DB::raw(1))
                            ->from('libyan_people')
                            ->whereColumn('libyan_people.id', 'libyan_people_requests.libyan_person_id')
                            ->where('libyan_people.ssn', $search);
                    });
                }
            })

            ->with([
                'libyanPerson:id,ssn,full_name,last_name,address_id,document_issue_place_id,doc_type,mother_full_name,date_of_b,place_of_b,job,passport_no,sex',
                'libyanPerson.address:id,name',
                'libyanPerson.document_issued_place:id,name',
                'department:id,name',
            ]);



        // $rejected_personal_pictures_count = $rejected_personal_pictures->count('id');
        // $last_page = ceil($rejected_personal_pictures_count / 10);
        if ($request->request_search_key != '') {
            $rejected_personal_pictures = $rejected_personal_pictures->simplePaginate(1)->appends($request->all());
        } else {
            $rejected_personal_pictures = $rejected_personal_pictures->simplePaginate(10)->appends($request->all());
        }
        return ['RejectedPersonalPictures', [
            'people' => $rejected_personal_pictures,
            'search' => '',
            'notes' => '',
            // 'count' => $rejected_personal_pictures_count,
            // 'last_page' => $last_page,
        ]];
        // return inertia('RejectedPersonalPictures', [
        //     'people' => $rejected_personal_pictures,
        //     'search' => '',
        //     'notes' => ''
        // ]);
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
            'note' => $request->note,
            'note_id' => $request->note_id,
        ]);

        // Return a success response to Inertia
        return redirect()->back()->with('success', 'Person added successfully.');
    }
}
