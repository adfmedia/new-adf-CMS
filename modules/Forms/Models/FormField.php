<?php

namespace Modules\Forms\Models;

use App\Models\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FormField extends Model
{
    use SoftDeletes, CachesQueries;

    protected $table        = 'form_fields';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'form_id',
        'label',
        'help_text',
        'type',
        'options',
        'settings',
        'required',
        'code_reference',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'settings'  => 'array'
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    protected function formFieldName(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => Str::slug($attributes['label'], '_')
        )->shouldCache();
    }

    protected function placeholder(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                if (!isset($attributes['settings']) || empty($attributes['settings']) || !Str::isJson($attributes['settings'])) {
                    return null;
                }

                $settings = json_decode($attributes['settings']);
                return data_get($settings, 'placeholder');
            }
        )->shouldCache();
    }

    protected function optionsArray(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => array_filter(explode(PHP_EOL, $attributes['options']))
        )->shouldCache();
    }

    protected function validation(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                $rules = [];
                $messages = [
                    'required'  => $attributes['label'] . ' is required, please complete it.'
                ];
                if ($attributes['required']) {
                    $rules[] = 'required';
                }

                switch ($attributes['type']) {
                    case 'email':
                        $rules[] = 'email';
                        $messages['email'] = 'Please provide a valid email address.';
                        break;
                }

                return [
                    'rules'     => $rules,
                    'messages'  => $messages,
                ];
            }
        )->shouldCache();
    }
}