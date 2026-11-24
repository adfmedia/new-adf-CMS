@if( isset( $breadcrumbs ) && !empty( $breadcrumbs ) )
@php
    $schema_arr = [
        '@context'          => 'http://schema.org',
        '@type'             => 'BreadcrumbList',
        'itemListElement'   => []
    ];
    foreach ($breadcrumbs as $idx => $crumb) {
        $schema_arr['itemListElement'][] = [
            '@type'     => 'ListItem',
            'position'  => $idx,
            'item'      => [
                '@id'       => $crumb['url'],
                'name'      => $crumb['text']
            ]
        ];
    }
@endphp
<script type="application/ld+json">
{!! json_encode($schema_arr, JSON_PRETTY_PRINT) !!}
</script>
@endif