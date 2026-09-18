

import './bootstrap';
import Alpine from 'alpinejs';

// Alpine global state for sidebar
Alpine.store('sidebar', {
    open: false,
    collapsed: false,
    toggle() {
        this.open = !this.open;
    },
    toggleCollapse() {
        this.collapsed = !this.collapsed;
    }
});

window.Alpine = Alpine;
Alpine.start();

// Chart.js will be loaded when user runs: npm install chart.js
// Uncomment when installed:
// import Chart from 'chart.js/auto';
// window.Chart = Chart;
