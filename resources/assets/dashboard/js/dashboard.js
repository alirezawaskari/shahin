require('./bootstrap');
require('classnames');
require('nicescroll');
require('sweetalert');
require('chart.js');
require('vue-chartjs');
require('select2/dist/js/select2.min');
require('./layouts/custom');
require('./layouts/app');
window.summernote = require('summernote/dist/summernote-bs4.min');
require('../summernote/lang/summernote-fa-IR');
window.toastr = require('toastr');
import VuePersianDatetimePicker from 'vue-persian-datetime-picker';
import Vuetable from 'vuetable-2'
import VueEvents from 'vue-events'
import { Form, HasError, AlertError } from 'vform'
import vSelect from 'vue-select'

import Actions from './components/tasks/Actions'
import UserAction from './components/users/Actions'
import FactorAction from './components/factors/Actions'
import TaskModal from './components/tasks/TaskModal'
import ServiceModal from './components/tasks/ServiceModal'
import Chart from './components/charts/Chart'

window.Vue = require('vue');
window.Form = Form;
Vue.use(Vuetable);
Vue.use(VueEvents);zzzz

Vue.component('task-data', require('./components/tasks/TaskData.vue').default);
Vue.component('task-expire-data', require('./components/tasks/TaskExpireData.vue').default);
Vue.component('task-paid-data', require('./components/tasks/TaskPaidData.vue').default);
Vue.component('factor-data', require('./components/factors/FactorData.vue').default);
Vue.component('factoritem-data', require('./components/factoritems/FactorItemData.vue').default);
Vue.component('user-data', require('./components/users/UserData.vue').default);
Vue.component('date-picker', VuePersianDatetimePicker);
Vue.component('task-modal', TaskModal);
Vue.component('service-modal', ServiceModal);
Vue.component('actions', Actions);
Vue.component('user-actions', UserAction);
Vue.component('factor-actions', FactorAction);
Vue.component('factoritem-actions', FactorItemAction);
Vue.component(HasError.name, HasError);
Vue.component(AlertError.name, AlertError);
Vue.component('v-select', vSelect);
Vue.component('chart', Chart);


const app = new Vue({
    el: '#app',
});

