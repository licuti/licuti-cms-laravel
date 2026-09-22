<?php

namespace App\Models;

use App\Core\Enums\UserStatus;
use App\Models\Traits\HasUuid;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, HasUuid, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'avatar_media_uuid',
        'gender',
        'birthday',
        'status',
        'is_admin',
        'google_id',
        'facebook_id',
        'last_login_at',
        'last_login_ip',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'facebook_id',
        'last_login_ip',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'phone_verified_at'  => 'datetime',
            'last_login_at'      => 'datetime',
            'birthday'           => 'date',
            'password'           => 'hashed',
            'is_admin'           => 'boolean',
            'status'             => UserStatus::class,
        ];
    }

    public function avatarMedia()
    {
        return $this->belongsTo(\App\Models\Media::class, 'avatar_media_uuid', 'uuid');
    }

    public function getAvatarUrl(): ?string
    {
        if ($this->relationLoaded('avatarMedia') && $this->avatarMedia) {
            return $this->avatarMedia->getThumbUrl();
        }

        if (!empty($this->avatar_media_uuid)) {
            $media = \App\Models\Media::where('uuid', $this->avatar_media_uuid)->first();
            return $media ? $media->getThumbUrl() : null;
        }

        if (!empty($this->avatar)) {
            return $this->avatar;
        }

        return null;
    }

    // ─── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', UserStatus::ACTIVE);
    }

    public function scopeAdmin($query)
    {
        return $query->where('is_admin', true);
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    public function isBanned(): bool
    {
        return $this->status === UserStatus::BANNED;
    }

    /**
     * Có quyền truy cập khu vực Quản trị (qua AdminMiddleware / AuthController).
     *
     * = is_admin (toàn quyền, Gate::before trong AppServiceProvider)
     *   || có permission admin.access (RolePermissionSeeder cấp cho
     *      super-admin / admin / editor).
     *
     * Role 'customer' không có admin.access -> bị chặn.
     */
    public function canAccessAdmin(): bool
    {
        return $this->can('admin.access');
    }
}
