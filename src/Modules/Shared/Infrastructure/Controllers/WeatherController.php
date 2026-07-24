<?php

namespace Modules\Shared\Infrastructure\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Shared\Application\Actions\GetCurrentWeatherAction;

class WeatherController extends Controller
{
    public function current(Request $request, GetCurrentWeatherAction $action): JsonResponse
    {
        $city = $request->query('city', config('services.openweathermap.city', 'Jakarta'));

        $data = $action->execute($city);

        if ($data === null) {
            return response()->json([
                'data' => null,
                'message' => 'Weather API key not configured.',
            ]);
        }

        return response()->json([
            'data' => $data,
        ]);
    }
}
