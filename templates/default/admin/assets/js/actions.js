/* ============================================================================
   BLOGGY ACTION DECK — JS для кнопок действий админ-панели.

   1. Чип-подсказка: плавающая pill-плашка с цветной точкой действия.
      Позиционируется fixed в вьюпорте, поэтому не обрезается
      table-responsive/overflow-контейнерами (в отличие от нативного title
      и Bootstrap-тултипов).
   2. Ink-ripple: цветная рябь от точки касания/клика по кнопке.
   ========================================================================== */
(function () {
    'use strict';

    /* Иконка из спрайта bs.svg в стиле Action Deck — доступна всем
       контроллерным JS-файлам (грузится раньше них в layout.php) */
    window.bloggyActIcon = function (name) {
        var base = window.BASE_URL || '';
        return '<svg class="icon icon-' + name + ' act-ic" width="16" height="16" style="fill: currentColor" aria-hidden="true">'
            + '<use href="' + base + '/templates/default/admin/icons/bs.svg#' + name + '"></use></svg>';
    };

    var tipEl = null;
    var currentEl = null;
    var showTimer = null;
    var LOCALE_RU = (document.documentElement.lang || '').toLowerCase().indexOf('ru') === 0;

    /* --- Чип-подсказка --------------------------------------------------- */

    function ensureTip() {
        if (!tipEl) {
            tipEl = document.createElement('div');
            tipEl.className = 'act-tip';
            tipEl.setAttribute('role', 'tooltip');
            document.body.appendChild(tipEl);
        }
        return tipEl;
    }

    function hideTip() {
        if (showTimer) {
            clearTimeout(showTimer);
            showTimer = null;
        }
        currentEl = null;
        if (tipEl) {
            tipEl.classList.remove('is-on');
        }
    }

    function showTip(el) {
        var tip = ensureTip();
        var text = el.getAttribute('data-tip');
        if (!text) {
            return;
        }

        tip.textContent = text;

        // Цвет действия — заимствуем у кнопки, чтобы точка светилась в тон
        var actColor = '';
        try {
            actColor = getComputedStyle(el).getPropertyValue('--act-c').trim();
        } catch (e) {}
        tip.style.setProperty('--act-c', actColor || '#60a5fa');

        // Измеряем и позиционируем
        tip.classList.remove('is-on', 'is-above');
        var probe = tip.style.left; // принудительный reflow ниже
        tip.style.left = '0px';
        tip.style.top = '0px';

        var r = el.getBoundingClientRect();
        var tw = tip.offsetWidth;
        var th = tip.offsetHeight;
        var margin = 8;

        var left = r.left + r.width / 2;
        left = Math.min(Math.max(left, tw / 2 + margin), window.innerWidth - tw / 2 - margin);

        var top = r.bottom + 9;
        if (top + th > window.innerHeight - margin) {
            top = r.top - 9 - th;
            tip.classList.add('is-above');
        }
        top = Math.max(top, margin);

        tip.style.left = Math.round(left) + 'px';
        tip.style.top = Math.round(top) + 'px';

        // reflow, чтобы переход сработал от нового положения
        void probe;
        requestAnimationFrame(function () {
            if (currentEl === el) {
                tip.classList.add('is-on');
            }
        });
    }

    function handleOver(e) {
        var el = e.target && e.target.closest ? e.target.closest('[data-tip]') : null;
        if (!el || el === currentEl) {
            return;
        }
        hideTip();
        currentEl = el;
        if (showTimer) {
            clearTimeout(showTimer);
        }
        showTimer = setTimeout(function () {
            showTimer = null;
            if (currentEl === el && document.contains(el)) {
                showTip(el);
            }
        }, 90);
    }

    function handleOut(e) {
        if (!currentEl) {
            return;
        }
        var related = e.relatedTarget;
        if (related && currentEl.contains(related)) {
            return;
        }
        hideTip();
    }

    document.addEventListener('pointerover', handleOver, true);
    document.addEventListener('pointerout', handleOut, true);
    document.addEventListener('pointerdown', function (e) {
        // Пошёл клик — подсказка больше не нужна
        var el = e.target && e.target.closest ? e.target.closest('[data-tip]') : null;
        if (el && currentEl === el) {
            hideTip();
        }
    }, true);
    window.addEventListener('scroll', hideTip, true);
    window.addEventListener('resize', hideTip);
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hideTip();
        }
    });

    /* --- Ink-ripple -------------------------------------------------------- */

    document.addEventListener('pointerdown', function (e) {
        var btn = e.target && e.target.closest ? e.target.closest('.act') : null;
        if (!btn || btn.disabled || btn.classList.contains('is-off')) {
            return;
        }

        var rect = btn.getBoundingClientRect();
        var x = (e.clientX !== undefined ? e.clientX : rect.left + rect.width / 2) - rect.left;
        var y = (e.clientY !== undefined ? e.clientY : rect.top + rect.height / 2) - rect.top;

        var ripple = document.createElement('span');
        ripple.className = 'act-ripple';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.addEventListener('animationend', function () {
            if (ripple.parentNode) {
                ripple.parentNode.removeChild(ripple);
            }
        });
        btn.appendChild(ripple);
    });
})();
