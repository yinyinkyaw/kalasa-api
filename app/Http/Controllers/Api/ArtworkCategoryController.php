<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\ArtworkCategory;
use App\Http\Controllers\Controller;

class ArtworkCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = ArtworkCategory::select('id', 'name')->all();

        if(!empty($categories)) {
            return $this->successResponse('Get data successfully', $categories);
        } else {
            return $this->nullResponse("There's no category data", NULL);
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
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $category = ArtworkCategory::create(['name' => $request->name]);
        return $this->successResponse('Data Created successfully', $category);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $category = ArtworkCategory::find($id);
        if($category) {
            $category->name = $request->name;
            $category->save();
            return $this->successResponse('Artwork category updated successfully', $category);
        }else {
            return $this->nullResponse("There's no category data", NULL);
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
        $category = ArtworkCategory::find($id);
        if($category) {
            $category->delete();
            return $this->successResponse('Artwork category updated successfully', $category);
        }else {
            return $this->nullResponse("There's no category data", NULL);
        }
    }
}
