(function () {
    var modal = null;
    var onSelectCallback = null;

    function csrfTokenValue() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.content : '';
    }

    function buildModal() {
        var el = document.createElement('div');
        el.className = 'editor-modal';
        el.id = 'media-picker-modal';
        el.innerHTML =
            '<div class="editor-modal__content">' +
                '<h3>Kies een afbeelding</h3>' +
                '<label class="media-picker-upload">' +
                    '<input type="file" accept="image/*" id="media-picker-upload" style="display:none">' +
                    'Nieuwe afbeelding uploaden' +
                '</label>' +
                '<p><span id="media-picker-status"></span></p>' +
                '<div class="media-picker-grid" id="media-picker-grid"></div>' +
                '<div style="text-align:right;margin-top:16px">' +
                    '<button type="button" class="editor-btn editor-btn--secondary" id="media-picker-cancel">Annuleren</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(el);

        el.addEventListener('click', function (event) {
            if (event.target === el) {
                close();
            }
        });
        el.querySelector('#media-picker-cancel').addEventListener('click', close);
        el.querySelector('#media-picker-upload').addEventListener('change', function (event) {
            var file = event.target.files[0];
            if (!file) {
                return;
            }
            var status = el.querySelector('#media-picker-status');
            status.textContent = 'Uploaden...';
            var formData = new FormData();
            formData.append('file', file);
            formData.append('csrf_token', csrfTokenValue());
            fetch('/admin/afbeeldingen/upload', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (data.ok) {
                        status.textContent = '';
                        select(data.filename, data.display_name);
                    } else {
                        status.textContent = data.error || 'Uploaden mislukt.';
                    }
                })
                .catch(function () {
                    status.textContent = 'Uploaden mislukt.';
                })
                .finally(function () {
                    event.target.value = '';
                });
        });

        return el;
    }

    function renderGrid(images) {
        var grid = modal.querySelector('#media-picker-grid');
        grid.innerHTML = '';
        images.forEach(function (image) {
            var item = document.createElement('div');
            item.className = 'media-picker-item';
            item.title = image.display_name;
            var img = document.createElement('img');
            img.src = image.url;
            img.alt = image.display_name;
            item.appendChild(img);
            item.addEventListener('click', function () {
                select(image.filename, image.display_name);
            });
            grid.appendChild(item);
        });
        if (!images.length) {
            grid.innerHTML = '<p>Nog geen afbeeldingen in de mediabibliotheek.</p>';
        }
    }

    function select(filename, displayName) {
        var callback = onSelectCallback;
        close();
        if (callback) {
            callback(filename, displayName);
        }
    }

    function close() {
        if (modal) {
            modal.classList.remove('show');
        }
    }

    function loadImages() {
        return fetch('/admin/afbeeldingen.json', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (response) { return response.json(); })
            .then(function (data) { return data.images || []; });
    }

    window.MediaPicker = {
        open: function (onSelect) {
            onSelectCallback = onSelect;
            if (!modal) {
                modal = buildModal();
            }
            modal.classList.add('show');
            loadImages().then(renderGrid);
        },
        close: close
    };
})();
