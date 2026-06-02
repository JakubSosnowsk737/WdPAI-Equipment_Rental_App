// Przelaczanie i zapamietywanie motywu (jasny / ciemny).
// Poczatkowy motyw ustawia inline skrypt w <head> (anty-FOUC).
(function () {
    'use strict';

    var toggle = document.getElementById('themeToggle');
    if (!toggle) return;

    function current() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    toggle.addEventListener('click', function () {
        var next = current() === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try {
            localStorage.setItem('theme', next);
        } catch (e) {}
    });

    // Reaguj na zmiane preferencji systemowych, gdy uzytkownik nie wybral motywu recznie.
    if (window.matchMedia) {
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function (e) {
            try {
                if (!localStorage.getItem('theme')) {
                    document.documentElement.setAttribute('data-theme', e.matches ? 'dark' : 'light');
                }
            } catch (err) {}
        });
    }
})();
