import './bootstrap';

import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';

// Register Alpine plugins
Alpine.plugin(collapse);

// Custom magic for formatting numbers
Alpine.magic('formatNumber', () => (value) => {
    if (value === null || value === undefined || value === '') return '';
    return new Intl.NumberFormat('id-ID').format(value);
});

// Parse formatted number back to raw number
Alpine.magic('parseNumber', () => (value) => {
    if (!value) return 0;
    return parseFloat(String(value).replace(/\./g, '').replace(',', '.')) || 0;
});

// Custom directive for money input with thousand separator
Alpine.directive('money', (el, { expression }, { evaluateLater, effect, cleanup }) => {
    const getValue = evaluateLater(expression);
    const setValue = evaluateLater(`${expression} = __value`);

    const formatNumber = (num) => {
        if (num === null || num === undefined || num === '') return '';
        return new Intl.NumberFormat('id-ID').format(num);
    };

    const parseNumber = (str) => {
        if (!str) return 0;
        return parseFloat(String(str).replace(/\./g, '').replace(',', '.')) || 0;
    };

    // Set initial value
    effect(() => {
        getValue(value => {
            el.value = formatNumber(value);
        });
    });

    // Handle input
    const handler = (e) => {
        const cursorPos = el.selectionStart;
        const oldLength = el.value.length;
        const rawValue = parseNumber(el.value);
        const formatted = formatNumber(rawValue);

        Alpine.evaluate(el, `${expression} = ${rawValue}`);
        el.value = formatted;

        // Adjust cursor position
        const newLength = formatted.length;
        const diff = newLength - oldLength;
        el.setSelectionRange(cursorPos + diff, cursorPos + diff);
    };

    el.addEventListener('input', handler);
    cleanup(() => el.removeEventListener('input', handler));
});

window.Alpine = Alpine;

Alpine.start();
