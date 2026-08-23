document.addEventListener('DOMContentLoaded', () => {

    const table = document.querySelector('.knot-items__table');
    if (!table || typeof knotItems === 'undefined') return;

    const saveButtons = document.querySelectorAll('[data-items-save]');
    const statusEl = document.querySelector('[data-items-status]');
    const dirty = new Set();

    /* ---------------- DIRTY TRACKING ---------------- */

    function markDirty(row) {
        row.classList.add('is-dirty');
        dirty.add(row);
        saveButtons.forEach(b => { b.disabled = false; });
        setStatus('Є незбережені зміни');
    }

    function setStatus(text, kind) {
        if (!statusEl) return;
        statusEl.textContent = text || '';
        statusEl.className = 'knot-items__status' + (kind ? ' is-' + kind : '');
    }

    function rowOf(el) {
        // Detail fields live in the sibling row, so fall back to the previous row.
        const row = el.closest('[data-item-row]');
        if (row) return row;

        const details = el.closest('[data-item-details]');
        return details ? details.previousElementSibling : null;
    }

    table.addEventListener('input', (e) => {
        if (!e.target.classList.contains('knot-items__input')) return;
        const row = rowOf(e.target);
        if (row) markDirty(row);
    });

    table.addEventListener('change', (e) => {
        if (!e.target.classList.contains('knot-items__input')) return;

        // The optional "уточнення" only applies while the item is in stock.
        if (e.target.dataset.field === 'in_stock') {
            const note = e.target.closest('.knot-items__stock')
                ?.querySelector('[data-field="in_stock_note"]');

            if (note) {
                note.disabled = !e.target.checked;
                if (!e.target.checked) note.value = '';
            }
        }

        const row = rowOf(e.target);
        if (row) markDirty(row);
    });

    /* ---------------- EXPAND / COLLAPSE ---------------- */

    table.addEventListener('click', (e) => {
        const toggle = e.target.closest('[data-items-toggle]');
        if (!toggle) return;

        const row = toggle.closest('[data-item-row]');
        const details = row?.nextElementSibling;
        if (!details || !details.hasAttribute('data-item-details')) return;

        const open = details.hidden;
        details.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.dashicons')?.classList.toggle('dashicons-arrow-down', open);
        toggle.querySelector('.dashicons')?.classList.toggle('dashicons-arrow-right', !open);
    });

    /* ---------------- COLLECT + SAVE ---------------- */

    function collect(row) {
        const details = row.nextElementSibling?.hasAttribute('data-item-details')
            ? row.nextElementSibling
            : null;

        const item = { id: Number(row.dataset.itemId), tax: {} };
        const scopes = details ? [row, details] : [row];

        scopes.forEach(scope => {
            scope.querySelectorAll('.knot-items__input').forEach(input => {
                const field = input.dataset.field;

                if (field === 'tax') {
                    item.tax[input.dataset.taxonomy] =
                        Array.from(input.selectedOptions).map(o => Number(o.value));
                    return;
                }

                item[field] = input.type === 'checkbox' ? input.checked : input.value;
            });
        });

        return item;
    }

    async function save() {
        if (!dirty.size) return;

        const rows = Array.from(dirty);
        const items = rows.map(collect);

        saveButtons.forEach(b => { b.disabled = true; });
        setStatus('Зберігаємо…');

        try {
            const res = await fetch(knotItems.restUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': knotItems.nonce
                },
                body: JSON.stringify({ items })
            });

            if (!res.ok) throw new Error('HTTP ' + res.status);

            const data = await res.json();
            const failed = new Set(data.failed || []);

            rows.forEach(row => {
                const id = Number(row.dataset.itemId);
                if (failed.has(id)) return;

                row.classList.remove('is-dirty');
                row.classList.add('is-saved');
                setTimeout(() => row.classList.remove('is-saved'), 1500);
                dirty.delete(row);
            });

            if (failed.size) {
                setStatus('Не вдалося зберегти: ' + Array.from(failed).join(', '), 'error');
                saveButtons.forEach(b => { b.disabled = false; });
            } else {
                setStatus('Збережено', 'ok');
            }

        } catch (err) {
            console.error(err);
            setStatus('Помилка збереження. Спробуйте ще раз.', 'error');
            saveButtons.forEach(b => { b.disabled = false; });
        }
    }

    saveButtons.forEach(btn => btn.addEventListener('click', save));

    /* ---------------- GUARD ---------------- */

    window.addEventListener('beforeunload', (e) => {
        if (!dirty.size) return;
        e.preventDefault();
        e.returnValue = '';
    });

});
