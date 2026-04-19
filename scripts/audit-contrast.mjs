#!/usr/bin/env node
/**
 * Audit Tailwind surface/text class pairings in blade files for WCAG AA contrast.
 * Reports pairs under 4.5:1 (body) or 3:1 (large/icon), and flags unrecognized utilities.
 *
 * Usage: npm run audit:contrast
 */

import { readFileSync, readdirSync, statSync } from 'fs';
import { join, relative } from 'path';
import { fileURLToPath } from 'url';
import { dirname } from 'path';

const __dirname = dirname(fileURLToPath(import.meta.url));
const ROOT = join(__dirname, '..');

// ── Color token table ────────────────────────────────────────────────────────
// Format: { light: [r,g,b], dark: [r,g,b] }
const TOKENS = {
    'surface':                    { light: [250,248,255], dark: [17,19,24] },
    'surface-dim':                { light: [217,217,229], dark: [17,19,24] },
    'surface-bright':             { light: [250,248,255], dark: [55,57,63] },
    'surface-variant':            { light: [225,226,237], dark: [67,70,85] },
    'surface-container':          { light: [237,237,249], dark: [29,31,39] },
    'surface-container-low':      { light: [243,243,254], dark: [25,27,35] },
    'surface-container-high':     { light: [231,231,243], dark: [40,42,50] },
    'surface-container-highest':  { light: [225,226,237], dark: [51,53,61] },
    'surface-container-lowest':   { light: [255,255,255], dark: [13,14,20] },
    'on-surface':                 { light: [25,27,35],    dark: [227,226,238] },
    'on-surface-variant':         { light: [67,70,85],    dark: [195,198,215] },
    'background':                 { light: [250,248,255], dark: [17,19,24] },
    'on-background':              { light: [25,27,35],    dark: [227,226,238] },
    'outline':                    { light: [115,118,134], dark: [141,143,158] },
    'outline-variant':            { light: [195,198,215], dark: [67,70,85] },
    'primary':                    { light: [0,74,198],    dark: [0,74,198] },
    'primary-container':          { light: [37,99,235],   dark: [37,99,235] },
    'on-primary':                 { light: [255,255,255], dark: [255,255,255] },
    'on-primary-container':       { light: [238,239,255], dark: [238,239,255] },
    'on-primary-fixed':           { light: [0,23,75],     dark: [0,23,75] },
    'on-primary-fixed-variant':   { light: [0,62,168],    dark: [0,62,168] },
    'primary-fixed':              { light: [219,225,255], dark: [219,225,255] },
    'primary-fixed-dim':          { light: [180,197,255], dark: [180,197,255] },
    'inverse-primary':            { light: [180,197,255], dark: [0,74,198] },
    'inverse-surface':            { light: [46,48,57],    dark: [227,226,238] },
    'inverse-on-surface':         { light: [240,240,251], dark: [46,48,57] },
    'secondary':                  { light: [0,108,73],    dark: [0,108,73] },
    'secondary-container':        { light: [108,248,187], dark: [108,248,187] },
    'on-secondary':               { light: [255,255,255], dark: [255,255,255] },
    'on-secondary-container':     { light: [0,113,77],    dark: [0,113,77] },
    'on-secondary-fixed':         { light: [0,33,19],     dark: [0,33,19] },
    'on-secondary-fixed-variant': { light: [0,82,54],     dark: [0,82,54] },
    'secondary-fixed':            { light: [111,251,190], dark: [111,251,190] },
    'secondary-fixed-dim':        { light: [78,222,163],  dark: [78,222,163] },
    'tertiary':                   { light: [62,63,204],   dark: [62,63,204] },
    'tertiary-container':         { light: [88,91,230],   dark: [88,91,230] },
    'on-tertiary':                { light: [255,255,255], dark: [255,255,255] },
    'on-tertiary-container':      { light: [241,238,255], dark: [241,238,255] },
    'on-tertiary-fixed':          { light: [7,0,108],     dark: [7,0,108] },
    'on-tertiary-fixed-variant':  { light: [47,46,190],   dark: [47,46,190] },
    'tertiary-fixed':             { light: [225,224,255], dark: [225,224,255] },
    'tertiary-fixed-dim':         { light: [192,193,255], dark: [192,193,255] },
    'error':                      { light: [186,26,26],   dark: [186,26,26] },
    'error-container':            { light: [255,218,214], dark: [255,218,214] },
    'on-error':                   { light: [255,255,255], dark: [255,255,255] },
    'on-error-container':         { light: [147,0,10],    dark: [147,0,10] },
    'warning':                    { light: [234,179,8],   dark: [234,179,8] },
    'warning-light':              { light: [254,249,195], dark: [254,249,195] },
    'on-warning':                 { light: [66,32,6],     dark: [66,32,6] },
    'white':                      { light: [255,255,255], dark: [255,255,255] },
    'black':                      { light: [0,0,0],       dark: [0,0,0] },
};

