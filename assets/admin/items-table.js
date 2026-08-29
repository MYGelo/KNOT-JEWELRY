document.addEventListener('DOMContentLoaded', () => {

    const table = document.querySelector('.knot-items__list');
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

    // Details live inside the item, so one lookup covers every field.
    const rowOf = el => el.closest('[data-item-row]');

    table.addEventListener('input', (e) => {
        if (!e.target.classList.contains('knot-items__input')) return;
        const row = rowOf(e.target);
        if (row) markDirty(row);
    });

    table.addEventListener('change', (e) => {
        if (!e.target.classList.contains('knot-items__input')) return;

        // The size list only exists for pieces that come in sizes.
        if (e.target.dataset.field === 'has_sizes') {
            const field = e.target.closest('.knot-items__stock')
                ?.querySelector('[data-sizes-field]');

            if (field) {
                field.hidden = !e.target.checked;

                if (!e.target.checked) {
                    field.querySelectorAll('[data-field="stock_sizes"]')
                        .forEach(box => { box.checked = false; });
                }
            }
        }

        // Sizes only mean anything while the item is in stock.
        if (e.target.dataset.field === 'in_stock') {
            const sizes = e.target.closest('.knot-items__stock')
                ?.querySelectorAll('[data-field="stock_sizes"]') || [];

            sizes.forEach(box => {
                box.disabled = !e.target.checked;
                if (!e.target.checked) box.checked = false;
            });
        }

        const row = rowOf(e.target);
        if (row) markDirty(row);
    });

    /* ---------------- EXPAND / COLLAPSE ---------------- */

    table.addEventListener('click', (e) => {
        const toggle = e.target.closest('[data-items-toggle]');
        if (!toggle) return;

        const row = toggle.closest('[data-item-row]');
        const details = row?.querySelector('[data-item-details]');
        if (!details) return;

        const open = details.hidden;
        details.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        toggle.querySelector('.dashicons')?.classList.toggle('dashicons-arrow-down', open);
        toggle.querySelector('.dashicons')?.classList.toggle('dashicons-arrow-right', !open);
    });

    /* ---------------- COLLECT + SAVE ---------------- */

    function collect(row) {
        const item = { id: Number(row.dataset.itemId), tax: {} };

        // Present even when nothing is ticked, so clearing every size saves.
        if (row.querySelector('[data-field="stock_sizes"]')) item.stock_sizes = [];

        row.querySelectorAll('.knot-items__input').forEach(input => {
            const field = input.dataset.field;

            if (field === 'tax') {
                item.tax[input.dataset.taxonomy] =
                    Array.from(input.selectedOptions).map(o => Number(o.value));
                return;
            }

            // One entry per checkbox, so collect them into a single array.
            if (field === 'stock_sizes') {
                item.stock_sizes = item.stock_sizes || [];
                if (input.checked) item.stock_sizes.push(input.value);
                return;
            }

            item[field] = input.type === 'checkbox' ? input.checked : input.value;
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
                // Show why, not just which — the server explains each rejection.
                const reasons = (data.errors || [])
                    .map(e => '#' + e.id + ': ' + e.message)
                    .join('; ');

                setStatus(
                    reasons || 'Не вдалося зберегти: ' + Array.from(failed).join(', '),
                    'error'
                );
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
