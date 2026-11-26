<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

final class StoreLocationController extends Controller
{
    private const API = 'https://iran-locations-api.ir/api/v1/fa/';

    public function provinces(): JsonResponse
    {
        return $this->locations('states', [], true);
    }

    public function cities(Request $request): JsonResponse
    {
        $validated = $request->validate(['province' => ['required', 'integer', 'min:1', 'max:1000']]);

        return $this->locations('cities', ['state_id' => (int) $validated['province']], false);
    }

    private function locations(string $resource, array $query, bool $provinces): JsonResponse
    {
        $key = 'store:iran-locations:v1:' . $resource . ':' . ($query['state_id'] ?? 'all');
        $cached = Cache::get($key);

        if (is_array($cached) && ($cached['fresh_until'] ?? 0) > time()) {
            return response()->json($cached['items']);
        }

        try {
            $raw = Http::acceptJson()->connectTimeout(2)->timeout(4)
                ->get(self::API . $resource, $query)->throw()->json();

            if (!is_array($raw) || !array_is_list($raw)) {
                throw new \UnexpectedValueException('Unexpected location response');
            }

            $items = [];
            foreach ($raw as $item) {
                if (!is_array($item) || !is_string($item['name'] ?? null)) {
                    continue;
                }

                $name = trim(strtr($item['name'], ['ي' => 'ی', 'ك' => 'ک']));
                if ($name === '' || mb_strlen($name) > 100) {
                    continue;
                }

                if ($provinces) {
                    $id = filter_var($item['id'] ?? null, FILTER_VALIDATE_INT);
                    if ($id === false || $id < 1 || $id > 1000) {
                        continue;
                    }
                    $items[$name] = ['id' => $id, 'name' => $name];
                } else {
                    $items[$name] = ['name' => $name];
                }
            }

            if ($items === [] || ($provinces && count($items) !== 31)) {
                throw new \UnexpectedValueException('Incomplete location response');
            }

            $items = array_values($items);
            Cache::put($key, ['items' => $items, 'fresh_until' => now()->addDay()->timestamp], now()->addDays(30));

            return response()->json($items);
        } catch (\Exception $exception) {
            if (is_array($cached) && !empty($cached['items'])) {
                return response()->json($cached['items']);
            }

            return response()->json(['message' => 'دریافت فهرست مکان‌ها ممکن نیست.'], 503);
        }
    }
}
