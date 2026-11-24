<?php

namespace Modules\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormSubmissionField extends Model
{
    protected $table        = 'form_submission_fields';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'form_submission_id',
        'form_field_id',
        'label',
        'value',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'value' => 'encrypted'
        ];
    }

    public function form_submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function form_field(): HasOne
    {
        return $this->hasOne(FormField::class, 'id', 'form_field_id');
    }
}