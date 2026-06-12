// Kalendarz wyboru zakresu dat wypozyczenia (vanilla JS, bez bibliotek).
// Progresywne ulepszanie: jesli JS dziala, ukrywa natywne pola <input type="date">
// i pisze do nich wybrane wartosci; bez JS dzialaja natywne pola.
(function () {
    'use strict';

    var mount = document.getElementById('rental-calendar');
    if (!mount) return;

    var startInput = document.getElementById('start_date');
    var endInput   = document.getElementById('end_date');
    var native     = document.getElementById('native-dates');
    var form       = document.getElementById('rental-form');
    var qty        = document.getElementById('quantity');
    if (!startInput || !endInput) return;

    var rate = parseFloat(mount.getAttribute('data-rate')) || 0;

    // Natywne pola przestaja byc wymagane (kalendarz waliduje sam) i znikaja.
    startInput.removeAttribute('required');
    endInput.removeAttribute('required');
    if (native) native.style.display = 'none';

    var MONTHS = ['Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec',
                  'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
    var DOW = ['Pn', 'Wt', 'Śr', 'Cz', 'Pt', 'So', 'Nd'];

    var today = new Date(); today.setHours(0, 0, 0, 0);
    var view  = new Date(today.getFullYear(), today.getMonth(), 1);
    var startDate = null, endDate = null;

    function pad(n)  { return n < 10 ? '0' + n : '' + n; }
    function iso(d)  { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); }
    function fmt(d)  { return pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear(); }
    function same(a, b) { return a && b && a.getTime() === b.getTime(); }
    function days(a, b) { return Math.round((b - a) / 86400000) + 1; }
    function el(tag, cls) { var e = document.createElement(tag); if (cls) e.className = cls; return e; }

    function navBtn(txt) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'cal-nav'; b.textContent = txt;
        return b;
    }

    function pick(d) {
        if (!startDate || (startDate && endDate)) { startDate = d; endDate = null; }
        else if (d < startDate)                   { startDate = d; }
        else                                      { endDate = d; }
        startInput.value = startDate ? iso(startDate) : '';
        endInput.value   = endDate   ? iso(endDate)   : '';
        build();
    }

    function build() {
        mount.innerHTML = '';

        var firstOfView = new Date(view.getFullYear(), view.getMonth(), 1);

        // naglowek z nawigacja
        var head = el('div', 'cal-head');
        var prev = navBtn('‹');
        var next = navBtn('›');
        if (firstOfView <= new Date(today.getFullYear(), today.getMonth(), 1)) prev.disabled = true;
        prev.onclick = function () { view = new Date(view.getFullYear(), view.getMonth() - 1, 1); build(); };
        next.onclick = function () { view = new Date(view.getFullYear(), view.getMonth() + 1, 1); build(); };
        var title = el('span', 'cal-title');
        title.textContent = MONTHS[view.getMonth()] + ' ' + view.getFullYear();
        head.appendChild(prev); head.appendChild(title); head.appendChild(next);
        mount.appendChild(head);

        // siatka dni
        var grid = el('div', 'cal-grid');
        DOW.forEach(function (d) { var c = el('span', 'cal-dow'); c.textContent = d; grid.appendChild(c); });

        var offset = (firstOfView.getDay() + 6) % 7; // poniedzialek = 0
        for (var i = 0; i < offset; i++) grid.appendChild(el('span', 'cal-empty'));

        var total = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
        for (var day = 1; day <= total; day++) {
            var d = new Date(view.getFullYear(), view.getMonth(), day);
            var cell = document.createElement('button');
            cell.type = 'button';
            cell.className = 'cal-day';
            cell.textContent = day;
            if (same(d, today)) cell.classList.add('is-today');
            if (d < today) { cell.classList.add('is-disabled'); cell.disabled = true; }
            if (same(d, startDate) || same(d, endDate)) cell.classList.add('is-selected');
            if (startDate && endDate && d > startDate && d < endDate) cell.classList.add('in-range');
            (function (dd) { cell.onclick = function () { pick(dd); }; })(d);
            grid.appendChild(cell);
        }
        mount.appendChild(grid);

        // podsumowanie
        var sum = el('div', 'cal-summary');
        if (startDate && endDate) {
            mount.classList.remove('cal-error');
            var n = days(startDate, endDate);
            var q = Math.max(1, parseInt(qty && qty.value, 10) || 1);
            var cost = rate * q * n;
            sum.innerHTML = '<span class="cal-range">' + fmt(startDate) + ' → ' + fmt(endDate) + '</span>' +
                '<span class="cal-cost">' + n + ' dni · ok. ' + cost.toFixed(2) + ' zł</span>';
        } else if (startDate) {
            sum.innerHTML = '<span class="cal-range">Początek: ' + fmt(startDate) + '</span>' +
                '<span class="cal-hint">kliknij datę końca</span>';
        } else {
            sum.innerHTML = '<span class="cal-hint">Wybierz datę początku, a następnie końca</span>';
        }
        mount.appendChild(sum);
    }

    if (qty) qty.addEventListener('input', build);

    if (form) {
        form.addEventListener('submit', function (e) {
            if (!startInput.value || !endInput.value) {
                e.preventDefault();
                mount.classList.add('cal-error');
            }
        });
    }

    build();
})();
