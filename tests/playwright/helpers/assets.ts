import { fileURLToPath } from 'node:url';

export const SAMPLE_PDF = fileURLToPath(new URL('../assets/sample.pdf', import.meta.url));
export const SAMPLE_PNG = fileURLToPath(new URL('../assets/sample.png', import.meta.url));
