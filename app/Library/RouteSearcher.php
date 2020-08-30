<?php

namespace App\Library;

class RouteSearcher {
    public function __construct() {
        $this->schedules = $s;
    }

    public function find($from, $to, $prev) {
        return $this->schedules
            ->filter(function($s) use($from, $time) {
                return $s['from_place_id'] == $from && $time <= $s['departure_time'];
            })
            ->sortBy('arrival_time')
            ->map(function($s) use($prev) {
                return [
                    'arrival_time' => $s['arrival_time'],
                    'schedules' => array_merge($prev, [$s])
                ];
            });
    }

    public function merge(&$p, $newRoutes) {
        $index = 0;

        foreach($newRoutes as $newRoute) {
            while($index < count($q)) {
                $route = $q[$index];

                if($newRoute['arrival_time'] !== $route['arrival_time']) {
                    $diff = $newRoute['arrival_time'] < $route['arrival_time'] ? -1 : 1;
                }else{
                    $diff = count($newRoute['schedules']) - count($route['schedules']);
                }

                if($diff < 0) break;
                else $index++;
            }

            array_splice($q, $index, 0, [$newRoutes]);
            if($index > 0) $index--;
        }
    }

    public function search($from, $to, $time) {
        $result = [];
        $q = $this->find($from, $time, [])->toArray();

        while(count($result) < 5 && count($q) > 0) {
            $route = array_shift($q);
            $schedules = array_last($route['schedules']);

            if($schedules['to_place_id'] == $to) {
                array_push($result, $route['schedules']);
            }else{
                $newRoutes = $this->find($schedules['to_place_id'], $schedules['arrival_time'], $route['schedules']);
                $this->merge($q, $newRoutes);
            }
        }
        return $result;
    }

}