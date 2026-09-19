<?php

namespace App\Services\Admin\Banner;

use App\Core\Base\BaseService;
use App\DTOs\Banner\BannerDTO;
use App\Models\Banner;
use App\Repositories\Interfaces\BannerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BannerService extends BaseService
{
    public function __construct(
        private readonly BannerRepositoryInterface $repository
    ) {
    }

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getActivePaginated($filters);
    }

    public function create(BannerDTO $dto): Banner
    {
        return DB::transaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['uuid'] = Str::uuid()->toString();

            $model = $this->repository->create($data);

            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['title'])) {
                    $model->translations()->create([
                        'locale'      => $locale,
                        'title'       => $transData['title'],
                        'image'       => $transData['image'] ?? null,
                        'description' => $transData['description'] ?? null,
                    ]);
                }
            }

            return $model;
        });
    }

    public function update(string $uuid, BannerDTO $dto): Banner
    {
        return DB::transaction(function () use ($uuid, $dto) {
            $model = $this->repository->findByUuidWithRelations($uuid);

            $this->repository->update($model->id, $dto->toArray());

            foreach ($dto->translations as $locale => $transData) {
                if (!empty($transData['title'])) {
                    $model->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'title'       => $transData['title'],
                            'image'       => $transData['image'] ?? null,
                            'description' => $transData['description'] ?? null,
                        ]
                    );
                }
            }

            return $model;
        });
    }

    public function delete(string $uuid): bool
    {
        return DB::transaction(function () use ($uuid) {
            $model = $this->repository->findByUuidWithRelations($uuid);
            $model->translations()->delete();
            return $this->repository->delete($model->id);
        });
    }
}