const BG_PREFIXES = ['bg-'];
const TEXT_PREFIXES = ['text-'];

// ── WCAG helpers ─────────────────────────────────────────────────────────────
function linearize(c) {
    const v = c / 255;
    return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
}

function luminance([r, g, b]) {
    return 0.2126 * linearize(r) + 0.7152 * linearize(g) + 0.0722 * linearize(b);
}

function contrast(fg, bg) {
    const l1 = luminance(fg);
    const l2 = luminance(bg);
    const lighter = Math.max(l1, l2);
    const darker = Math.min(l1, l2);
    return (lighter + 0.05) / (darker + 0.05);
}

// ── File walker ──────────────────────────────────────────────────────────────
function* walk(dir) {
    for (const entry of readdirSync(dir)) {
        const full = join(dir, entry);
        if (statSync(full).isDirectory()) yield* walk(full);
        else if (full.endsWith('.blade.php')) yield full;
    }
}

// ── Class extractor ──────────────────────────────────────────────────────────
const CLASS_RE = /class="([^"]+)"/g;
const MULTICLASS_RE = /class='([^']+)'/g;

function extractClasses(line) {
    const results = [];
    for (const re of [CLASS_RE, MULTICLASS_RE]) {
        re.lastIndex = 0;
        let m;
        while ((m = re.exec(line)) !== null) {
            results.push(...m[1].split(/\s+/).filter(Boolean));
        }
    }
    return results;
}

function tokenFromClass(cls, prefix) {
    if (!cls.startsWith(prefix)) return null;
    const rest = cls.slice(prefix.length);
    const token = rest.replace(/\/.*$/, ''); // strip opacity
    return token;
}

// ── Main ─────────────────────────────────────────────────────────────────────
const viewsDir = join(ROOT, 'resources', 'views');
let issues = 0;
let unknownCount = 0;

const KNOWN_TOKENS = new Set(Object.keys(TOKENS));

for (const file of walk(viewsDir)) {
    const rel = relative(ROOT, file);
    const lines = readFileSync(file, 'utf8').split('\n');

    lines.forEach((line, i) => {
        const classes = extractClasses(line);

        // Flag unrecognized opacity patterns
        for (const cls of classes) {
            if (cls.match(/\/([\d]+)\/([\d]+)/)) {
                console.log(`TYPO   ${rel}:${i + 1}  ${cls}  (double-slash opacity)`);
                unknownCount++;
            }
        }

        // Check contrast pairs
        const bgClasses = classes.filter(c => BG_PREFIXES.some(p => c.startsWith(p)));
        const textClasses = classes.filter(c => TEXT_PREFIXES.some(p => c.startsWith(p)));

        // Collect dark: variant overrides — if dark:text-X is present, those ARE the dark text colors
        const darkTextTokens = classes
            .filter(c => c.startsWith('dark:text-'))
            .map(c => c.slice('dark:text-'.length).replace(/\/.*$/, ''))
            .filter(t => KNOWN_TOKENS.has(t));
        const darkBgTokens = classes
            .filter(c => c.startsWith('dark:bg-'))
            .map(c => c.slice('dark:bg-'.length).replace(/\/.*$/, ''))
            .filter(t => KNOWN_TOKENS.has(t));

        for (const bg of bgClasses) {
            const bgToken = tokenFromClass(bg, 'bg-');
            if (!bgToken || !KNOWN_TOKENS.has(bgToken)) continue;

            for (const text of textClasses) {
                const textToken = tokenFromClass(text, 'text-');
                if (!textToken || !KNOWN_TOKENS.has(textToken)) continue;

                for (const theme of ['light', 'dark']) {
                    // In dark mode, if dark: overrides exist use them instead
                    const effectiveFgTokens = theme === 'dark' && darkTextTokens.length > 0
                        ? darkTextTokens : [textToken];
                    const effectiveBgTokens = theme === 'dark' && darkBgTokens.length > 0
                        ? darkBgTokens : [bgToken];

                    for (const ft of effectiveFgTokens) {
                        for (const bt of effectiveBgTokens) {
                            const bgColor = TOKENS[bt][theme];
                            const fgColor = TOKENS[ft][theme];
                            const ratio = contrast(fgColor, bgColor);

                            if (ratio < 4.5) {
                                const level = ratio < 3 ? 'FAIL  ' : 'WARN  ';
                                const fgLabel = ft !== textToken ? `dark:text-${ft}` : text;
                                const bgLabel = bt !== bgToken ? `dark:bg-${bt}` : bg;
                                                console.log(`${level} ${rel}:${i + 1}  ${fgLabel} on ${bgLabel}  ratio=${ratio.toFixed(2)}:1  [${theme}]`);
                                issues++;
                            }
                        }
                    }
                }
            }
        }
    });
}

console.log(`\n── Summary ─────────────────────────────────────────`);
console.log(`Contrast issues: ${issues}`);
console.log(`Typo classes:    ${unknownCount}`);
if (issues === 0 && unknownCount === 0) console.log('All clear.');
