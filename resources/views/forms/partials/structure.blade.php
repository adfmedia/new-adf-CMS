<div class="space-y-4">
    @foreach( $form->structure['rows'] as $row )
        @php
            $row_grid = 'grid-cols-1';
            $col_count = sizeof($row['columns']);
            if ($col_count == 2) { $row_grid .= ' md:grid-cols-2'; }
            elseif ($col_count == 3) { $row_grid .= ' md:grid-cols-3'; }
            elseif ($col_count >= 4) { $row_grid .= ' md:grid-cols-4'; }
        @endphp
        <div class="grid {{ $row_grid }} space-x-4">
            @foreach( $row['columns'] as $column )
                <div class="space-y-4">
                    @foreach( $column['fields'] as $field )
                        @include('forms.partials.field')
                    @endforeach
                </div>
            @endforeach
        </div>
    @endforeach

    @if( $form->marketing_opt_in && !empty($form->marketing_opt_in_text) )
        @php $opt_in_id = uniqid(); @endphp
        <div class="flex items-start">
            <div class="flex h-6 items-center">
                <input
                    type="checkbox" name="opt_in" value="1" id="marketing-opt-in-{{ $opt_in_id }}"
                    class="h-4 2-4 rounded-sm border-gray-300 text-primary-600 focus:ring-primary-600"
                >
            </div>
            <div class="ml-3 text-sm leading-6">
                <label for="marketing-opt-in-{{ $opt_in_id }}" class="font-semibold text-gray-900">{!! $form->marketing_opt_in_text_privacy_linked !!}</label>
                @if( !empty($form->marketing_opt_out_text) )
                    <p class="text-gray-500">{!! $form->marketing_opt_out_text_privacy_linked !!}</p>
                @endif
            </div>
        </div>
    @endif

    <div class="flex">
        <button type="submit" class="btn">{{ !empty($form->submit_text) ? $form->submit_text : 'Submit' }}</button>
    </div>
    @if( $form->use_recaptcha && config('services.recaptcha.show_badge') )
    <div class="flex items-center mt-2">
        <a 
            href="https://policies.google.com/privacy?hl=en" target="_blank" 
            class="flex items-center justify-center text-xs space-x-2"
        >
            <span>protected by</span>
            <img 
                src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACgAAAAoCAMAAAC7IEhfAAAA/1BMVEUAAABygLUtX9E/hvSzs7MaNayzs7Ozs7Ozs7M9gvGzs7MaNaw/hvQaNayzs7M+hfM/hvSzs7Ozs7Ozs7Ozs7Ozs7MaNaw6e+oaNayzs7Ozs7MaNayzs7MaNayzs7Ozs7M/hvSzs7M8gO+zs7MaNayzs7Ozs7Ozs7M/hvSzs7MaNayzs7M3deWzs7M4eOgaNawaNaw/hvQ9gvGzs7M4d+c4duazs7MaNaw/hvQ2c+M/hvSzs7Ozs7M/hvQaNawaNaw/hvSzs7MaNayzs7M/hvSzs7MaNaw/hvQaNawzbN0aNawwZNazs7MaNawaNayzs7MzbN2zs7MaNaw/hvQ9gfAZZSlxAAAAUXRSTlMAAwXwBaXzsqQJ7bOy82n0vpJzGxAL/vbu3tXVzMHAqKGZhYNyYFlJQT0oJSIgGRAM/v787ezi393Zvbe0rqmRjouFeGtjY2FNREA6KiIbFRM194giAAABYUlEQVR4AY3Q156aUBSF8UURFDXGotGU2HvRlOm993Jg3v9ZZoTtEfYIP76rdfG/WgDUjBPszc7upM/vVfCklNCtejIKlwSpSuJZEikZpLLXBNLwes0xKEuorrM9N9tyOJT9mi+dB1P/HQYDcpG2Ce45UdCu2gRvHA5ZBGe5mLDhsHK3nePsZzj5w6EGYNFhFDj1kW93+wSBpx9BqGg+N0Hq6wpiHpAYrN3fB2ApCWJU8cPWGrYAVxJUErYfZqTTUvDkUdAR3JWwAUolF4SahJegyDHoyAbMMShYSQWbi+sQ1yGuQ5Qbh8HHoCsaFyHwe8nvSj+FKDAo5RSy4ocT4guHlFEow03vWkKQ5JBovdDv9/LbgpJSREQyLhRdCa1omF9Bs/w7yh0oBE0dpQhp0g9LB+j1MNfU4WXSuDI2MauHVeSAYlLw/jVfsKlx3vIz62yKsPRh+7BmCGHUzPZQgb93L9c5w0BCxz8AAAAASUVORK5CYII=" 
                alt="reCAPTCHA Badge" 
                width="20"
                height="20"
                loading="lazy"
            >
            <span>reCAPTCHA</span>
        </a>
    </div>
    @endif
</div>