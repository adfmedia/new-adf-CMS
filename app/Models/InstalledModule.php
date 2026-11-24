<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A row in the module registry (the `modules` table)
 *
 * @property string $name
 * @property string $version
 * @property string $status
 * @property bool $is_core
 * @property array|null $manifest
 */
class InstalledModule extends Model
{
    public const STATUS_ENABLED     = 'enabled';
    public const STATUS_DISABLED    = 'disabled';
    public const STATUS_UNINSTALLED = 'uninstalled';

    public $table = 'modules';

    protected $fillable = [
        'name',
        'version',
        'status',
        'is_core',
        'manifest',
        'checksum',
        'installed_at',
        'status_changed_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'is_core'           => 'boolean',
            'manifest'          => 'array',
            'installed_at'      => 'datetime',
            'status_changed_at' => 'datetime',
        ];
    }

    /**
     * Key used by the CMS change log to load this record
     */
    public function getOwnerKeyName(): string
    {
        return 'id';
    }

    /**
     * Name shown for this record in the CMS change log
     */
    public function getTitleAttribute(): string
    {
        return $this->name;
    }

    public function isEnabled(): bool
    {
        return $this->status === self::STATUS_ENABLED;
    }
}
