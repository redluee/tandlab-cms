(function () {
    if (!document.body.classList.contains('is-editing')) {
        return;
    }

    var state = {
        settings: {},
        tandwerk: { create: {}, update: {}, delete: [], order: null },
        team: { create: {}, update: {}, delete: [], order: null }
    };

    var originalValues = new WeakMap();
    var activeField = null;
    var toolbarEl = null;
    var tmpCounter = 0;

    function csrfTokenValue() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function parseRef(ref) {
        var parts = ref.split(':');
        if (parts[0] === 'setting') {
            return { kind: 'setting', key: parts.slice(1).join(':') };
        }
        return { kind: parts[0], id: parts[1], field: parts[2] };
    }

    function isTmpId(id) {
        return typeof id === 'string' && id.indexOf('new-') === 0;
    }

    function markFieldChange(kind, id, field, value) {
        var bucket = state[kind];
        if (!bucket) {
            return;
        }
        if (isTmpId(id)) {
            bucket.create[id] = bucket.create[id] || {};
            bucket.create[id][field] = value;
        } else {
            bucket.update[id] = bucket.update[id] || {};
            bucket.update[id][field] = value;
        }
        updateChangeCount();
    }

    function markDelete(kind, id) {
        var bucket = state[kind];
        if (!bucket) {
            return;
        }
        if (isTmpId(id)) {
            delete bucket.create[id];
        } else {
            delete bucket.update[id];
            var numericId = Number(id);
            if (bucket.delete.indexOf(numericId) === -1) {
                bucket.delete.push(numericId);
            }
        }
        updateChangeCount();
    }

    function applyFieldChange(ref, value) {
        var parsed = parseRef(ref);
        if (parsed.kind === 'setting') {
            state.settings[parsed.key] = value;
            updateChangeCount();
        } else {
            markFieldChange(parsed.kind, parsed.id, parsed.field, value);
        }
    }

    function countChanges() {
        var n = Object.keys(state.settings).length;
        ['tandwerk', 'team'].forEach(function (kind) {
            var bucket = state[kind];
            n += Object.keys(bucket.create).length;
            n += Object.keys(bucket.update).length;
            n += bucket.delete.length;
            if (bucket.order) {
                n += 1;
            }
        });
        return n;
    }

    function hasChanges() {
        return countChanges() > 0;
    }

    function updateChangeCount() {
        var el = document.getElementById('editor-change-count');
        if (!el) {
            return;
        }
        var n = countChanges();
        el.textContent = n + ' wijziging' + (n === 1 ? '' : 'en');
    }

    function showToast(message, kind) {
        var toast = document.createElement('div');
        toast.className = 'editor-toast editor-toast--' + kind;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(function () { toast.remove(); }, 4000);
    }

    // --- Text / richtext editing ---

    function makeToolbarButton(html, onClick, title) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = html;
        if (title) {
            btn.title = title;
        }
        btn.addEventListener('mousedown', function (event) { event.preventDefault(); });
        btn.addEventListener('click', onClick);
        return btn;
    }

    var LINK_ICON_SVG = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" '
        + 'stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        + '<path d="M10 13a5 5 0 0 0 7.07 0l2.83-2.83a5 5 0 0 0-7.07-7.07L11.5 4.5"></path>'
        + '<path d="M14 11a5 5 0 0 0-7.07 0L4.1 13.83a5 5 0 0 0 7.07 7.07L12.5 19.5"></path>'
        + '</svg>';

    function positionToolbar(el) {
        var rect = el.getBoundingClientRect();
        toolbarEl.style.top = Math.max(4, window.scrollY + rect.top - 42) + 'px';
        toolbarEl.style.left = (window.scrollX + rect.left) + 'px';
    }

    function showToolbar(el, type) {
        hideToolbar();
        toolbarEl = document.createElement('div');
        toolbarEl.className = 'editor-toolbar';

        toolbarEl.appendChild(makeToolbarButton('<b>B</b>', function () { document.execCommand('bold'); }, 'Vet'));
        toolbarEl.appendChild(makeToolbarButton('<i>I</i>', function () { document.execCommand('italic'); }, 'Cursief'));

        if (type === 'richtext') {
            toolbarEl.appendChild(makeToolbarButton(LINK_ICON_SVG, function () {
                var url = prompt('Link-URL (http(s)://, mailto: of tel:)', 'https://');
                if (url) {
                    document.execCommand('createLink', false, url);
                }
            }, 'Link invoegen'));
            toolbarEl.appendChild(makeToolbarButton('&#8226;', function () { document.execCommand('insertUnorderedList'); }, 'Opsomming'));
            toolbarEl.appendChild(makeToolbarButton('1.', function () { document.execCommand('insertOrderedList'); }, 'Genummerde lijst'));
            toolbarEl.appendChild(makeToolbarButton('&#10005;', function () { document.execCommand('removeFormat'); }, 'Opmaak wissen'));
        }

        document.body.appendChild(toolbarEl);
        positionToolbar(el);
    }

    function hideToolbar() {
        if (toolbarEl) {
            toolbarEl.remove();
            toolbarEl = null;
        }
    }

    function onOutsideClick(event) {
        if (!activeField) {
            return;
        }
        if (activeField.contains(event.target)) {
            return;
        }
        if (toolbarEl && toolbarEl.contains(event.target)) {
            return;
        }
        endActiveField();
    }

    function activateField(el, type) {
        endActiveField();
        el.setAttribute('contenteditable', 'true');
        el.classList.add('is-editing-field');
        el.focus();
        showToolbar(el, type);
        activeField = el;
        document.addEventListener('mousedown', onOutsideClick, true);
    }

    function endActiveField() {
        if (!activeField) {
            return;
        }
        var el = activeField;
        el.removeAttribute('contenteditable');
        el.classList.remove('is-editing-field');
        hideToolbar();
        document.removeEventListener('mousedown', onOutsideClick, true);
        activeField = null;

        var ref = el.dataset.edit;
        var newValue = el.innerHTML.trim();
        var original = originalValues.get(el);
        if (newValue !== original) {
            el.classList.add('is-dirty');
            applyFieldChange(ref, newValue);
        }
    }

    function setupTextField(el) {
        var type = el.dataset.editType === 'richtext' ? 'richtext' : 'text';
        originalValues.set(el, el.innerHTML.trim());

        el.addEventListener('click', function (event) {
            event.preventDefault();
            if (!el.classList.contains('is-editing-field')) {
                activateField(el, type);
            }
        });

        el.addEventListener('paste', function (event) {
            event.preventDefault();
            var text = (event.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });

        el.addEventListener('keydown', function (event) {
            if (type === 'text' && event.key === 'Enter') {
                event.preventDefault();
            }
        });
    }

    // --- Images ---

    function closeImageMenu() {
        var existing = document.getElementById('editor-image-menu');
        if (existing) {
            existing.remove();
        }
    }

    function triggerUpload(callback) {
        var input = document.createElement('input');
        input.type = 'file';
        input.accept = 'image/*';
        input.addEventListener('change', function () {
            var file = input.files[0];
            if (!file) {
                return;
            }
            var formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', csrfTokenValue());
            fetch('/admin/afbeeldingen/upload', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.ok) {
                        callback(data.filename);
                    } else {
                        showToast(data.error || 'Uploaden mislukt.', 'error');
                    }
                })
                .catch(function () {
                    showToast('Uploaden mislukt.', 'error');
                });
        });
        input.click();
    }

    function applyImageChange(el, ref, filename) {
        if (el.tagName === 'IMG') {
            el.src = '/uploads/' + filename;
        } else {
            el.style.backgroundImage = "url('/uploads/" + filename + "')";
        }
        el.classList.add('is-dirty');
        applyFieldChange(ref, filename);
    }

    function openImageMenu(el, ref) {
        closeImageMenu();
        var menu = document.createElement('div');
        menu.className = 'editor-image-menu show';
        menu.id = 'editor-image-menu';

        var panel = document.createElement('div');
        panel.className = 'editor-image-menu__panel';

        var replaceBtn = document.createElement('button');
        replaceBtn.type = 'button';
        replaceBtn.textContent = 'Vervang afbeelding';
        replaceBtn.addEventListener('click', function () {
            closeImageMenu();
            window.MediaPicker.open(function (filename) {
                applyImageChange(el, ref, filename);
            });
        });
        panel.appendChild(replaceBtn);

        var uploadBtn = document.createElement('button');
        uploadBtn.type = 'button';
        uploadBtn.textContent = 'Upload nieuwe afbeelding';
        uploadBtn.addEventListener('click', function () {
            closeImageMenu();
            triggerUpload(function (filename) {
                applyImageChange(el, ref, filename);
            });
        });
        panel.appendChild(uploadBtn);

        var altRef = el.dataset.editAlt;
        if (altRef) {
            var altBtn = document.createElement('button');
            altBtn.type = 'button';
            altBtn.textContent = 'Alt-tekst';
            altBtn.addEventListener('click', function () {
                closeImageMenu();
                var current = el.getAttribute('aria-label') || el.getAttribute('alt') || '';
                var value = prompt('Alt-tekst', current);
                if (value !== null) {
                    if (el.hasAttribute('aria-label')) {
                        el.setAttribute('aria-label', value);
                    }
                    if (el.hasAttribute('alt')) {
                        el.setAttribute('alt', value);
                    }
                    applyFieldChange(altRef, value);
                }
            });
            panel.appendChild(altBtn);
        }

        var cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.textContent = 'Annuleren';
        cancelBtn.addEventListener('click', closeImageMenu);
        panel.appendChild(cancelBtn);

        menu.appendChild(panel);
        menu.addEventListener('click', function (event) {
            if (event.target === menu) {
                closeImageMenu();
            }
        });
        document.body.appendChild(menu);
    }

    function setupImageField(el) {
        var ref = el.dataset.edit;
        el.addEventListener('click', function (event) {
            event.preventDefault();
            openImageMenu(el, ref);
        });
    }

    // --- Hero slides ---

    function setupHeroSlides(section) {
        var ref = section.dataset.edit;
        var slideEls = Array.prototype.slice.call(section.querySelectorAll('.hero__slide'));
        var slides = slideEls.map(function (el) {
            var match = /url\(['"]?\/uploads\/([^'")]+)['"]?\)/.exec(el.style.backgroundImage);
            return match ? match[1] : '';
        }).filter(Boolean);
        slideEls.forEach(function (el) { el.remove(); });

        var strip = document.createElement('div');
        strip.className = 'editor-hero-strip';
        section.appendChild(strip);

        var intervalWrap = document.createElement('div');
        intervalWrap.className = 'editor-hero-interval';
        var intervalLabel = document.createElement('label');
        intervalLabel.textContent = 'Wisseltijd (seconden)';
        var intervalInput = document.createElement('input');
        intervalInput.type = 'number';
        intervalInput.min = '2';
        intervalInput.max = '30';
        intervalInput.step = '1';
        var currentIntervalMs = parseInt(section.dataset.interval, 10);
        intervalInput.value = String(currentIntervalMs > 0 ? Math.round(currentIntervalMs / 1000) : 6);
        intervalInput.addEventListener('change', function () {
            var seconds = parseInt(intervalInput.value, 10);
            if (!seconds || seconds < 2) {
                seconds = 2;
            } else if (seconds > 30) {
                seconds = 30;
            }
            intervalInput.value = String(seconds);
            section.dataset.interval = String(seconds * 1000);
            applyFieldChange('setting:hero_interval', String(seconds));
        });
        intervalLabel.appendChild(intervalInput);
        intervalWrap.appendChild(intervalLabel);
        section.appendChild(intervalWrap);

        function syncSlides() {
            applyFieldChange(ref, slides.slice());
            renderSlides();
        }

        function renderSlides() {
            section.querySelectorAll('.hero__slide').forEach(function (el) { el.remove(); });
            strip.innerHTML = '';

            slides.forEach(function (filename, index) {
                var bg = document.createElement('div');
                bg.className = 'hero__slide' + (index === 0 ? ' is-active' : '');
                bg.style.backgroundImage = "url('/uploads/" + filename + "')";
                section.insertBefore(bg, section.firstChild);

                var thumb = document.createElement('div');
                thumb.className = 'editor-hero-thumb' + (index === 0 ? ' is-active' : '');
                thumb.style.backgroundImage = "url('/uploads/" + filename + "')";
                thumb.dataset.index = String(index);
                thumb.addEventListener('click', function () {
                    section.querySelectorAll('.hero__slide').forEach(function (el, i) {
                        el.classList.toggle('is-active', i === index);
                    });
                    strip.querySelectorAll('.editor-hero-thumb').forEach(function (t) {
                        t.classList.toggle('is-active', t === thumb);
                    });
                });

                var removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.innerHTML = '&times;';
                removeBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                    slides.splice(index, 1);
                    syncSlides();
                });
                thumb.appendChild(removeBtn);

                strip.appendChild(thumb);
            });

            var addBtn = document.createElement('button');
            addBtn.type = 'button';
            addBtn.className = 'editor-hero-add';
            addBtn.textContent = '+';
            addBtn.addEventListener('click', function () {
                window.MediaPicker.open(function (filename) {
                    slides.push(filename);
                    syncSlides();
                });
            });
            strip.appendChild(addBtn);

            if (typeof Sortable !== 'undefined') {
                Sortable.create(strip, {
                    animation: 150,
                    filter: '.editor-hero-add, button',
                    onEnd: function () {
                        var newOrder = [];
                        strip.querySelectorAll('.editor-hero-thumb').forEach(function (thumb) {
                            var idx = parseInt(thumb.dataset.index, 10);
                            newOrder.push(slides[idx]);
                        });
                        slides = newOrder;
                        syncSlides();
                    }
                });
            }
        }

        renderSlides();
    }

    // --- Repeatable items (Tand / Team) ---

    function initField(el) {
        var type = el.dataset.editType;
        if (type === 'slides') {
            setupHeroSlides(el);
        } else if (type === 'image') {
            setupImageField(el);
        } else {
            setupTextField(el);
        }
    }

    function setupRepeatableItem(item, kind) {
        var controls = document.createElement('div');
        controls.className = 'editor-item-controls';

        var dragHandle = document.createElement('button');
        dragHandle.type = 'button';
        dragHandle.className = 'editor-item-btn editor-drag-handle';
        dragHandle.title = 'Slepen om te herordenen';
        dragHandle.innerHTML = '&#9776;';
        controls.appendChild(dragHandle);

        var deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'editor-item-btn editor-item-btn--danger';
        deleteBtn.title = 'Verwijderen';
        deleteBtn.innerHTML = '&times;';
        deleteBtn.addEventListener('click', function (event) {
            event.stopPropagation();
            if (!confirm('Item verwijderen?')) {
                return;
            }
            markDelete(kind, item.dataset.id);
            item.remove();
        });
        controls.appendChild(deleteBtn);

        var anchor = item.querySelector('.tand-grid__cell--image') || item;
        anchor.appendChild(controls);

        item.querySelectorAll('[data-edit]').forEach(initField);
    }

    function seedCreateDefaults(newItem, kind, tmpId) {
        var data = state[kind].create[tmpId] = state[kind].create[tmpId] || {};
        newItem.querySelectorAll('[data-edit]').forEach(function (el) {
            var parsed = parseRef(el.dataset.edit);
            if (parsed.kind !== kind) {
                return;
            }
            data[parsed.field] = el.dataset.editType === 'image' ? '' : el.innerHTML.trim();
        });
    }

    function setupAddItemButton(btn) {
        var kind = btn.dataset.addItem;
        var templateId = btn.dataset.template;
        btn.addEventListener('click', function () {
            var template = document.getElementById(templateId);
            if (!template) {
                return;
            }
            tmpCounter += 1;
            var tmpId = 'new-' + tmpCounter;
            var html = template.innerHTML.split('__ID__').join(tmpId);
            var wrapper = document.createElement('div');
            wrapper.innerHTML = html.trim();
            var newItem = wrapper.firstElementChild;
            var list = document.querySelector('[data-edit-list="' + kind + '"]');
            if (list) {
                list.appendChild(newItem);
            } else {
                btn.parentNode.insertBefore(newItem, btn);
            }
            seedCreateDefaults(newItem, kind, tmpId);
            setupRepeatableItem(newItem, kind);
            updateChangeCount();
        });
    }

    function setupSortableList(grid) {
        if (typeof Sortable === 'undefined') {
            return;
        }
        var kind = grid.dataset.editList;
        Sortable.create(grid, {
            handle: '.editor-drag-handle',
            animation: 150,
            onEnd: function () {
                var order = Array.prototype.map.call(
                    grid.querySelectorAll('[data-edit-item]'),
                    function (el) { return el.dataset.id; }
                );
                state[kind].order = order;
                updateChangeCount();
            }
        });
    }

    // --- Save / cancel ---

    function buildPayload() {
        var payload = {};
        if (Object.keys(state.settings).length) {
            payload.settings = state.settings;
        }
        ['tandwerk', 'team'].forEach(function (kind) {
            var bucket = state[kind];
            var hasAny = Object.keys(bucket.create).length || Object.keys(bucket.update).length ||
                bucket.delete.length || bucket.order;
            if (!hasAny) {
                return;
            }
            payload[kind] = {
                create: Object.keys(bucket.create).map(function (tmp) {
                    var fields = Object.assign({}, bucket.create[tmp]);
                    fields.tmp = tmp;
                    return fields;
                }),
                update: bucket.update,
                delete: bucket.delete,
                order: bucket.order || undefined
            };
        });
        return payload;
    }

    function saveChanges() {
        var payload = buildPayload();
        fetch('/admin/bewerken/opslaan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfTokenValue()
            },
            body: JSON.stringify(payload)
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.ok) {
                    showToast('Opgeslagen.', 'success');
                    window.removeEventListener('beforeunload', beforeUnloadHandler);
                    setTimeout(function () { window.location.reload(); }, 400);
                } else {
                    showToast(data.error || 'Opslaan mislukt.', 'error');
                }
            })
            .catch(function () {
                showToast('Opslaan mislukt. Controleer je verbinding.', 'error');
            });
    }

    function cancelChanges() {
        if (!hasChanges() || confirm('Wijzigingen annuleren en de pagina herladen?')) {
            window.removeEventListener('beforeunload', beforeUnloadHandler);
            window.location.reload();
        }
    }

    function beforeUnloadHandler(event) {
        if (hasChanges()) {
            event.preventDefault();
            event.returnValue = '';
        }
    }

    // --- Init ---

    document.querySelectorAll('[data-edit]').forEach(function (el) {
        if (el.closest('template') || el.closest('[data-edit-item]')) {
            return;
        }
        initField(el);
    });

    document.querySelectorAll('[data-edit-item]').forEach(function (item) {
        if (item.closest('template')) {
            return;
        }
        var kind = parseRef(item.dataset.editItem).kind;
        setupRepeatableItem(item, kind);
    });

    document.querySelectorAll('[data-edit-list]').forEach(setupSortableList);
    document.querySelectorAll('[data-add-item]').forEach(setupAddItemButton);

    var saveBtn = document.getElementById('editor-save');
    var cancelBtn = document.getElementById('editor-cancel');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveChanges);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', cancelChanges);
    }

    window.addEventListener('beforeunload', beforeUnloadHandler);
})();
