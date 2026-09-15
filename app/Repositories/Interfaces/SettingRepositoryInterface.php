<?php

namespace App\Repositories\Interfaces;

interface SettingRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get settings by group
     *
     * @param string $group
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByGroup(string $group);

    /**
     * Get setting by key
     *
     * @param string $key
     * @return \App\Models\Setting|null
     */
    public function getByKey(string $key);

    /**
     * Update or create a setting
     *
     * @param string $key
     * @param array $data
     * @return \App\Models\Setting
     */
    public function updateOrCreateByKey(string $key, array $data);
}
