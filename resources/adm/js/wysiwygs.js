window.addEventListener('load', () => {
    import('./components/generic/wysiwyg-config')
        .then(({ bindTinymce }) => {
            bindTinymce();
        });
});