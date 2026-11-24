/* Import TinyMCE */
import tinymce from 'tinymce';

/* Default icons are required. After that, import custom icons if applicable */
import 'tinymce/icons/default/icons.min.js';

/* Required TinyMCE components */
import 'tinymce/themes/silver/theme.min.js';
import 'tinymce/models/dom/model.min.js';

/* Import a skin (can be a custom skin instead of the default) */
import 'tinymce/skins/ui/oxide/skin.js';

/* Import plugins */
import 'tinymce/plugins/lists';
import 'tinymce/plugins/link';
import 'tinymce/plugins/image';
import 'tinymce/plugins/code';
import 'tinymce/plugins/table';
import 'tinymce/plugins/wordcount';

/* content UI CSS is required */
import 'tinymce/skins/ui/oxide/content.js';

let CACHED_LINKS    = false;

export function bindTinymce() {
    window.loadWysiwygs = () => {
        tinymceInit();
        dynamicLinkList();
    };

    window.loadWysiwygs();
}

function tinymceInit() {
    tinymce.init({
        license_key: 'gpl',
        selector: '[data-wysiwyg]',
        plugins: 'lists,link,image,code,table,wordcount',
        menubar: true,
        toolbar: 'bold italic underline | bullist numlist | link image',
        paste_as_text: true,
        image_caption: true,

        skin_url: 'default',
        
        relative_urls: false,
        remove_script_host: false,
        document_base_url: WYSIWYG_BASE_URL,

        content_css: WYSIWYG_APP_CSS,
        body_class: WYSIWYG_BODY_CLASS,

        file_picker_callback: fileManagerPickerCallback,
        link_list: dynamicLinkList,

        extended_valid_elements: 'img[loading=lazy|src|sizes|srcset|usemap|ismap|alt=|width=|height=|class|name|longdesc|align|border|hspace|vspace]',

        formats: {
            cite: { block: 'cite' }
        },
        style_formats: [
            { title: 'Headings', items: [
                { title: 'H2', block: 'h2' },
                { title: 'H3', block: 'h3' },
                { title: 'H4', block: 'h4' },
                { title: 'H5', block: 'h5' },
                { title: 'H6', block: 'h6' }
            ]},
            { title: 'Inline', items: [
                { title: 'Bold', inline: 'bold' },
                { title: 'Italic', inline: 'italic' },
                { title: 'Underline', inline: 'underline' },
                { title: 'Strikethrough', inline: 'strikethrough' },
                { title: 'Superscript', inline: 'superscript' },
                { title: 'Subscript', inline: 'subscript' },
            ]},
            { title: 'Blocks', items: [
                { title: 'Paragraph', format: 'p' },
                { title: 'Blockquote', format: 'blockquote' },
                { title: 'Citation', format: 'cite' },
                { title: 'Div', format: 'div' },
                { title: 'Pre', format: 'pre' }
            ]},
            { title: 'Align', items: [
                { title: 'Left', format: 'alignleft' },
                { title: 'Center', format: 'aligncenter' },
                { title: 'Right', format: 'alignright' },
                { title: 'Justify', format: 'alignjustify' }
            ]}
        ],
        
        init_instance_callback: (editor) => {
            editor.on('focusout', () => {
                const previewPane = document.querySelector('[data-preview-pane]');
                if (previewPane) {
                    editor.save();
                    editor.getElement().dispatchEvent(new Event('change'));
                }
            });
        }
    });
}

function fileManagerPickerCallback(callback, value, meta) {
    const imageFileManager = window.getFileManager('modal');
    const imageFmModal = document.querySelector('[data-modal="image-file-manager"]');

    if (imageFileManager && imageFmModal) {
        const evtTarget = document.getElementById('wysiwyg-evt-target');

        let type = 'file';
        let scope = 'file';

        if (meta.filetype == 'image') {
            type = 'image';
            scope = 'image';
        }

        imageFileManager.initWithTarget(
            evtTarget,
            type,
            scope,
            //tileSelect.dataset.file, 
            //tileSelect.dataset.folder
        );
        imageFmModal.classList.add('open', 'wysiwyg-opened');

        evtTarget.removeEventListener('file-selected', fileManagerFileSelected);
        evtTarget.removeEventListener('image-selected', fileManagerFileSelected);
        evtTarget.addEventListener('file-selected', fileManagerFileSelected.bind(null, callback, value, meta, imageFmModal));
        evtTarget.addEventListener('image-selected', fileManagerFileSelected.bind(null, callback, value, meta, imageFmModal));
    }
}

function fileManagerFileSelected(callback, value, meta, modal, evt) {
    if (evt.detail && evt.detail.file) {
        // Load file details
        const selected_file_id = parseInt(evt.detail.file);

        const data = new FormData();
        data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
        data.append('field', 'wysiwyg');
        data.append('method', 'getFileDetails');
        data.append('file', selected_file_id);

        fetch(
            '/adm/ajax/tools/field_method',
            {
                method: 'post',
                body: data,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        )
        .then(resp => resp.json())
        .then(json => {
            modal.classList.remove('open', 'wysiwyg-opened');
            callback(
                json.src, {
                    alt: json.alt,
                    width: json.width + '',
                    height: json.height + ''
                }
            );
        });
    }
}

function dynamicLinkList(success) {
    if (CACHED_LINKS) {
        if (success) {
            success(CACHED_LINKS);
        }
        return;
    }

    const data = new FormData();
    data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
    data.append('field', 'link');
    data.append('method', 'getTinymceLinkList');

    fetch(
        '/adm/ajax/tools/field_method',
        {
            method: 'post',
            body: data,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }
    )
    .then(resp => resp.json())
    .then(json => {
        CACHED_LINKS = json.links;
        if (success) {
            success(CACHED_LINKS);
        }
    });
}