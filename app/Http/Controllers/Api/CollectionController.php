<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Collection;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CollectionRequest;

class CollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $collections = Collection::select('id', 'title', 'image', 'description')->latest()->paginate(10);

        foreach ($collections as $collection) {
            $collection->image = asset('storage/'.$collection->image);
        }

        if(!empty($collections->items())) {
            return $this->successResponse('Get Data Successfully', $collections);
        } else {
            return $this->nullResponse("There's no Collection Data", NULL);
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
    public function store(CollectionRequest $request)
    {
        $fileName = image_store('collection_image', $request->file('image'));

        Collection::insert([
            'title' => $request->title,
            'image' => $fileName,
            'description' => $request->description,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $this->successResponse('Data Created Successfully', NULL);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $collection = Collection::select('id', 'title', 'image', 'description')->find($id);

        if(!empty($collection)) {
            return $this->successResponse('Data Found', $collection);
        } else {
            return $this->failResponse("There's no data", NULL);
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
    public function update(CollectionRequest $request, $id)
    {
        $collection = Collection::find($id);

        if(!empty($collection)) {
            image_delete('/collection_image/', $collection->image);

            $fileName = image_store('/collection_image/', $request->file('image'));

            $collection->update([
                'title' => $request->title,
                'image' => $fileName,
                'description' => $request->description,
                'updated_at' => Carbon::now(),
            ]);

            return $this->successResponse('Collection Updated Successfully!', $collection);
        } else {
            return $this->nullResponse("There's no Collection Data", NULL);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $collection = Collection::find($id);

        if(!empty($collection)) {
            if(image_delete('/collection_image/', $collection->image)){

                $collection->delete();

                return $this->successResponse('Collection Deleted Successfully!', NULL);
            } else{
                return $this->failResponse('Collection Image Error! Please Contact to Backend Team', NULL);
            }
        } else {
            return nullResponse("There's no Collection Data", NULL);
        }
    }

    /**
     * Total Collection
     */
    public function total()
    {
        $total = Collection::count();

        if(!empty($total)) {
            return $this->successResponse('Total Collection Get Successfully!', $total);
        } else {
            return $this->nullResponse("There's no Collection Data", NULL);
        }
    }

    /**
     * Search blog with name
     */
    public function searchByName(Request $request)
    {
        $collections = Collection::query();
        $key = $request->input('q');

        if(!empty($request->input()) && $key != NULL) {
            $collections = search_by_name($collections, $key);

            if(!empty($collections->items())) {
                return $this->successResponse('Collection Found Successfully!', $collections);
            } else {
                return $this->nullResponse("There's no Collection Data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * Filter by date
     */
    public function filterByDate(Request $request)
    {
        $collections = Collection::query();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if(!empty($request->input()) && $startDate != NULL && $endDate != NULL) {
            $collections = filter_by_date($collections, $startDate, $endDate);

            if(!empty($collections->items())) {
                return $this->successResponse('Blog Found Successfully!', $collections);
            } else {
                return $this->nullResponse("There's no Blog Data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * Sort by name
     */
    public function sortByName(Request $request)
    {
        $collections = Collection::query();
        $key = $request->input('q');

        if(!empty($request->input()) && $key != NULL) {
            $collections = sort_by_name($collections, $key);

            if(!empty($collections->items())) {
                return $this->successResponse('Blog Found Successfully!', $collections);
            } else {
                return $this->nullResponse("There's no Blog Data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * Delete all blogs
     */
    public function multipleDelete(Request $request)
    {
        $collections = Collection::query();

        $ids = $request->input('ids');
        $ids = explode(',', $ids);

        if(!empty($request->input()) && $ids != NULL) {
            multiple_delete($collections, $ids, '/blog_image/');

            return $this->successResponse('Multiple Collections Deleted Successfully!', NULL);
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }
}
