<?php

namespace Adm\Models;

use App\Models\Scopes\LanguageScope;
use App\Models\Scopes\StatusEnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ScopedBy([LanguageScope::class])]
class ChangeLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table        = 'change_logs';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey   = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable     = [
        'changeable_type',
        'changeable_id',
        'language_id',
        'change_type',
        'adm_user_id'
    ];

    /**
     * Returns the resource that was changed
     * 
     * @return MorphTo
     */
    public function changeable(): MorphTo
    {
        $RelatedModel = $this->getRelatedRecordModel();
        $Morph = $this->morphTo(null, null, null, $this->getRelatedRecordOwnerKey($RelatedModel));

        if ($this->relatedRecordOwnerHasStatus($RelatedModel)) {
            $Morph->withoutGlobalScope(StatusEnabledScope::class);
        }
        if ($this->relatedRecordOwnerHasSoftDeletes($RelatedModel) && strtolower($this->change_type) == 'deleted') {
            $Morph->withTrashed();
        }
            
        return $Morph;
    }

    /**
     * Returns the CMS user who made the change
     * 
     * @return BelongsTo
     */
    public function adm_user(): BelongsTo
    {
        return $this->belongsTo(AdmUser::class);
    }

    /**
     * Returns the 'primary' ID field of the related
     * model
     * 
     * @param Model $RelatedModel
     * @return mixed
     */
    protected function getRelatedRecordOwnerKey(Model $RelatedModel): mixed
    {
        return $RelatedModel->getOwnerKeyName();
    }

    /**
     * Returns true or false depending on whether the related
     * model class has a 'status' fillable attribute
     * 
     * @param Model $RelatedModel
     * @return bool
     */
    protected function relatedRecordOwnerHasStatus(Model $RelatedModel): bool
    {
        return in_array('status', $RelatedModel->getFillable());
    }

    /**
     * Returns true or false depending on whether the related
     * model class uses the SoftDeletes trait
     * 
     * @param Model $RelatedModel
     * @return bool
     */
    protected function relatedRecordOwnerHasSoftDeletes(Model $RelatedModel): bool
    {
        return $RelatedModel::isSoftDeletable();
    }

    /**
     * Returns a new instance of the related model class
     * 
     * @return Model
     */
    protected function getRelatedRecordModel(): Model
    {
        return new $this->changeable_type();
    }
}