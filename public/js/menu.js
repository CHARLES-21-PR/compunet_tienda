// Menu script extracted from navigation.blade.php
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    ready(function () {
        var toggle = document.querySelector('.menu_hamburguer');
        var nav = document.querySelector('.nav-1');
        if (!toggle || !nav) return;

        // icon swap: keep original src
        var img = toggle.querySelector('.menu_img');
        var hamburgerSrc = img ? img.getAttribute('src') : null;
        // optional cross icon -- fallback to rotate if not present
        var crossSrc = '/assets/close.svg'; // you may add this file to public/assets

        function openMenu() {
            nav.classList.add('open');
            toggle.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
            document.documentElement.style.overflow = 'hidden';
            document.body.style.overflow = 'hidden';
            if (img && crossSrc) {
                // try to swap to cross if available
                fetch(crossSrc, { method: 'HEAD' }).then(function (res) {
                    if (res.ok) img.setAttribute('src', crossSrc);
                }).catch(function () { img.style.transform = 'rotate(90deg)'; });
            }
        }

        function closeMenu() {
            nav.classList.remove('open');
            toggle.classList.remove('open');
            toggle.setAttribute('aria-expanded', 'false');
            document.documentElement.style.overflow = '';
            document.body.style.overflow = '';
            if (img) {
                img.setAttribute('src', hamburgerSrc);
                img.style.transform = '';
            }
            // collapse any open submenus
            nav.querySelectorAll('.enlace.enlace-active').forEach(function (el) {
                collapseSubmenu(el);
                el.classList.remove('enlace-active');
            });
        }

        toggle.addEventListener('click', function (e) {
            e.stopPropagation();
            if (nav.classList.contains('open')) closeMenu(); else openMenu();
        });

        // Helpers to animate submenu height (slide)
        function expandSubmenu(enlace) {
            var submenu = enlace.querySelector('.menu_nesting');
            if (!submenu) return;
            submenu.style.display = 'block';
            var h = submenu.scrollHeight;
            submenu.style.maxHeight = '0px';
            // force reflow
            // eslint-disable-next-line no-unused-expressions
            submenu.offsetHeight;
            submenu.style.transition = 'max-height 300ms ease';
            submenu.style.maxHeight = h + 'px';
        }

        function collapseSubmenu(enlace) {
            var submenu = enlace.querySelector('.menu_nesting');
            if (!submenu) return;
            submenu.style.transition = 'max-height 220ms ease';
            submenu.style.maxHeight = '0px';
            // after transition remove display block to keep layout clean
            setTimeout(function () {
                if (submenu.style.maxHeight === '0px') submenu.style.display = '';
            }, 260);
        }

        // Click on header toggles submenu in mobile
        nav.querySelectorAll('.enlace.enlace-show > .menu_link').forEach(function (head) {
            head.addEventListener('click', function (e) {
                if (window.innerWidth <= 1164 || nav.classList.contains('open')) {
                    e.preventDefault();
                    e.stopPropagation();
                    var parent = head.closest('.enlace');
                    if (!parent) return;
                    var isActive = parent.classList.contains('enlace-active');
                    // accordion behavior: close others
                    nav.querySelectorAll('.enlace.enlace-show.enlace-active').forEach(function (el) {
                        if (el !== parent) {
                            el.classList.remove('enlace-active');
                            collapseSubmenu(el);
                        }
                    });
                    if (isActive) {
                        parent.classList.remove('enlace-active');
                        collapseSubmenu(parent);
                    } else {
                        parent.classList.add('enlace-active');
                        expandSubmenu(parent);
                    }
                }
            });
        });

        // Clicking links closes menu (except header links)
        nav.querySelectorAll('a').forEach(function (a) {
            a.addEventListener('click', function (e) {
                var parentEnlace = a.closest('.enlace.enlace-show');
                var isHeaderLink = parentEnlace && parentEnlace.querySelector('.menu_link') === a;
                if (isHeaderLink) return;
                // navigation links should close the menu on mobile
                if (window.innerWidth <= 1164) closeMenu();
            });
        });

        // Close on outside click or ESC
        document.addEventListener('click', function (e) {
            if (!nav.contains(e.target) && !toggle.contains(e.target) && nav.classList.contains('open')) closeMenu();
        });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && nav.classList.contains('open')) closeMenu(); });

        // Responsive: on resize, collapse submenus if moving to desktop
        window.addEventListener('resize', function () {
            if (window.innerWidth > 1164) {
                // remove inline styles we used for mobile animation
                nav.querySelectorAll('.menu_nesting').forEach(function (submenu) {
                    submenu.style.maxHeight = '';
                    submenu.style.transition = '';
                    submenu.style.display = '';
                });
                nav.querySelectorAll('.enlace.enlace-active').forEach(function (el) { el.classList.remove('enlace-active'); });
            }
        });
    });
})();
