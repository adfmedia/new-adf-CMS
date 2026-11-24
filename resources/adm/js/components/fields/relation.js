export function bindRelationField(relationField) {
    const channel_name = relationField.getAttribute('data-relation-field').replaceAll('\\', '-');
    const fieldBroadcast = new BroadcastChannel(channel_name);
    fieldBroadcast.onmessage = (e) => {
        const class_name = fieldBroadcast.name.replaceAll('-', '\\');

        const data = new FormData();
        data.append('_token', document.querySelector('[name="csrf-token"]').getAttribute('content'));
        data.append('field', 'relation');
        data.append('method', 'getDropdownOptions');
        data.append('relation_class', class_name);
        data.append('relation_config', relationField.dataset.relationConfig);

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
            let existing_values = [];
            let placeholderOpt;
            for (const option of relationField.querySelectorAll('option')) {
                existing_values.push(option.value);
                if (option.value == 0) {
                    placeholderOpt = option;
                }
            }

            if (json.success && json.opts) {
                let all_values = [];
                for (const option_id in json.opts) {
                    all_values.push(option_id);
                    const opt_label = json.opts[option_id];

                    // If the value is already there, make sure it's title is up to date
                    if (existing_values.includes(option_id)) { 
                        if (option_id > 0) {
                            for (const option of relationField.querySelectorAll('option')) {
                                if (option.value == option_id) {
                                    option.innerText = opt_label;
                                }
                            }
                        }
                        continue; 
                    }

                    // Otherwise, it's a new value - so insert it at the top
                    const opt_html = `<option value="${option_id}">${opt_label}</option>`;
                    const opt_dom = new DOMParser().parseFromString(opt_html, 'text/html').body.childNodes[0];
                    if (placeholderOpt) {
                        placeholderOpt.insertAdjacentElement('afterend', opt_dom);
                    } else {
                        relationField.prepend(opt_dom);
                    }
                }

                // And if a related item has been deleted, remove it from the dropdown
                for (const option of relationField.querySelectorAll('option')) {
                    if (!all_values.includes(option.value)) {
                        option.remove();
                    }
                }
            }
        })
        .catch((err) => {});
    };
}