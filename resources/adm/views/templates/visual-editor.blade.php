<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    @include('includes.structure.meta')
    <body class="h-full">
        <div class="bg-gray-100 min-h-full flex flex-col">
            {{-- Settings bar --}}
            <div class="flex items-center px-6 py-3 border-b border-gray-300">
                <div class="w-1/3 flex items-center">
                    @if( !empty($back_button) && $back_button['enabled'] )
                        <a href="{{ $back_button['url'] }}" class="btn">{{ $back_button['text'] }}</a>
                    @endif
                </div>
                <div class="w-1/3 flex items-center justify-center">
                    <div class="flex items-stretch bg-white rounded-md border border-gray-300 shadow-sm overflow-hidden">
                        <a
                            data-preview-resizer data-width="w-preview-mobile"
                            href="#" title="Preview on mobile"
                            class="size-10 flex items-center justify-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                            </svg>
                        </a>
                        <a
                            data-preview-resizer data-width="w-preview-tablet"
                            href="#" title="Preview on tablet"
                            class="size-10 flex items-center justify-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-15a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 4.5v15a2.25 2.25 0 0 0 2.25 2.25Z" />
                            </svg>
                        </a>
                        <a
                            data-preview-resizer data-width="w-preview-laptop"
                            href="#" title="Preview on laptop"
                            class="size-10 flex items-center justify-center"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor" class="size-4">
                                <path d="M80-160q-33 0-56.5-23.5T0-240h160q-33 0-56.5-23.5T80-320v-440q0-33 23.5-56.5T160-840h640q33 0 56.5 23.5T880-760v440q0 33-23.5 56.5T800-240h160q0 33-23.5 56.5T880-160H80Zm400-40q17 0 28.5-11.5T520-240q0-17-11.5-28.5T480-280q-17 0-28.5 11.5T440-240q0 17 11.5 28.5T480-200ZM160-320h640v-440H160v440Zm0 0v-440 440Z"/>
                            </svg>
                        </a>
                        <a
                            data-preview-resizer data-width="w-full"
                            href="#" title="Preview on desktop"
                            class="size-10 flex items-center justify-center bg-primary-medium text-white"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" fill="currentColor" class="size-4">
                                <path d="M320-120v-80h80v-80H160q-33 0-56.5-23.5T80-360v-400q0-33 23.5-56.5T160-840h640q33 0 56.5 23.5T880-760v400q0 33-23.5 56.5T800-280H560v80h80v80H320ZM160-360h640v-400H160v400Zm0 0v-400 400Z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <div class="w-1/3 flex items-center justify-end gap-2">
                    @if( !empty($save_back_button) && $save_back_button['enabled']) 
                        <button
                            type="submit" form="cms_form"
                            name="save_go_back"
                            class="btn btn--save"
                        >{!! $save_back_button['text'] !!}</button>
                    @endif
                    @if( !empty($save_continue_button) && $save_continue_button['enabled'] )
                        <button
                            type="submit" form="cms_form"
                            name="save_continue"
                            class="btn btn--save"
                        >{!! $save_continue_button['text'] !!}</button>
                    @endif
                </div>
            </div>
            {{-- Wrapper --}}
            <div class="flex-1 flex items-stretch">
                {{-- Sidebar (fields) --}}
                <div class="w-full lg:w-1/4 lg:border-r border-gray-300 relative">
                    <div class="absolute inset-0 w-full h-full overflow-y-auto overflow-x-hidden p-6">
                        @yield('content')
                    </div>
                </div>
                {{-- Preview pane --}}
                <div class="w-full lg:w-3/4 flex justify-center">
                    <div data-preview-sizer class="w-full h-full relative">
                        <iframe
                            data-preview-pane data-src="{{ $preview_button['url'] }}"
                            name="preview-pane" id="preview-pane"
                            class="absolute inset-0 w-full h-full"
                        ></iframe>
                        <div data-preview-loader class="absolute inset-0 w-full h-full flex items-center justify-center bg-white/50">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-12 animate-spin">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    @include('includes.structure.footer-scripts')
</html>