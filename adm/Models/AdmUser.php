<?php

namespace Adm\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class AdmUser extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'adm_user_group_id',
        'mfa_enrolled',
        'mfa_secret',
    ];

    /**
     * The attributes that are hidden
     * 
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'mfa_enrolled',
        'mfa_secret',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    /**
     * Returns the user's assigned group
     * 
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(AdmUserGroup::class, 'adm_user_group_id', 'id');
    }

    /**
     * Return the user's first name
     * 
     * @return Attribute
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => explode(' ', $attributes['name'])[0]
        )->shouldCache();
    }

    /**
     * Returns the user's last name
     * 
     * @return Attribute
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $parts = str_split($attributes['name']);
                array_shift($parts);
                return implode(' ', $parts);
            }
        )->shouldCache();
    }

    /**
     * Returns the user's super admin status
     * 
     * @return Attribute
     */
    protected function isSuperAdmin(): Attribute
    {
        $User = $this;
        return Attribute::make(
            get: function (mixed $value) use ($User) {
                return $User->group ? $User->group->super_admin : false;
            }
        )->shouldCache();
    }

    /**
     * Determine whether the user can access the
     * provided area based on their group
     * permissions
     * 
     * @param string $area
     * @return bool
     */
    public function canAccess(string $area): bool
    {
        if (!$this->group) { return false; }
        if ($this->group->super_admin) { return true; }
        return in_array($area, $this->group->permissions);
    }
}