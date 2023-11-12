<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class Owners {

    /**
     * Заберает список оунеров с машины ai.r-broker.ru
     * @return array
     */
    public static function getOwners(): array
    {
        $request = Http::withHeader('GoodkeyApiPass', env('MCC_SERVICES_PASS'))
            ->post('https://lk.sales-management-center.com/calls/owners');
        $response = json_decode($request->body(), true);
        if (isset($response['owners'])) {
            return $response['owners'];
        }
        return [];
    }

    /**
     * Заберает список оунеров с машины ai.r-broker.ru
     * @return array
     */
    public static function getCalls($owner, $call_id, $date = null): array
    {
        $request = Http::withHeader('GoodkeyApiPass', env('MCC_SERVICES_PASS'))
            ->post('https://lk.sales-management-center.com/calls/events', [
                'owner' => $owner,
                'call_id' => $call_id,
                'date' => $date,
            ]);
        $response = json_decode($request->body(), true);
        if (isset($response['result'])) {
            return $response['result'];
        }
        return [];
    }

}
