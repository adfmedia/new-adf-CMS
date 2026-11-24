@php 
    $url = data_get($Item, $field);
    $output_url = '/' . ltrim($url, '/');
    if (strlen($url) > 50) {
        $output_url = substr($output_url, 0, 26) . '...' . substr($output_url, -25);
    }
@endphp
<a 
    href="{{ rtrim($column['base_url'], '/') . '/' . $url }}"
    target="_blank"
    class="text-primary-medium transition-colors hover:text-primary-accent"
>{{ $output_url }}</a>