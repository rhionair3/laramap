<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class Gmaps extends Controller
{
	public function index()
    {
        return view('gmap');
    }

    public function getCalculate(Request $request)
    {	
    	$ori = $request['oriLat'].', '.$request['oriLang'];
    	$dest = $request['desLat'].', '.$request['desLang'];
    	$trans = $request['jenisKendaraan'];

    	$requestor = array('latitude' => $request['oriLat'], 'logitude' => $request['oriLang']);
    	$requestdes = array('latitude' => $request['desLat'], 'logitude' => $request['desLang']);

    	$requestData['origin'] = array('latitude' => $request['oriLat'], 'logitude' => $request['oriLang']);
    	$requestData['destination'] = array('latitude' => $request['desLat'], 'logitude' => $request['desLang']);
        $response = \GoogleMaps::load('directions')
            ->setParam([
                'origin'          => $ori,
                'destination'     => $dest,
            ])
            ->get();

        // echo "</pre>";
        $news = json_decode($response);

        // print_r((array) $news->routes[0]->legs[0]->distance);
        // echo "<br/>";
        // print_r((array) $news->routes[0]->legs[0]->duration);
        $duration = $news->routes[0]->legs[0]->duration;
        $distance = $news->routes[0]->legs[0]->distance;
        if ($trans == "MOTOR") {
        	$type = 'motorcicle';
        	$distance_per_litre = 25;
        	$price_per_litre = 6500;
        } 
        if ($trans == "MOBIL") {
        	$type = 'car';
        	$distance_per_litre = 12;
        	$price_per_litre = 6500;
        }
        if ($trans == "KENDARAAN_UMUM") {
        	$type = 'TAXI';
        	$distance_per_litre = 5;
        	$price_per_litre = 6500;
        }

        $veh['type'] = $type;
        $veh['distance_per_litre'] = $distance_per_litre;
        $veh['price_per_litre'] = $price_per_litre;

        $cost = ($distance->value / ($distance_per_litre * 1000)) * $price_per_litre;

        $resp['requests']= array('origin' => $requestor, 'destination' => $requestdes, 'vehicle' => $veh);
        $resp['response']= array('distance' => $distance->text, 'duration' => $duration->text, 'cost' => 'Rp. '.round($cost));

        return json_encode($resp);
    }
}
