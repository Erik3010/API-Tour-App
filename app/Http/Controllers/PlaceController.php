<?php

namespace App\Http\Controllers;

use App\User;
use App\Place;
use App\History;

use App\Library\Poi;
use App\Library\PoiFactory;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
// use Illuminate\Support\Facades\DB;

class PlaceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::where('token', $request->token)->first();
        if(!$user) {
            $places = Place::orderBy('name','ASC')->get()->toArray();

            $data = array_map(function($place) {
                return Arr::except($place, ['open_time','close_time','created_at','updated_at']);
            }, $places);

            return response()->json($data, 200);
        }
        $user_id = $user->id;
        $placesId = Place::pluck('id');

        // $placeHistory = Place::join('histories','histories.to_place_id','=','places.id')
                    // ->where('histories.user_id', $user_id)
                    // ->select(['places.*','histories.num_searches', DB::raw('count(histories.num_searches) as totalHistory')])
                    // ->orderBy('totalHistory','asc')
                    // ->groupBy('places.id')
                    // ->get();

        $placeDataId = History::whereIn('to_place_id', $placesId)
                                ->where('user_id', $user_id)
                                ->orderBy('num_searches','DESC')
                                ->pluck('to_place_id');

        $placeHistory = Place::whereIn('id', $placeDataId)
                                ->groupBy('id')
                                ->get()
                                ->toArray();

        $placeSort = Place::whereNotIn('id',$placeDataId)
                            ->orderBy('name')
                            ->get()
                            ->toArray();

        $places = array_merge($placeHistory, $placeSort);
        // $places = Arr::collapse($placeHistory, $placeSort);
        return $places;

        $data = array_map(function($place) {
            return Arr::except($place, ['open_time','close_time','created_at','updated_at']);
        }, $places);

        return response()->json($data, 200);

        // if(!$placeHistory->isEmpty()) {
        //     foreach($placeHistory as $placeHis) {
        //         $idHis[] = $placeHis->id;
        //     }
        //     $placeAlpha = Place::whereNotIn('id',$idHis)->orderBy('name','asc')->get();

        //     $placeHistory = json_encode($placeHistory);
        //     $placeAlpha = json_encode($placeAlpha);

        //     // merge the data
        //     $places = array_merge(json_decode($placeHistory, true), json_decode($placeAlpha, true));
        // }else{
        //     $places = Place::all();
        // }

        // foreach($places as $place) {
        //     $data[] = [
        //         'id' => $place['id'],
        //         'name' => $place['name'],
        //         'type' => $place['type'],
        //         'latitude' => $place['latitude'],
        //         'longitude' => $place['longitude'],
        //         'x' => $place['x'],
        //         'y' => $place['y'],
        //         'open_time' => $place['open_time'],
        //         'close_time' => $place['close_time'],
        //         'image_path' => $place['image_path'],
        //         'description' => $place['description']
        //     ];
        // }

        // $placeOriginal = Place::leftJoin('histories', function($join) {
        //                         $join->on('places.id', '=','histories.to_place_id');
        //                     })
        //                 ->where('histories.user_id', null)
        //                 ->orderBy('places.name', 'asc')
        //                 ->get(['places.*']);
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
            'name' => 'required',
            'type' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'description' => 'required',
            'open_time' => 'required',
            'close_time' => 'required'
        ]);

        if($validate->fails()) return response()->json(['message' => 'Data cannot be processed'], 422);

        $poi = new PoiFactory();
        $coordinate = $poi->calculate([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        $image = $request->file('image');
        $imageName = $image->getClientOriginalName();
        // move image to public/images/
        $image->move(public_path('images'), $imageName);

        // create here
        $param = $request->all();
        $param['image_path'] = $imageName;
        $param['x'] = $coordinate['x'];
        $param['y'] = $coordinate['y'];

        $params = Arr::except($param, ['token','image']);

        Place::create($params);

        return response()->json(['message' => 'create success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Place  $place
     * @return \Illuminate\Http\Response
     */
    public function show(Place $place, Request $request)
    {
        $user_id = User::where('token', $request->token)->pluck('id')->first();

        $num_searches = History::where([
            'user_id' => $user_id,
            'to_place_id' => $place->id
        ])->get();

        if($num_searches->isEmpty()) {
            $num_search = 0;
        }else{
            foreach($num_searches as $num) {
                $nums[] = $num->num_searches;
            }
            $num_search = array_sum($nums);
        }

        $data = [
            'name' => $place->name,
            'type' => $place->type,
            'x' => $place->x,
            'y' => $place->y,
            'image_path' => $place->image_path,
            'description' => $place->description,
            'num_searches' => $num_search
        ];

        return response()->json($data, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Place  $place
     * @return \Illuminate\Http\Response
     */
    public function edit(Place $place)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Place  $place
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Place $place)
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required',
            'type' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            'description' => 'required'
        ]);

        if($validate->fails()) return response()->json(['message' => 'Data cannot be processed'], 422);

        // get coordinate x and y
        $poi = new PoiFactory();
        $coordinate = $poi->calculate([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude
        ]);

        $image = $request->image;
        $imageName = $image->getClientOriginalName();

        if($imageName != $place->image_path) {
            if(file_exists("images/$place->image_path")) {
                unlink(public_path('images/').$place->image_path);
            }
        }

        $image->move(public_path('images'), $imageName);

        // update here
        $place->update([
            'name' => $request->name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'image_path' => $imageName,
            'description' => $request->description,
            'type' => $request->type,
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'x' => $coordinate['x'],
            'y' => $coordinate['y']
        ]);

        return response()->json(['message' => 'create success'], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Place  $place
     * @return \Illuminate\Http\Response
     */
    public function destroy(Place $place)
    {
        if(file_exists(public_path('images/').$place->image_path)) {
            unlink(public_path('images/').$place->image_path);
        }

        $place->delete();

        return response()->json(['message' => 'delete success'], 200);
    }
}