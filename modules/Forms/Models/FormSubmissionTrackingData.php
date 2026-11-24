<?php

namespace Modules\Forms\Models;

use Illuminate\Database\Eloquent\Model;

class FormSubmissionTrackingData extends Model
{
    protected $table        = 'form_submission_tracking_data';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'form_submission_id',
        'parameter',
        'value'
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
}