<?php

namespace Modules\Forms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormSubmission extends Model
{
    protected $table        = 'form_submissions';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'form_id',
        'submission_url',
        'marketing_opt_in',
        'marketing_opt_in_text',
        'marketing_opt_in_date',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormSubmissionField::class);
    }

    public function tracking_data(): HasMany
    {
        return $this->hasMany(FormSubmissionTrackingData::class);
    }
}