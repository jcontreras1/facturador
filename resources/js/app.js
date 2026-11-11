import './bootstrap';
import 'bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import { iniciarEmisionComprobante, finalizarEmisionComprobante } from './utils/emisionComprobante.js';
import { createApp } from 'vue/dist/vue.esm-bundler.js';
window.Swal = Swal;
window.iniciarEmisionComprobante = iniciarEmisionComprobante;
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        finalizarEmisionComprobante();
    }
});
window.Alpine = Alpine;
Alpine.start();


import facturaa from './components/factura/a.vue';
import facturab from './components/factura/b.vue';
import facturac from './components/factura/c.vue';


const app = createApp({});
app.component('factura-a', facturaa);
app.component('factura-b', facturab);
app.component('factura-c', facturac);

app.mount('#app');