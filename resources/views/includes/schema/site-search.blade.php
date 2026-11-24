@php
    $SearchPage = \App\Models\Url::getSystemPage('search', 'index');
@endphp
@if( $SearchPage && in_array('Search', config('modules.available')) )
@php
    $schema_arr = [
        '@context'          => 'http://schema.org',
        '@type'             => 'WebSite',
        'url'               => route('theme'),
        'potentialAction'   => [
            '@type'             => 'SearchAction',
            'target'            => $SearchPage->url . '?search={search_term_string}',
            'query-input'       => 'required name=search_term_string'
        ]
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($schema_arr, JSON_PRETTY_PRINT) !!}
</script>
@endif