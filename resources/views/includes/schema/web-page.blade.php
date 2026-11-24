@if( isset($Page) && $Page )
@php
    $desc = $Page->url_record ? $Page->url_record->meta_description : '';
    $schema_arr = [
        '@context'      => 'http://schema.org',
        '@type'         => 'WebPage',
        'name'          => $Page->h1_title ? $Page->h1_title : $Page->title,
        'description'   => \Illuminate\Support\Str::limit($desc, 200),
        'datePublished' => $Page->created_at ? $Page->created_at->format("Y-m-d") : '',
        'dateModified'  => $Page->updated_at ? $Page->updated_at->format("Y-m-d") : '',
    ];
@endphp
<script type="application/ld+json">
{!! json_encode($schema_arr, JSON_PRETTY_PRINT) !!}
</script>
@endif