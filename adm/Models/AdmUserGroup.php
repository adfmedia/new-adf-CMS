<?php

namespace Adm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class AdmUserGroup extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'permissions',
        'super_admin',
        'active'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions'   => 'array',
        ];
    }

    /**
     * Returns the users assigned to this group
     * 
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(AdmUser::class);
    }

    /**
     * Return the options to display in CMS dropdowns,
     * filtered to remove super admin users if the logged
     * in user is not a super admin
     * 
     * @return array
     */
    public static function getAdmSelectOptions(): array
    {
        $User = Auth::guard('adm')->user();
        $GroupsQry = self::distinct()->orderBy('title');
        if (!$User->is_super_admin) {
            $GroupsQry->where('super_admin', false);
        }

        $options = [
            0   => 'Select a group'
        ];
        foreach ($GroupsQry->get() as $Group) {
            $options[$Group->id] = $Group->title;
        }

        return $options;
    }
}