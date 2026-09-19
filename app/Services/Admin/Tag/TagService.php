<?php

namespace App\Services\Admin\Tag;

use App\Core\Base\BaseService;
use App\DTOs\Tag\TagDTO;
use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TagService extends BaseService
{
    public function __construct(
        private readonly TagRepositoryInterface $repository
    ) {}

    public function getList(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->getFiltered($filters);
    }

    public function findByUuid(string $uuid): Tag
    {
        return $this->repository->findByUuid($uuid);
    }

    public function create(TagDTO $dto): Tag
    {
        return $this->handleTransaction(function () use ($dto) {
            $data = $dto->toArray();
            $data['slug'] = $this->generateUniqueTagSlug($dto->name, $dto->slug);

            return $this->repository->create($data);
        });
    }

    public function update(string $uuid, TagDTO $dto): Tag
    {
        return $this->handleTransaction(function () use ($uuid, $dto) {
            $model = $this->findByUuid($uuid);
            $data = $dto->toArray();
            $data['slug'] = $this->generateUniqueTagSlug($dto->name, $dto->slug, $model->id);

            $this->repository->update($model->id, $data);

            return $model->fresh();
        });
    }

    public function delete(string $uuid): bool
    {
        return $this->handleTransaction(function () use ($uuid) {
            $model = $this->findByUuid($uuid);
            $model->posts()->detach();

            return $this->repository->delete($model->id);
        });
    }

    private function generateUniqueTagSlug(string $name, ?string $customSlug = null, ?int $ignoreId = null): string
    {
        $base = Str::slug($customSlug ?: $name);
        $slug = $base;
        $count = 1;

        while (true) {
            $query = Tag::where('slug', $slug);
            if ($ignoreId) {
                $query->where('id', '!=', $ignoreId);
            }

            if (!$query->exists()) {
                break;
            }

            $slug = $base . '-' . $count;
            $count++;
        }

        return $slug;
    }
}
