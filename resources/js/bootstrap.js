// Konfigurasi dasar HTTP: expose axios global + tandai request sebagai AJAX (X-Requested-With).
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
