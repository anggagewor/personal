<?php

namespace Modules\Shared\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    public function current(Request $request): JsonResponse
    {
        $apiKey = config('services.openweathermap.key');
        $city = $request->query('city', config('services.openweathermap.city', 'Jakarta'));

        if (!$apiKey) {
            return response()->json([
                'data' => null,
                'message' => 'Weather API key not configured.',
            ]);
        }

        $cacheKey = "weather:{$city}";

        $data = Cache::remember($cacheKey, 1800, function () use ($apiKey, $city) {
            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'q' => $city,
                'appid' => $apiKey,
                'units' => 'metric',
                'lang' => 'id',
            ]);

            if (!$response->successful()) {
                return null;
            }

            $json = $response->json();

            return [
                'city' => $json['name'] ?? $city,
                'temp' => round($json['main']['temp'] ?? 0),
                'feels_like' => round($json['main']['feels_like'] ?? 0),
                'humidity' => $json['main']['humidity'] ?? 0,
                'description' => ucfirst($json['weather'][0]['description'] ?? ''),
                'icon' => $json['weather'][0]['icon'] ?? '01d',
                'wind_speed' => round(($json['wind']['speed'] ?? 0) * 3.6, 1),
            ];
        });

        return response()->json([
            'data' => $data,
        ]);
    }
}
