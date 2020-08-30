<?php
/*
for reference only
*/

namespace App\Library;

class Poi
{
    private $latitude;

    private $longitude;

    public function __construct($latitude, $longitude)
    {
        $this->latitude = deg2rad($latitude);
        $this->longitude = deg2rad($longitude);
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function distanceTo(Poi $other)
    {
        $earthRadius = 6371000;

        $diffLatitude = $other->getLatitude() - $this->latitude;
        $diffLongitude = $other->getLongitude() - $this->longitude;

        $a = sin($diffLatitude / 2) * sin($diffLatitude / 2) +
            cos($other->getLatitude()) * cos($this->latitude) *
            sin($diffLongitude / 2) * sin($diffLongitude / 2);
        $c = 2 * asin(sqrt($a));

        return $c * $earthRadius;
    }
}