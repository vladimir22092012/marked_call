<?php
namespace App\Services;

use App\Models\Events;
use App\Models\Owner;
use Illuminate\Support\Facades\Http;

class Owners {

    /**
     * Заберает список оунеров с машины ai.r-broker.ru
     * @return array
     */
    public static function getOwners(): array
    {
        $data = [];
        Owner::query()->where('active', '=', 1)
            ->orderBy('name')
            ->each(function(Owner $owner) use (&$data) {
                $data[$owner->owner_id] = $owner->name;
            });
        return $data;
    }

    /**
     * Заберает список оунеров с машины ai.r-broker.ru
     */
    public static function getCalls($owner, $call_id = null, $date = []): \Illuminate\Database\Eloquent\Collection|array
    {
        $model = new Events();
        $model->set_table($owner);
        $query = $model->newQuery();
        $query->select(['id', 'title', 'datetime_event']);
        if (!empty($call_id)) {
            $query->where('id', '=', $call_id);
        }
        if (!empty($date)) {
            $query->whereBetween('datetime_event', [$date[0], $date[1]]);
        }
        return $query->get();
    }

}
