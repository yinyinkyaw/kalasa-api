<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Contact;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contacts = Contact::select('id', 'name', 'email', 'message')->latest()->paginate(10);

        if(!empty($contacts->items())) {
            return $this->successResponse('Get data successfully', $contacts);
        } else {
            return $this->nullResponse("There's no contact data", NULL);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'message' => 'required|string'
        ]);
        Contact::create($data);

        return $this->successResponse('Data Created successfully', NULL);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $contact = Contact::select('id', 'name', 'email', 'message')->find($id);
        if($contact) {
            return $this->successResponse('Get data successfully', $contact);
        } else {
            return $this->nullResponse("There's no contact data", NULL);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $contact = Contact::find($id);

        if($contact) {
            $contact->delete();
            return $this->successResponse('Contact data deleted successfully', NULL);
        } else {
            return $this->failResponse("There's no data to delete", NULL);
        }
    }

    // search by name
    public function searchByName() {
        $contacts = Contact::select('id', 'name', 'email', 'message')
                                ->where('name', 'ILIKE', "%" . request('q') . "%")
                                ->paginate(10);

        if(!empty($contacts->items())) {
            return $this->successResponse('Get contact data successfully', $contacts);
        } else {
            return $this->nullResponse("There's no contact data", NULL);
        }
    }

    // total count
    public function total() {
        $total = count(Contact::all());

        return $this->successResponse('Get data successfully', $total);
    }

    // filter by date
    public function filterByDate(Request $request) {
        $contacts = Contact::query();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if(!empty($request->input()) && $startDate != NULL && $endDate != NULL) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $contacts = filter_by_date($contacts, $startDate, $endDate);
            if(!empty($contacts->items())) {
                return $this->successResponse('Get contact data successfully', $contacts);
            } else {
                return $this->nullResponse("There's no contact data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key required", NULL);
        }
    }

    // sort by name
    public function sortByName(Request $request) {
        $contacts = Contact::query();
        $key = $request->q;
        if($key != NULL) {
            $contacts = sort_by_name($contacts, $key);
            if(!empty($contacts->items())) {
                return $this->successResponse('Get contact data successfully', $contacts);
            } else {
                return $this->nullResponse("There's no contact data", NULL);
            }
        }else {
            return $this->validatorErrorResponse("Search key required", NULL);
        }
    }

    // delete all
    public function multipleDelete(Request $request) {
        $ids = $request->input('ids');
        $ids = explode(',', $ids);
        if(!empty($request->input()) && $ids != NULL) {
            Contact::whereIn('id', $ids)->delete();
            return $this->successResponse('Contact data deleted successfully', NULL);
        }else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }
}
