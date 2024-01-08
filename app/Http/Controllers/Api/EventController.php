<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Services\CacheService;
use App\Http\Requests\EventRequest;
use App\Http\Controllers\Controller;

class EventController extends Controller
{

    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::select('id', 'title', 'image', 'status', 'location', 'description', 'opening_datetime', 'closing_datetime')->latest()->paginate(10);
        $cacheKey = 'events';

        $this->cacheService->storeCache($cacheKey, $events);

        $events = $this->cacheService->getCache($cacheKey);

        foreach ($events as $event) {
            $event->image = asset('storage/'.$event->image);
        }

        if(!empty($events)) {
            return $this->successResponse('Get Data Successfully', $events);
        } else {
            return $this->nullResponse("There's no Event Data", NULL);
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
    public function store(EventRequest $request)
    {
        $fileName = image_store('event_image', $request->file('image'));

        $opening_datetime = $request->opening_date . ' ' . $request->opening_time;
        $closing_datetime = $request->closing_date . ' ' . $request->closing_time;

        Event::insert([
            'title' => $request->event_name,
            'image' => $fileName,
            'status' => $request->status,
            'location' => $request->location,
            'description' => $request->description,
            'opening_datetime' => Carbon::parse($opening_datetime)->format('Y-m-d H:i:s'),
            'closing_datetime' => Carbon::parse($closing_datetime)->format('Y-m-d H:i:s'),
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
        $cacheKey = 'events';

        $event = $this->cacheService->getCache($cacheKey);

        if($event) {
            $event = $event->select('id', 'title', 'image', 'status', 'location', 'description', 'opening_datetime', 'closing_datetime')->find($id);
        } else {
            $event = Event::find($id);
        }

        if(!empty($event)) {
            return $this->successResponse('Data Found', $event);
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
    public function update(Request $request, $id)
    {
        $event = Event::find($id);

        $opening_datetime = $request->opening_date . ' ' . $request->opening_time;
        $closing_datetime = $request->closing_date . ' ' . $request->closing_time;

        if(!empty($event)) {
            if($request->file('image')) {
                image_delete('/event_image/', $event->image);

                $fileName = image_store('event_image', $request->file('image'));

                $event->update([
                    'title' => $request->event_name,
                    'image' => $fileName,
                    'status' => $request->status,
                    'location' => $request->location,
                    'description' => $request->description,
                    'opening_datetime' => Carbon::parse($opening_datetime)->format('Y-m-d H:i:s'),
                    'closing_datetime' => Carbon::parse($closing_datetime)->format('Y-m-d H:i:s'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                $event->update([
                    'title' => $request->event_name,
                    'status' => $request->status,
                    'location' => $request->location,
                    'description' => $request->description,
                    'opening_datetime' => Carbon::parse($opening_datetime)->format('Y-m-d H:i:s'),
                    'closing_datetime' => Carbon::parse($closing_datetime)->format('Y-m-d H:i:s'),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
            return $this->successResponse('Event Updated Successfully!', $event);
        } else {
            return $this->nullResponse("There's no Event Data", NULL);
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
        $event = Event::find($id);

        if(!empty($event)) {
            if(image_delete('/event_image/', $event->image)){

                $event->delete();

                return $this->successResponse('Event Deleted Successfully!', NULL);
            } else{
                return $this->failResponse('Event Image Error! Please Contact to Backend Team', NULL);
            }
        } else {
            return nullResponse("There's no Event Data", NULL);
        }
    }

    /**
     * Total Events
     */
    public function total()
    {
        $total = Event::count();

        if(!empty($total)) {
            return $this->successResponse('Total Events Get Successfully!', $total);
        } else {
            return $this->nullResponse("There's no Events Data", NULL);
        }
    }

    /**
     * Search event with name
     */
    public function searchByName(Request $request)
    {
        $events = Event::query();
        $key = $request->input('q');

        if(!empty($request->input()) && $key != NULL) {
            $events = search_by_name($events, $key);

            if(!empty($events->items())) {
                return $this->successResponse('Event Found Successfully!', $events);
            } else {
                return $this->nullResponse("There's no Event Data", NULL);
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
        $events = Event::query();

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $startDate = Carbon::parse($startDate)->startOfDay();
        $endDate = Carbon::parse($endDate)->endOfDay();

        if(!empty($request->input()) && $startDate != NULL && $endDate != NULL) {
            $events = filter_by_date($events, $startDate, $endDate);

            if(!empty($events->items())) {
                return $this->successResponse('Event Found Successfully!', $events);
            } else {
                return $this->nullResponse("There's no Event Data", NULL);
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
        $events = Event::query();
        $key = $request->input('q');

        if(!empty($request->input()) && $key != NULL) {
            $events = sort_by_name($events, $key);

            if(!empty($events->items())) {
                return $this->successResponse('Event Found Successfully!', $events);
            } else {
                return $this->nullResponse("There's no Event Data", NULL);
            }
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * Delete all events
     */
    public function multipleDelete(Request $request)
    {
        $events = Event::query();

        $ids = $request->input('ids');
        $ids = explode(',', $ids);

        if(!empty($request->input()) && $ids != NULL) {
            multiple_delete($events, $ids, '/event_image/');

            return $this->successResponse('Multiple Events Deleted Successfully!', NULL);
        } else {
            return $this->validatorErrorResponse("Search key requierd", NULL);
        }
    }

    /**
     * This function is for search list.
     * Like when the user type "Yangon" in search box,
     * frontend will show suggestion.
     * This function will return object array.
     */
    public function searchList()
    {
        $events = Event::select('title', 'location')->get();

        return $this->successResponse('Get Data Successully', $events);
    }
}
