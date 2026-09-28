(function () {
    'use strict';

    var active = null;

    function el(tag, className, text) {
        var node = document.createElement(tag);
        if (className) {
            node.className = className;
        }
        if (text !== undefined) {
            node.textContent = text;
        }
        return node;
    }

    function open(options) {
        return new Promise(function (resolve) {
            if (active) {
                active.close(null);
            }

            var previousFocus = document.activeElement;
            var overlay = el('div', 'tl-dialog');
            var box = el('div', 'tl-dialog__box' + (options.danger ? ' tl-dialog__box--danger' : ''));
            box.setAttribute('role', options.alert ? 'alertdialog' : 'dialog');
            box.setAttribute('aria-modal', 'true');

            var title = el('h2', 'tl-dialog__title', options.title);
            title.id = 'tl-dialog-title';
            box.setAttribute('aria-labelledby', title.id);
            box.appendChild(title);

            if (options.message) {
                var message = el('p', 'tl-dialog__message', options.message);
                box.appendChild(message);
            }
            if (options.list && options.list.length) {
                var list = el('ul', 'tl-dialog__list');
                options.list.forEach(function (item) { list.appendChild(el('li', '', item)); });
                box.appendChild(list);
            }
            if (options.after) {
                box.appendChild(el('p', 'tl-dialog__message', options.after));
            }

            var input = null;
            if (options.input) {
                input = el('input', 'tl-dialog__input');
                input.type = 'text';
                input.value = options.value || '';
                if (options.placeholder) {
                    input.placeholder = options.placeholder;
                }
                input.setAttribute('aria-labelledby', title.id);
                box.appendChild(input);
            }

            var actions = el('div', 'tl-dialog__actions');
            var cancelBtn = null;
            if (!options.alert) {
                cancelBtn = el('button', 'tl-dialog__btn tl-dialog__btn--secondary', options.cancelLabel || 'Annuleren');
                cancelBtn.type = 'button';
                actions.appendChild(cancelBtn);
            }
            var okBtn = el('button', 'tl-dialog__btn' + (options.danger ? ' tl-dialog__btn--danger' : ''), options.okLabel || 'OK');
            okBtn.type = 'button';
            actions.appendChild(okBtn);
            box.appendChild(actions);

            overlay.appendChild(box);
            document.body.appendChild(overlay);
            document.body.classList.add('tl-dialog-open');

            function close(result) {
                if (active !== api) {
                    return;
                }
                active = null;
                document.removeEventListener('keydown', onKey, true);
                overlay.remove();
                document.body.classList.remove('tl-dialog-open');
                if (previousFocus && previousFocus.focus) {
                    previousFocus.focus({ preventScroll: true });
                }
                resolve(result);
            }

            function accept() {
                close(input ? input.value : true);
            }

            function dismiss() {
                close(input ? null : false);
            }

            function onKey(event) {
                if (event.key === 'Escape') {
                    event.preventDefault();
                    event.stopPropagation();
                    dismiss();
                } else if (event.key === 'Enter' && event.target !== cancelBtn) {
                    event.preventDefault();
                    event.stopPropagation();
                    accept();
                } else if (event.key === 'Tab') {
                    var focusable = [input, cancelBtn, okBtn].filter(Boolean);
                    var first = focusable[0];
                    var last = focusable[focusable.length - 1];
                    if (event.shiftKey && document.activeElement === first) {
                        event.preventDefault();
                        last.focus();
                    } else if (!event.shiftKey && document.activeElement === last) {
                        event.preventDefault();
                        first.focus();
                    }
                }
            }

            var api = { close: close };
            active = api;
            document.addEventListener('keydown', onKey, true);
            overlay.addEventListener('mousedown', function (event) {
                if (event.target === overlay) {
                    dismiss();
                }
            });
            okBtn.addEventListener('click', accept);
            if (cancelBtn) {
                cancelBtn.addEventListener('click', dismiss);
            }

            if (input) {
                input.focus();
                input.select();
            } else {
                (options.danger && cancelBtn ? cancelBtn : okBtn).focus();
            }
        });
    }

    function confirmDialog(options) {
        return open({
            title: options.title || 'Weet je het zeker?',
            message: options.message,
            list: options.list,
            after: options.after,
            okLabel: options.okLabel || 'Bevestigen',
            cancelLabel: options.cancelLabel,
            danger: options.danger
        });
    }

    function promptDialog(options) {
        return open({
            title: options.title,
            message: options.message,
            input: true,
            value: options.value,
            placeholder: options.placeholder,
            okLabel: options.okLabel || 'OK'
        });
    }

    function alertDialog(options) {
        return open({
            title: options.title || 'Let op',
            message: options.message,
            alert: true,
            danger: true,
            okLabel: options.okLabel || 'Begrepen'
        });
    }

    window.TandlabDialog = { confirm: confirmDialog, prompt: promptDialog, alert: alertDialog };

    // Declaratief: <button data-confirm="..."> of <form data-confirm="...">
    var confirmed = new WeakSet();

    function askAndProceed(event, target, submitter) {
        if (confirmed.has(target)) {
            confirmed.delete(target);
            return;
        }
        event.preventDefault();
        confirmDialog({
            title: target.getAttribute('data-confirm-title') || 'Weet je het zeker?',
            message: target.getAttribute('data-confirm'),
            okLabel: target.getAttribute('data-confirm-ok') || 'Bevestigen',
            danger: target.hasAttribute('data-confirm-danger')
        }).then(function (ok) {
            if (!ok) {
                return;
            }
            var form = target.form || target;
            confirmed.add(target);
            if (submitter && form.requestSubmit) {
                form.requestSubmit(submitter);
            } else if (form.requestSubmit) {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    }

    document.addEventListener('click', function (event) {
        var btn = event.target.closest ? event.target.closest('button[data-confirm], input[data-confirm]') : null;
        if (btn && btn.form) {
            askAndProceed(event, btn, btn);
        }
    });

    document.addEventListener('submit', function (event) {
        if (event.target.hasAttribute && event.target.hasAttribute('data-confirm')) {
            askAndProceed(event, event.target, event.submitter);
        }
    });

    // Vervangt de native validatiebubbel door een eigen melding.
    document.addEventListener('invalid', function (event) {
        var field = event.target;
        event.preventDefault();
        if (active) {
            return;
        }
        var label = field.id ? document.querySelector('label[for="' + field.id + '"]') : null;
        var name = label ? label.textContent.replace(/[:*]\s*$/, '').trim() : '';
        var message = name ? 'Vul het veld "' + name + '" in.' : 'Vul alle verplichte velden in.';
        if (field.type === 'file') {
            message = 'Kies eerst een bestand.';
        }
        alertDialog({ title: 'Formulier onvolledig', message: message }).then(function () {
            field.focus();
        });
    }, true);
})();
