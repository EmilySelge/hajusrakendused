<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class WeatherController extends Controller
{
    public function index()
    {
        return Inertia::render('Weather');
    }

    public function search(Request $request)
    {
        $request->validate([
            'city' => 'required|string|max:255',
        ]);

        try {
            $city = $request->city;

            $data = Cache::remember("weather_" . strtolower($city), 600, function () use ($city) {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => $city,
                    'appid' => config('services.openweathermap.key'),
                    'units' => 'metric',
                ]);

                return $response->json();
            });

            if (isset($data['cod']) && $data['cod'] !== 200) {
                return response()->json(['error' => 'City not found'], 404);
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch weather data'], 500);
        }
    }
}