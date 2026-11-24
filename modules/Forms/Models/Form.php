<?php

namespace Modules\Forms\Models;

use App\Casts\File;
use App\Casts\FormStructure;
use App\Casts\Link;
use App\Casts\Wysiwyg;
use App\Models\Traits\CachesQueries;
use App\Models\Url;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Scopes\StatusEnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;

#[ScopedBy([StatusEnabledScope::class])]
class Form extends Model
{
    use SoftDeletes, CachesQueries;

    protected $table        = 'forms';
    protected $primaryKey   = 'id';

    protected $fillable     = [
        'title',
        'heading',
        'content',

        'structure',

        'submit_text',
        'marketing_opt_in',
        'marketing_opt_in_text',
        'marketing_opt_out_text',
        'use_recaptcha',
        'redirect_link',

        'internal_recipients',
        'internal_subject',

        'send_auto_response',
        'auto_response_subject',
        'auto_response_content',
        'auto_response_attachment',

        'ga4_event',
        'status',

        'code_reference',
        'template',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'structure'                 => FormStructure::class,
            'redirect_link'             => Link::class,
            'auto_response_attachment'  => File::class,
            'content'                   => Wysiwyg::class,
            'auto_response_content'     => Wysiwyg::class,
        ];
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }

    /**
     * Return a collection of the form's fields, in the
     * same order as they would be output on a page
     */
    public function orderedFlowFields(): Attribute
    {
        return Attribute::make(
            get: function () {
                $fields = [];
                foreach ($this->structure['rows'] as $row) {
                    foreach ($row['columns'] as $column) {
                        foreach ($column['fields'] as $field) {
                            $fields[] = $field;
                        }
                    }
                }
                return collect($fields);
            }
        )->shouldCache();
    }

    public function form_submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public static function findByReference(string $reference)
    {
        return self::where('code_reference', $reference)->first();
    }

    protected function marketingOptInTextPrivacyLinked(): Attribute
    {
        $Model = $this;
        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($Model) {
                $text = $attributes['marketing_opt_in_text'];
                return is_string($text) ? $Model->addPrivacyLinksIntoText($text) : $text;
            }
        )->shouldCache();
    }

    protected function marketingOptOutTextPrivacyLinked(): Attribute
    {
        $Model = $this;
        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($Model) {
                $text = $attributes['marketing_opt_out_text'];
                return is_string($text) ? $Model->addPrivacyLinksIntoText($text) : $text;
            }
        )->shouldCache();
    }
    
    public function addPrivacyLinksIntoText(string $text): string
    {
        $PrivacyPage = Url::getSystemPage('page', 'privacy_policy');
        if (!$PrivacyPage) {
            return $text;
        }

        $terms_to_replace = [
            'Privacy Policy', 'Privacy policy', 'privacy policy',
            'Privacy Notice', 'Privacy notice', 'privacy notice',
        ];
        $replacements = [];
        foreach ($terms_to_replace as $target_text) {
            $replacements[] = '<a href="' . $PrivacyPage->url . '" target="_blank" class="underline hover:no-underline">' . $target_text . '</a>';
        }

        return str_replace($terms_to_replace, $replacements, $text);
    }
}