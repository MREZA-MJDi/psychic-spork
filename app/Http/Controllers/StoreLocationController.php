<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class StoreLocationController extends Controller
{
    public function provinces(): JsonResponse
    {
        $provinces = [];

        foreach ($this->locations() as $id => $province) {
            $provinces[] = ['id' => $id, 'name' => $province['name']];
        }

        return response()->json($provinces);
    }

    public function cities(Request $request): JsonResponse
    {
        $validated = $request->validate(['province' => ['required', 'integer', 'min:1', 'max:1000']]);
        $cities = $this->locations()[(int) $validated['province']]['cities'] ?? [];

        return response()->json(array_map(fn (string $name) => ['name' => $name], $cities));
    }

    private function locations(): array
    {
        return json_decode(
            file_get_contents(resource_path('data/iran-locations.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );
    }
}
