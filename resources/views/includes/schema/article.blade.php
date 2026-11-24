@if( isset($Page) && $Page )
@php
    $description = $Page->excerpt;
    $publish_date = $Page->created_at ? $Page->created_at->format("Y-m-d") : '';
    if ($Page->schema_date) {
        $publish_date = $Page->schema_date->format("Y-m-d");
    }
    $schema_arr = [
        '@context'          => 'http://schema.org',
        '@type'             => 'Article',
        'headine'           => $Page->h1_title,
        'url'               => $Page->url,
        'mainEntityOfPage'  => $Page->url,
        'description'       => (strlen($description) > 200 ? substr($description, 0, 200) . '...' : $description),
        'publisher'         => [
            '@type'             => 'Organization',
            'name'              => config('app.name'),
            'logo'              => [
                '@type'             => 'ImageObject',
                'url'               => Vite::asset('resources/images/schema-image.jpg')
            ]
        ],
        'datePublished'     => $publish_date,
        'dateModified'      => $Page->updated_at ? $Page->updated_at->format("Y-m-d") : '',
    ];
    if (!empty($Page->image) && !empty($Page->image['social_share'])) {
        $schema_arr['image'] = [
            '@type'     => 'ImageObject',
            'url'       => $Page->image['social_share']['src'],
            'width'     => $Page->image['social_share']['width'],
            'height'     => $Page->image['social_share']['height']
        ];
    }
    if ($Page->author) {
        $schema_arr['author'] = [
            '@type'     => 'Person',
            'name'      => $Page->author->title
        ];
    }
@endphp
<script type="application/ld+json">
{!! json_encode($schema_arr, JSON_PRETTY_PRINT) !!}
</script>
@endif