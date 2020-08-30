<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\History;
use App\Place;
use App\Schedule;
use App\User;
use Illuminate\Support\Arr;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use App\Library\RouteSearcher;

class RouteController extends Controller
{
    public function search($from, $to, $departure) {
        $from1 = Place::find($from);
        $to1 = Place::find($to);

        $from2 = Schedule::where('from_place_id', $from)->count();
        $to2 = Schedule::where('to_place_id', $to)->count();

        if(!($from1 && $to1 && $from2 && $to2) || $from == $to) return response()->json(['message' => 'not found']);

        $departure = $departure ? $departure : date('H:i:s', strtotime(Carbon::now('+7')));

        $schedules = Schedule::get();
        $route = new RouteSearcher($schedules);
        $result = $route->search($from, $to, $time);

        $result = array_map(function($s) use($from, $to) {
            $departure_time = $s[0]['departure_time'];
            $arrival_time = array_last($s)['arrival_time'];
            $travel_time = date('H:i:s', strtotime($arrival_time) - strtotime($departure_time));

            $lines = [];
            $schedules = [];

            $t = 0;

            for($i=0;$i<count($s);$i++) {
                $curr = &$s[$i];
                if($i) {
                    $prev = $s[$i-1];
                    if($curr['line'] !== $prev['line']) $t++;
                }

                array_push($schedule_id, $curr['id']);
            }

            return [
                'schedules' => $s,
                'departure_time' => $departure_time,
                'arrival_time' => $arrival_time,
                'travel_time' => $travel_time,
                'lines' => $lines,
                'number_of_transfers' => $t,
                'point' => [$from, $to]
            ];
        }, $result);

        return response()->json($result, 200);
    }

    public function search2($from, $to, $departure) {
        $sch = [];
        if($departure === "null") $departure = date('H:i:s');

        // history
        $histories = History::where([
            'from_place_id' => $from,
            'to_place_id' => $to
        ])->get();

        if($histories->isEmpty()) {
            $number = 0;
        }else{
            foreach($histories as $history) {
                $his[] = $history->num_searches;
            }

            $number = array_sum($his);
        }

        $storedRoute = [];

        // schedule
        $schedules = Schedule::where(['from_place_id' => $from,'to_place_id'=> $to,])
                                ->where('departure_time', '>=', $departure)
                                ->orderBy('arrival_time', 'ASC')
                                ->limit(5)
                                ->get();

        $tempId = Schedule::where('from_place_id', $from)->pluck('to_place_id')->toArray();

        return $schedules;
        // * ----------------------------------------------------------------
        // search route line
        // $place_from = Place::find($from);
        // $place_to = Place::find($to);

        // $x1 = $place_from->x;
        // $x2 = $place_to->x;

        // $y1 = $place_from->y;
        // $y2 = $place_to->y;

        // if($place_from->x > $place_to->x) {
        //     $x1 = $place_to->x;
        //     $x2 = $place_from->x;
        // }

        // if($place_from->y > $place_to->y) {
        //     $y1 = $place_to->y;
        //     $y2 = $place_from->y;
        // }

        // $placeQuery = Place::whereBetween('x', [$x1, $x2])
        //                 ->whereBetween('y', [$y1, $y2])
        //                 ->orderBy('x','ASC')
        //                 ->orderBy('y','ASC');

        // $placesGet = $placeQuery->get();
        // $placesId = $placeQuery->pluck('id');

        // return $placesId;

        // end place
        // * ----------------------------------------------------------------

        if($schedules->isEmpty()) {
            // $sch = [];
            return response()->json(['message' => 'no route found'], 422);
        }else{
            foreach($schedules as $schedule) {
                // ? manual PHP
                // $depar = new \DateTime($schedule->departure_time);
                // $arriv = new \DateTime($schedule->arrival_time);
                // $travel_time = $depar->diff($arriv);
                // return $travel_time->format('%H:%i:%s');

                // ! using Carbon
                $travel_time = (new Carbon($schedule->departure_time))
                                ->diff(new Carbon($schedule->arrival_time))
                                ->format('%H:%I:%S');

                $deparStart = strtotime($departure);
                $tempDepar = date('H:i', $deparStart + 3600);
                $deparEnd = strtotime($tempDepar);
                // $deparEnd = date('H', strtotime($departure));

                $sch_depar = strtotime($schedule->departure_time);

                // $depar_hour = date('H i', strtotime($departure));
                // return $depar_hour;
                // $next_depar_hour = intval($depar_hour)+1;
                // $sch_depar = date('H', strtotime($schedule->departure_time));

                // if($sch_depar >= $depar_hour && $sch_depar < $next_depar_hour) {

                if($sch_depar >= $deparStart && $sch_depar < $deparEnd) {
                    $sch[] = [
                        'id' => $schedule->id,
                        'line' => $schedule->line,
                        'departure_time' => $schedule->departure_time,
                        'arrival_time' => $schedule->arrival_time,
                        'travel_time' => $travel_time,
                        'from_place' => Schedule::find($schedule->id)->from_place()->first([
                                            'id','name','type','longitude','latitude','x','y','description','image_path'
                                        ]),
                        'to_place' => Schedule::find($schedule->id)->to_place()->first([
                            'id','name','type','longitude','latitude','x','y','description','image_path'
                        ])
                    ];
                }

            }
        }

        // if($sch->isEmpty()) return response()->json(['message' => 'no route found'], 422);

        // merge data
        $data = [
            'num_selections' => $number,
            'schedules' => $sch
        ];
        return response()->json($data, 200);
    }

    public function store(Request $request) {
        $user_id = User::where('token', $request->token)->first()->id;

        // validation
        $validate = Validator::make($request->all(), [
            'from_place_id' => 'required',
            'to_place_id' => 'required',
            'schedule_id' => 'required'
        ]);
        if($validate->fails()) return response()->json(['message' => 'data cannot be processed'], 422);

        $histories = History::where([
            'from_place_id' => $request->from_place_id,
            'to_place_id' => $request->to_place_id
        ])->get();

        $schedule_id = implode(', ', $request->schedule_id);

        if($histories->isEmpty()) {
            History::create([
                'user_id' => $user_id,
                'from_place_id' => $request->from_place_id,
                'to_place_id' => $request->to_place_id,
                'schedule_id' => $schedule_id,
                'num_searches' => '1'
            ]);
        }else{
            foreach($histories as $history) {
                $history->update([
                    'schedule_id' => $schedule_id,
                    'num_searches' => intval($history->num_searches)+1
                ]);
            }
        }

        return response()->json(['message' => 'create success'], 200);
    }
}