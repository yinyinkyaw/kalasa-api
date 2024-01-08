<?php

namespace App\Http\Controllers\Api;

use App\Models\Artist;
use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class ArtworkController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $artworks = Artwork::with('artist')->latest()->paginate(10);

        foreach ($artworks as $artwork) {
            $artwork->image = asset('storage/'.$artwork->image);
        }

        if(!empty($artworks->items())) {
            return $this->successResponse('Get data successfully', $artworks);
        } else {
            return $this->nullResponse("There's no artwork data", NULL);
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
        $fields = $this->FormValidation($request);
        $fields['image']  = image_store('artwork_image', $request->file('image'));
        $artwork = Artwork::create($fields);

        return $this->successResponse('Artwork created successfully', $artwork);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $artwork = Artwork::find($id);
        if($artwork) {
            return $this->successResponse('Get data successfully', $artwork);
        } else {
            return $this->nullResponse("There's no artwork data", NULL);
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

    public function updateArtwork(Request $request, $id) {
        $fields = $this->FormValidation($request);
        $artwork = Artwork::find($id);
        if($artwork) {
            if(image_delete('/artwork_image/', $artwork->image)) {
                $fields['image'] = image_store('artwork_image', $request->file('image'));
                $artwork = Artwork::update($fields);
                return $this->successResponse('Artwork updated successfully', $artwork);
            }else{
                return $this->failResponse('Artwork Image Error! Please Contact to Backend Team', NULL);
            }
        } else {
            return $this->nullResponse("There's no artwork data", NULL);
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
        $artwork = Artwork::find($id);
        if($artwork) {
            if(image_delete('/artwork_image/', $artwork->image)) {
                $artwork->delete();
                return $this->successResponse('Artwork data deleted successfully', NULL);
            }else{
                return $this->failResponse('Artwork Image Error! Please Contact to Backend Team', NULL);
            }
        }else {
            return $this->nullResponse("There's no artwork data", NULL);
        }
    }

    public function total() {
        $total = count(Artwork::all());

        return $this->successResponse('Get data successfully', $total);
    }

    public function statusUpdate($id, Request $request) {
        $request->validate(['status' => 'required|integer']);
        $artwork = Artwork::find($id);
        if($artwork) {
            $artwork->status = $request->status;
            $artwork->save();
            return $this->successResponse('Artwork status updated successfully', $artwork);
        }else {
            return $this->nullResponse("There's no artwork data", NULL);
        }
    }

    public function searchByName(Request $request) {
        $artworks = Artwork::where('name', 'ILIKE', "%" . request('search') . "%")->paginate(10);

        if(!empty($artworks->items())) {
            return $this->successResponse('Get artwork data successfully', $artworks);
        } else {
            return $this->nullResponse("There's no artwork data", NULL);
        }
    }

    public function getByArtist(Artist $artist) {
        $artworks = Artwork::where('artist_id', $artist->id)->paginate(10);

        if(!empty($artworks->items())) {
            return $this->successResponse('Get artwork data successfully', $artworks);
        } else {
            return $this->nullResponse("There's no artwork data", NULL);
        }
    }

    public function filterByDate(Request $request) {
        $artworks = Artwork::query();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if(!empty($request->input()) && $startDate != NULL && $endDate != NULL) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $artworks = filter_by_date($artworks, $startDate, $endDate);
            if(!empty($artworks->items())) {
                return $this->successResponse('Get artwork data successfully', $artworks);
            } else {
                return $this->nullResponse("There's no artwork data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key required", NULL);
        }
    }

    public function sortByName(Request $request) {
        $artworks = Artwork::query();
        $key = $request->key;
        if($key != NULL) {
            // $artworks = sort_by_name($artworks, $key);
            $artworks = $artworks->orderBy('name', $key)->paginate(10);
            if(!empty($artworks->items())) {
                return $this->successResponse('Get artwork data successfully', $artworks);
            } else {
                return $this->nullResponse("There's no artwork data", NULL);
            }
        }else {
            return $this->validatorErrorResponse("Search key required", NULL);
        }
    }

    public function getByCategory($category) {
        $artworks = Artwork::where('category_id', $category)->latest()->paginate(10);
        if(!empty($artworks->items())) {
            return $this->successResponse('Get data successfully', $artworks);
        } else {
            return $this->nullResponse("There's no artwork data", NULL);
        }
    }

    public function multipleDelete(Request $request) {
        $ids = $request->input('ids');
        $ids = explode(',', $ids);
        if(!empty($request->input()) && $ids != NULL) {
            Artwork::whereIn('id', $ids)->delete();
            return $this->successResponse('Artwork data deleted successfully', NULL);
        }else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }


    private function FormValidation($request) {
        $validators = $request->validate([
            'name' => 'required|string|max:255',
            'image' => 'required|image|mimes:png,jpg,jpeg,svg,gif,jfif,pjpeg,pjp,avif',
            'artist_id' => 'required|exists:artists,id',
            'year' => 'required|integer',
            'category_id' => 'required|integer|exists:artwork_categories,id',
            'price' => 'required|integer',
            'size' => 'required|string|max:255',
            'description' => 'required|string',
        ]);
        return $validators;
    }
}
