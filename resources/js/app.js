/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue').default;

// Modulo compartido
import shared from './components/shared'

// Importar Librerias
import VueSweetalert2 from 'vue-sweetalert2';

// Importar Estilos

// AgGrip y Thema
import "ag-grid-community/dist/styles/ag-grid.css"; 
import "ag-grid-community/dist/styles/ag-theme-material.css";
// SweetAlert2
import 'sweetalert2/dist/sweetalert2.min.css';


Vue.mixin(shared.AlertsComponent);
Vue.mixin(shared.ReadHttpStatusErrors); 

// Inicializar Librerias
Vue.use(VueSweetalert2);

Vue.component('sites-component', require('./components/admin/SitesComponent.vue').default);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
});
