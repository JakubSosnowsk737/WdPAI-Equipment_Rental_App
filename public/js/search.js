// Dynamiczne wyszukiwanie sprzetu przez FETCH API.
(function () {
    'use strict';

    const input  = document.getElementById('eq-search');
    const select = document.getElementById('eq-category');
    const grid   = document.getElementById('eq-grid');
    if (!input || !grid) return;

    let timer = null;

    function render(items) {
        if (!items.length) {
            grid.innerHTML = '<p>Brak wyników.</p>';
            return;
        }
        grid.innerHTML = items.map(eq => `
            <article class="equipment-card">
                <h3>${escapeHtml(eq.name)}</h3>
                <p class="cat">${escapeHtml(eq.category_name || '')}</p>
                <p>${escapeHtml(eq.description || '')}</p>
                <p class="rate">${Number(eq.daily_rate).toFixed(2)} zł / dzień</p>
                <p class="stock">Dostępne: ${eq.available_quantity} / ${eq.total_quantity}</p>
                <a href="/equipment/${eq.id}" class="btn-sm">Szczegóły</a>
            </article>
        `).join('');
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[c]));
    }

    function search() {
        const params = new URLSearchParams();
        if (input.value) params.set('q', input.value);
        if (select && select.value) params.set('category_id', select.value);

        fetch('/api/equipment?' + params.toString(), {
            headers: { 'Accept': 'application/json' }
        })
            .then(r => r.ok ? r.json() : Promise.reject(r.status))
            .then(data => render(data.items || []))
            .catch(err => {
                grid.innerHTML = '<p class="error">Błąd pobierania danych.</p>';
                console.error(err);
            });
    }

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(search, 250);
    });
    if (select) select.addEventListener('change', search);
})();
