<?php

namespace App\Http\Controllers\Api;

use App\Models\Artist;
use App\Models\Artwork;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class ArtistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $artists = Artist::select('id', 'name', 'profile_image', 'description', 'status', 'created_at')->latest()->paginate(10);
        foreach ($artists as $artist) {
            $artworks = Artwork::where('artist_id', $artist->id);
            $artist->total_artwork =$artworks->count() ?? 0;
            $artist->sold_artwork = $artworks->where('status', 0)->count() ?? 0;
            $artist->added_date = $artist->created_at->format('d/m/y');
            $artist->image = asset('storage/'.$artist->profile_image);
        }

        if(!empty($artists->items())) {
            return $this->successResponse('Get data successfully', $artists);
        } else {
            return $this->nullResponse("There's no artist data", NULL);
        }
    }

    public function enduserArtistList() {
        $artists = Artist::select('id', 'name', 'profile_image', 'description', 'status', 'created_at')->where('status', 1)->latest()->paginate(10);

        foreach ($artists as $artist) {
            $artworks = Artwork::where('artist_id', $artist->id);
            $artist->total_artwork =$artworks->count() ?? 0;
            $artist->sold_artwork = $artworks->where('status', 0)->count() ?? 0;
            $artist->added_date = $artist->created_at->format('d/m/y');
            $artist->image = asset('storage/'.$artist->profile_image);
        }

        if(!empty($artists->items())) {
            return $this->successResponse('Get data successfully', $artists);
        } else {
            return $this->nullResponse("There's no artist data", NULL);
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

        $fileName = image_store('artist_profile_image', $request->file('profile_image'));
        $fields['profile_image'] = $fileName;
        $artist = Artist::create($fields);

        return $this->successResponse('Artist created successfully', $artist);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $artist = Artist::find($id);
        if($artist) {
            return $this->successResponse('Get data successfully', $artist);
        } else {
            return $this->nullResponse("There's no artist data", NULL);
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

    public function updateArtist(Request $request, $id) {
        $fields = $this->FormValidation($request);
        $artist = Artist::find($id);
        if($artist) {
            if(image_delete('/artist_profile_image/', $artist->profile_image)) {
                $fileName = image_store('artist_profile_image', $request->file('profile_image'));
                $artist->name = $request->name;
                $artist->profile_image = $fileName;
                $artist->description = $request->description;
                $artist->save();
                return $this->successResponse('Artist data updated successfully', $artist);
            }else{
                return $this->failResponse('Artist Image Error! Please Contact to Backend Team', NULL);
            }
        } else {
            return $this->nullResponse("There's no artist data", NULL);
        }
    }

    public function statusUpdate($id, Request $request) {
        $request->validate(['status' => 'required|integer']);
        $artist = Artist::find($id);
        if($artist) {
            $artist->status = $request->status;
            $artist->save();
            return $this->successResponse('Artist status updated successfully', $artist);
        }else {
            return $this->nullResponse("There's no artist data", NULL);
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
        $artist = Artist::find($id);
        if($artist) {
            $artist->delete();
            return $this->successResponse('Artist data deleted successfully', NULL);
        } else {
            return $this->nullResponse("There's no artist data", NULL);
        }
    }

    public function total() {
        $total = count(Artist::all());

        return $this->successResponse('Get data successfully', $total);
    }

    public function searchByName(Request $request) {
        $artists = Artist::where('name', 'ILIKE', "%" . request('q') . "%")->paginate(10);

        if(!empty($artists->items())) {
            return $this->successResponse('Get artist data successfully', $artists);
        } else {
            return $this->nullResponse("There's no artist data", NULL);
        }
    }

    public function enduserSearchByName(Request $request) {
        $artists = Artist::where('status', 1)
                        ->where('name', 'ILIKE', "%" . request('q') . "%")->paginate(10);

        if(!empty($artists->items())) {
            return $this->successResponse('Get artist data successfully', $artists);
        } else {
            return $this->nullResponse("There's no artist data", NULL);
        }
    }

    public function sortByName(Request $request) {
        $artists = Artist::query();
        $key = $request->q;
        if($key != NULL) {
            $artists = Artist::OrderBy('name', $key)->paginate(10);
            if(!empty($artists->items())) {
                return $this->successResponse('Get artist data successfully', $artists);
            } else {
                return $this->nullResponse("There's no artist data", NULL);
            }
        }else {
            return $this->validatorErrorResponse("Search key required", NULL);
        }
    }


    public function filterByDate(Request $request) {
        $artists = Artist::query();
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        if(!empty($request->input()) && $startDate != NULL && $endDate != NULL) {
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            $artists = filter_by_date($artists, $startDate, $endDate);
            if(!empty($artists->items())) {
                return $this->successResponse('Get artist data successfully', $artists);
            } else {
                return $this->nullResponse("There's no artist data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key required", NULL);
        }
    }

    public function multipleDelete(Request $request) {
        $ids = $request->input('ids');
        $ids = explode(',', $ids);
        if(!empty($request->input()) && $ids != NULL) {
            Artist::whereIn('id', $ids)->delete();
            return $this->successResponse('Artist data deleted successfully', NULL);
        }else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }


    // form validation
    private function FormValidation($request) {
        $validators = $request->validate([
            'name' => 'required|string|min:2|max:50',
            'profile_image' => 'required|image|mimes:png,jpg,jpeg,svg,gif,jfif,pjpeg,pjp,avif',
            'description' => 'required|string|min:4',
            'status' => 'required|in:0,1',
        ]);

        return $validators;
    }
}
