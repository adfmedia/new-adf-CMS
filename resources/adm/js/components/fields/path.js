export function bindPathField(elWrapper) {
    const urlField = elWrapper.querySelector('[data-url-field]');
    const urlGenFields = getGenerationFieldsFromElementAttribute(elWrapper.dataset.urlGenField, elWrapper);

    if (urlField && urlGenFields.length) {
        if (urlField.value) {} // We don't need to do anything if the URL field is already populated
        else {
            let urlManuallyChanged = false;
            urlField.addEventListener('keyup', () => {
                urlManuallyChanged = true;
            });

            let genFieldTimer;
            for (const urlGenField of urlGenFields) {
                urlGenField.addEventListener('keyup', () => {
                    clearTimeout(genFieldTimer);
                    genFieldTimer = setTimeout(() => {
                        let url = urlManuallyChanged ? urlField.value : getArrayFieldValuesAsString(urlGenFields, '-');
                        let regex_pattern = /[^a-z\d\s&-]/g;    // Replace anything not a-z, number, spaces, ampersands or dashes
                        if (urlGenField.dataset.allowSlashes == 'true') {
                            regex_pattern = /[^a-z\d\s&-\/]/g;  // If allowing slashes, include them in the character set
                        }
                        url = url.toLowerCase();
                        url = url.replaceAll(regex_pattern, '');
                        url = url.replaceAll(' ', '-');
                        url = url.replaceAll('&', '-and-');
                        url = url.replaceAll('--', '-');

                        urlField.value = url;
                        urlField.dispatchEvent(new Event('change'));
                    }, 100);
                });
            }
        }
    }

    const metaTitleField = elWrapper.querySelector('[data-meta-field="title"]');
    const metaTitleGenFields = getGenerationFieldsFromElementAttribute(elWrapper.dataset.metaTitleGenField, elWrapper);

    const metaDescField = elWrapper.querySelector('[data-meta-field="desc"]');
    const metaDescGenFields = getGenerationFieldsFromElementAttribute(elWrapper.dataset.metaDescGenField, elWrapper);

    let metaManuallyChanged = false;
    const metaGenFields = [];
    if (metaTitleField && metaTitleGenFields.length) {
        metaGenFields.push({
            destField: metaTitleField,
            genFields: metaTitleGenFields
        });
    }
    if (metaDescField && metaDescGenFields.length) {
        metaGenFields.push({
            destField: metaDescField,
            genFields: metaDescGenFields
        });
    }
    if (metaGenFields.length) {
        for (const gen_field_conf of metaGenFields) {
            const targetField = gen_field_conf.destField;
            if (targetField && !targetField.value.length) {
                targetField.addEventListener('keyup', () => {
                    metaManuallyChanged = true;
                });

                for (const genField of gen_field_conf.genFields) {
                    genField.addEventListener('change', () => {
                        if (!metaManuallyChanged) {
                            targetField.value = getArrayFieldValuesAsString(gen_field_conf.genFields, ' ').replace(/\r?\n|\r/gm, " ");;
                            targetField.dispatchEvent(new Event('change'));
                        }
                    });
                }
            }
        }
    }

    const prefixLabel = elWrapper.querySelector('[data-prefix-label]');
    if (prefixLabel) {
        const prefixField = document.querySelector('.input-wrap [name="' + prefixLabel.dataset.prefixField + '"]');
        if (prefixField) {
            const hiddenPrefixField = elWrapper.querySelector('[data-hidden-prefix-field]');
            const basePrefix = elWrapper.querySelector('[data-base-prefix]');
            const seoPrefix = elWrapper.querySelector('[data-seo-prefix]');

            prefixField.addEventListener('change', () => {
                const data = new FormData();
                data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
                data.append('field', 'path');
                data.append('method', 'getPathPrefixForForm');
                data.append('prefix_class', prefixLabel.dataset.prefixClass);
                data.append('prefix_id', prefixField.value);
                data.append('language_id', prefixLabel.dataset.language);

                if (basePrefix) {
                    data.append('base_prefix', basePrefix.innerText);
                }

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
                    if (typeof(json.prefix) != 'undefined') {
                        let prefix_url = '';
                        if (basePrefix) {
                            prefix_url += basePrefix.innerText;
                        }
                        if (json.prefix) {
                            prefix_url += json.prefix;
                        }
                        if (prefix_url.endsWith('/')) {
                            prefix_url = prefix_url.slice(0, -1);
                        }

                        if (hiddenPrefixField) {
                            hiddenPrefixField.value = prefix_url;
                        }
                        if (seoPrefix) {
                            const prefix_parts = prefix_url.length ? prefix_url.split('/') : [];
                            seoPrefix.innerHTML = '';
                            for (const prefix_part of prefix_parts) {
                                seoPrefix.innerHTML += `
                                    <span>${prefix_part}</span>
                                    <span>&#8250;</span>
                                `;
                            }
                        }
                        prefixLabel.innerText = json.prefix ? json.prefix + '/' : '';
                    }
                });
            });
        }
    }

    const partsWrap = document.querySelector('[data-url-parts]');
    if(partsWrap) {
        let url = '';
        if(!parseInt(partsWrap.dataset.urlParts)) {
            return;
        }
        const parts = partsWrap.querySelectorAll('[data-part]');
        for(const part of parts) {
            url+= part.innerText;
        }
        partsWrap.href = url;
    }
};

function getGenerationFieldsFromElementAttribute(attribute, elWrapper) {
    if (!attribute || !attribute.length) { return []; }

    const field_names = JSON.parse(attribute);
    const genFields = [];
    for (const gen_field_name of field_names) {
        genFields.push(elWrapper.closest('form').querySelector('[name="' + gen_field_name + '"]'));
    }
    return genFields;
}

function getArrayFieldValuesAsString(arrayFields, join_character) {
    let str_values = [];
    for (const field of arrayFields) {
        if (!field.value.length) { continue; }

        str_values.push(field.value);
    }

    return str_values.join(join_character);
}