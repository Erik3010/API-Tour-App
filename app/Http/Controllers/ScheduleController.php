<?php

namespace App\Http\Controllers;

use App\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $schedule = Schedule::all();
        // $schedule = Schedule::paginate(20);

        return response()->json($schedule, 200);
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
        $validate = Validator::make($request->all(), [
            'line' => 'required',
            'from_place_id' => 'required',
            'to_place_id' => 'required',
            'departure_time' => 'required',
            'arrival_time' => 'required',
            'distance' => 'required',
            'speed' => 'required'
        ]);

        if($validate->fails()) return response()->json(['message' => 'Data cannot be processedd'], 422);

        $rangeStart = strtotime('08:30 AM');
        $rangeEnd = strtotime('06:00 PM');

        $depar_time = strtotime($request->departure_time);
        $arriv_time = strtotime($request->arrival_time);

        // return 'depart_time: '.$depar_time .' '. 'arriv_time: '.$arriv_time;
        if($depar_time > $rangeStart &&
             $depar_time < $rangeEnd &&
             $arriv_time > $rangeStart &&
             $arriv_time < $rangeEnd &&
             $arriv_time > $depar_time
        ) {
            Schedule::create([
                'line' => $request->line,
                'from_place_id' => $request->from_place_id,
                'to_place_id' => $request->to_place_id,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'distance' => $request->distance,
                'speed' => $request->speed
            ]);
            return response()->json(['message' => 'Create success'], 200);
        }

        return response()->json(['message' => 'Data cannot be processed'], 422);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Schedule  $schedul
     * @return \Illuminate\Http\Response
     */
    public function show(Schedule $schedule)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function edit(Schedule $schedule)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Schedule $schedule)
    {

        $validate = Validator::make($request->all(), [
            'line' => 'required',
            'from_place_id' => 'required',
            'to_place_id' => 'required',
            'departure_time' => 'required',
            'arrival_time' => 'required',
            'distance' => 'required',
            'speed' => 'required'
        ]);

        if($validate->fails()) return response()->json(['message' => 'data cannot be processed'], 422);

        $rangeStart = strtotime('08:30 AM');
        $rangeEnd = strtotime('06.00 PM');

        $depar_time = strtotime($request->departure_time);
        $arriv_time = strtotime($request->arrival_time);

        if($depar_time > $rangeStart &&
            $depar_time < $rangeEnd &&
            $arriv_time > $rangeStart &&
            $arriv_time < $rangeEnd &&
            $arriv_time > $depar_time
        ) {
            $schedule->update([
                'line' => $request->line,
                'from_place_id' => $request->from_place_id,
                'to_place_id' => $request->to_place_id,
                'departure_time' => $request->departure_time,
                'arrival_time' => $request->arrival_time,
                'distance' => $request->distance,
                'speed' => $request->speed
            ]);

            return response()->json(['message' => 'Update succecss'], 200);
        }

        return response()->json(['message' => 'data cannot be proceseed'], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Schedule  $schedule
     * @return \Illuminate\Http\Response
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();

        return response()->json(['message' => 'delete sucess'], 200);
    }
}
