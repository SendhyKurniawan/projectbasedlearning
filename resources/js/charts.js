/**
 * charts.js — PBL Workspace shared Chart.js bootstrap
 *
 * Registers all Chart.js components and applies the Scholar Tech
 * Material 3 design-system tokens as chart defaults.
 * Exposes `window.PJBLChart` so per-dashboard @push('scripts') blocks
 * can instantiate charts without additional imports.
 */
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// ── Read CSS custom properties from the root element ──────────────────────────
const style = getComputedStyle(document.documentElement);
const get = (v) => style.getPropertyValue(v).trim();

// Build rgba helpers from the RGB channel variables already in tokens.css
// tokens.css stores space-separated channels (e.g. "25 27 35") — convert to comma-separated
const rgba = (channel, alpha = 1) => {
    const channels = get(channel).replace(/\s+/g, ', ');
    return `rgba(${channels}, ${alpha})`;
};

// Brand colours (static, not channel-based)
const primary    = '#004ac6';
const secondary  = '#006c49';
const tertiary   = '#3e3fcc';

// Surface / text tokens (dark-mode aware via CSS variables)
const onSurface        = rgba('--on-surface-rgb');
const onSurfaceVariant = rgba('--on-surface-variant-rgb', 0.7);
const outlineVariant   = rgba('--outline-variant-rgb', 0.3);
const surfaceContainer = rgba('--surface-container-rgb');

// ── Apply global Chart.js defaults ────────────────────────────────────────────
Chart.defaults.color           = onSurfaceVariant;
Chart.defaults.borderColor     = outlineVariant;
Chart.defaults.backgroundColor = surfaceContainer;
Chart.defaults.font.family     = "'Inter', sans-serif";
Chart.defaults.font.size       = 11;

// Default dataset colour palette (cycle through brand colours)
Chart.defaults.plugins.colors = { enabled: false }; // we set manually

// Expose colour palette for inline scripts
window.PJBLChartColors = { primary, secondary, tertiary };

// ── Export Chart constructor ───────────────────────────────────────────────────
window.PJBLChart = Chart;
