export function bindCMSFields(wrapper){
    if (typeof(wrapper) == 'undefined') {
        wrapper = document;
    }

    const urlPathFieldWrap = wrapper.querySelector('[data-url-path-field]');
    if (urlPathFieldWrap) {
        import('../fields/path')
            .then(({ bindPathField }) => {
                bindPathField(urlPathFieldWrap);
                wrapper.dispatchEvent(new Event('path-fields-bound'));
            });
    }

    const linkFields = wrapper.querySelectorAll('[data-link-field]');
    if (linkFields.length) {
        import('../fields/link')
            .then(({ bindLinkField }) => {
                for (const linkField of linkFields) {
                    bindLinkField(linkField);
                }
                wrapper.dispatchEvent(new Event('link-fields-bound'));
            });
    }

    const imageFields = wrapper.querySelectorAll('[data-image-tile]');
    if (imageFields.length) {
        import('../fields/image')
            .then(({ bindImageField }) => {
                for (const imageField of imageFields) {
                    bindImageField(imageField);
                }
                wrapper.dispatchEvent(new Event('image-fields-bound'));
            });
    }

    const fileFields = wrapper.querySelectorAll('[data-file-field]');
    if (fileFields.length) {
        import('../fields/file')
            .then(({ bindFileField }) => {
                for (const fileField of fileFields) {
                    bindFileField(fileField);
                }
                wrapper.dispatchEvent(new Event('file-fields-bound'));
            });
    }

    const osFields = wrapper.querySelectorAll('[data-orderable-select]');
    if (osFields.length) {
        import('../fields/orderable-select')
            .then(({ bindSortable }) => {
                for (const osField of osFields) {
                    bindSortable(osField);
                }
                wrapper.dispatchEvent(new Event('orderable-select-fields-bound'));
            });
    }

    const colourFields = wrapper.querySelectorAll('[data-colour-picker]');
    if (colourFields.length) {
        import('../fields/colour')
            .then(({ bindColourPickers }) => {
                bindColourPickers();
                wrapper.dispatchEvent(new Event('colour-fields-bound'));
            });
    }

    const relationFields = wrapper.querySelectorAll('[data-relation-field]');
    if (relationFields.length) {
        import('../fields/relation')
            .then(({ bindRelationField }) => {
                for (const relationField of relationFields) {
                    bindRelationField(relationField);
                }
                wrapper.dispatchEvent(new Event('relation-fields-bound'));
            });
    }

    const relationSearchFields = wrapper.querySelectorAll('[data-relation-search-field]');
    if (relationSearchFields.length) {
        import('../fields/relation-search')
            .then(({ bindSelectSearchField }) => {
                for (const searchField of relationSearchFields) {
                    bindSelectSearchField(searchField);
                }
                wrapper.dispatchEvent(new Event('relation-search-fields-bound'));
            });
    }

    const repeatableFields = wrapper.querySelectorAll('[data-repeatable-field]');
    if (repeatableFields.length) {
        import('../fields/repeatable')
            .then(({ bindRepeatableField }) => {
                for (const repeatableField of repeatableFields) {
                    bindRepeatableField(repeatableField);
                }
                wrapper.dispatchEvent(new Event('repeatable-fields-bound'));
            });
    }

    const pageBuilderFields = wrapper.querySelectorAll('[data-page-builder]');
    if (pageBuilderFields.length) {
        import('../fields/page-builder')
            .then(({ bindPageBuilder }) => {
                for (const pageBuilderField of pageBuilderFields) {
                    bindPageBuilder(pageBuilderField);
                }
                wrapper.dispatchEvent(new Event('page-builders-bound'));
            });
    }

    const formStructureFields = wrapper.querySelectorAll('[data-form-structure]');
    if (formStructureFields.length) {
        import('../fields/form-structure')
            .then(({ bindFormStructureField }) => {
                for (const formStructureField of formStructureFields) {
                    bindFormStructureField(formStructureField);
                }
                wrapper.dispatchEvent(new Event('form-structures-bound'));
            });
    }

    const toggleFields = wrapper.querySelectorAll('[data-field][data-toggles]');
    if (toggleFields.length) {
        for (const toggleField of toggleFields) {
            bindToggleField(toggleField);
        }
    }

    // For good measure, reload WYSIWYGs
    if (typeof(window.loadWysiwygs) != 'undefined') {
        window.loadWysiwygs();
    }

    document.dispatchEvent(new Event('cms-fields-bound'));
}

