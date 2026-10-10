/* QuizUp Campus - shared behaviour: theme switch and mobile menu */
(function () {
    'use strict';

    var root = document.documentElement;
    var themeBtn = document.getElementById('themeToggle');

    function currentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function paintThemeButton() {
        if (!themeBtn) { return; }
        var isDark = currentTheme() === 'dark';
        themeBtn.textContent = isDark ? 'Light' : 'Dark';
        themeBtn.setAttribute('aria-pressed', String(isDark));
    }

    function setTheme(name) {
        root.setAttribute('data-theme', name);
        try { localStorage.setItem('qc-theme', name); } catch (err) { /* private mode */ }
        paintThemeButton();
    }

    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
        });
        paintThemeButton();
    }

    var navToggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (navToggle && nav) {
        navToggle.addEventListener('click', function () {
            var open = nav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', String(open));
        });
    }
})();

/* Small helpers other scripts can use */
window.QC = {
    csrf: function () {
        var m = document.querySelector('meta[name="csrf"]');
        return m ? m.getAttribute('content') : '';
    },
    getJSON: function (url) {
        return fetch(url, { credentials: 'same-origin' }).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) { throw new Error(data.error || 'Request failed'); }
                return data;
            });
        });
    },
    postJSON: function (url, payload) {
        return fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.QC.csrf() },
            body: JSON.stringify(payload)
        }).then(function (r) {
            return r.json().then(function (data) {
                if (!r.ok) { throw new Error(data.error || 'Request failed'); }
                return data;
            });
        });
    }
};
