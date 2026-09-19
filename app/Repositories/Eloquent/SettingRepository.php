<?php

namespace App\Repositories\Eloquent;

use App\Repositories\BaseRepository;

use App\Models\Setting;
use App\Repositories\Interfaces\SettingRepositoryInterface;

class SettingRepository extends BaseRepository implements SettingRepositoryInterface
{
    public function __construct(Setting $model)
    {
        parent::__construct($model);
    }

    public function getByGroup(string $group)
    {
        return $this->model->byGroup($group)->get();
    }

    public function getByKey(string $key)
    {
        return $this->model->where('key', $key)->first();
    }

    public function updateOrCreateByKey(string $key, array $data)
    {
        return $this->model->updateOrCreate(
            ['key' => $key],
            $data
        );
    }
}
