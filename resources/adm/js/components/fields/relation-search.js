export function bindSelectSearchField(selectSearchField) {
    const inputField = selectSearchField.querySelector('[data-search]');
    const selectField = selectSearchField.querySelector('select');

    if(inputField && selectField) {
        selectField.classList.add("max-h-[250px]");
        const selectLookup = () => {
            const searchValue = inputField.value;
            const options = selectField.querySelectorAll('option');
            if(searchValue.length >= 2) {
                for(const option of options) {
                    if((option.innerText).toLowerCase().includes(searchValue.toLowerCase())) {
                        option.classList.remove('hidden');
                    }
                    else {
                        option.classList.add('hidden');
                    }
                }
            }
            else {
                for(const option of options) {
                    option.classList.remove('hidden');
                    selectField.setAttribute('size', options.length);
                }
            }
        };

        const resetSelect = () => {
            inputField.value = "";
            inputField.setAttribute('placeholder', '');
            selectField.setAttribute('size', 1);
            selectField.style.top = 0;
            selectField.style.zIndex = -1;
            selectField.removeAttribute('data-focus');

            const options = selectField.querySelectorAll('options');
            for(const option of options) {
                option.classList.remove('hidden');
            }
            const icon = selectSearchField.querySelector('[data-icon]');
            if(icon) {
                icon.classList.add('hidden');
            }
        }

        inputField.addEventListener('change', function(){
            selectLookup();
        });
        inputField.addEventListener('keyup', function() {
            selectLookup();
        });

        inputField.addEventListener('focus', function() {
            if(selectField.hasAttribute('data-focus')) { return false; }
            resetSelect();
            inputField.setAttribute('placeholder', 'Search for an option');
            selectField.setAttribute('data-focus', 1);
            const icon = selectSearchField.querySelector('[data-icon]');
            if(icon) {
                icon.classList.remove('hidden');
            }

            selectField.style.top = inputField.offsetHeight + "px";
            selectField.style.zIndex = 3;
            const options = selectField.querySelectorAll('option');

            if(options) {
                selectField.setAttribute('size', options.length)

                for(const option of options) {
                    option.onclick = () => {
                        if(option.selected) {
                            resetSelect();
                        }
                    }
                    if(option.selected) {
                        selectField.scrollTo(0, option.offsetTop - 20);
                    }
                }
            }
            selectLookup();
        });

        selectField.onchange = () => {
            resetSelect();
        }

        inputField.addEventListener('blur', function(e) {
            if(e.relatedTarget != selectField) {
                resetSelect();
            }
        });
        selectField.addEventListener('blur', function(e) {
            if(e.relatedTarget != inputField) {
                resetSelect();
            }
        });
    }
}