export function changeFieldPrefixes(wrapper, from_prefix, to_prefix, reset_values) {
    if (typeof(reset_values) == 'undefined') {
        reset_values = false;
    }

    for (const inpt of wrapper.querySelectorAll('input, textarea, select')) {
        if (inpt.hasAttribute('name')) {
            inpt.setAttribute('name', inpt.getAttribute('name').replace(from_prefix, to_prefix));
        }

        if (inpt.hasAttribute('id')) {
            inpt.setAttribute('id', inpt.getAttribute('id').replace(from_prefix, to_prefix));
        }

        if (reset_values) {
            if (['checkbox', 'radio'].includes(inpt.getAttribute('type'))) {
                inpt.checked = false;
            } else {
                inpt.value = '';
            }
        }
    }

    for (const label of wrapper.querySelectorAll('label')) {
        if (label.hasAttribute('for')) {
            label.setAttribute('for', label.getAttribute('for').replace(from_prefix, to_prefix));
        }
    }

    for (const repeatableField of wrapper.querySelectorAll('[data-repeatable-field]')) {
        repeatableField.setAttribute('data-repeatable-field', repeatableField.dataset.repeatableField.replace(from_prefix, to_prefix));
    }
}
function bindToggleField(fieldWrap) {
    if (fieldWrap.dataset.toggleable) { return; }

    const toggleableField = fieldWrap.querySelector('[name="' + fieldWrap.dataset.field + '"]');
    if (!toggleableField) { return; }

    toggleableField.addEventListener('change', () => {
        const toggleSettings = JSON.parse(fieldWrap.dataset.toggles);
        for (const toggleSetting of toggleSettings) {
            if (
                typeof toggleSetting.when == 'undefined' &&
                typeof toggleSetting.show == 'undefined' && 
                typeof toggleSetting.hide == 'undefined'
            ) { continue; }

            if(toggleSetting.when != toggleableField.value) { continue; }
            
            const showFields = [];
            const hideFields = [];

            if (typeof toggleSetting.show != 'undefined') {
                for (const show_field_name of toggleSetting.show) {
                    const showField = document.querySelector('[data-field="' + show_field_name + '"]');
                    if (showField) {
                        showFields.push(showField);
                    }
                }
            }
            if (typeof toggleSetting.hide != 'undefined') {
                for (const hide_field_name of toggleSetting.hide) {
                    const hideField = document.querySelector('[data-field="' + hide_field_name + '"]');
                    if (hideField) {
                        hideFields.push(hideField);
                    }
                }
            }

            /* 
             * If the toggle field is set to the value we're looking for,
             * show the show fields & hide the hide fields
            */
            if (toggleSetting.when == toggleableField.value) {
                for (const showField of showFields) {
                    showField.classList.remove('hidden');
                }
                for (const hideField of hideFields) {
                    hideField.classList.add('hidden');
                }
            }
            /*
            * Else, do the reverse and hide the show fields & show
            * the hide fields
            */ 
            else {
                for (const showField of showFields) {
                    showField.classList.add('hidden');
                }
                for (const hideField of hideFields) {
                    hideField.classList.remove('hidden');
                }
            }
        }
    });
    // Dispatch the event to auto-show/hide fields based on current state
    toggleableField.dispatchEvent(new Event('change'));

    fieldWrap.dataset.toggleable = true;
}