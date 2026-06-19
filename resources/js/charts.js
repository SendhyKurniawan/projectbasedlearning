// Inisialisasi Chart.js bersama: menerapkan token design-system & meng-expose window.PJBLChart.
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const style = getComputedStyle(document.documentElement);
const get = (v) => style.getPropertyValue(v).trim();

// tokens.css menyimpan channel warna dipisah spasi (mis. "25 27 35") — ubah jadi dipisah koma.
const rgba = (channel, alpha = 1) => {
    const channels = get(channel).replace(/\s+/g, ', ');
    return `rgba(${channels}, ${alpha})`;
};

const primary    = '#004ac6';
const secondary  = '#006c49';
const tertiary   = '#3e3fcc';

const onSurface        = rgba('--on-surface-rgb');
const onSurfaceVariant = rgba('--on-surface-variant-rgb', 0.7);
const outlineVariant   = rgba('--outline-variant-rgb', 0.3);
const surfaceContainer = rgba('--surface-container-rgb');

Chart.defaults.color           = onSurfaceVariant;
Chart.defaults.borderColor     = outlineVariant;
Chart.defaults.backgroundColor = surfaceContainer;
Chart.defaults.font.family     = "'Inter', sans-serif";
Chart.defaults.font.size       = 11;

Chart.defaults.plugins.colors = { enabled: false };

// Expose palet & konstruktor Chart agar dipakai inline-script tiap halaman dashboard.
window.PJBLChartColors = { primary, secondary, tertiary };

window.PJBLChart = Chart;
