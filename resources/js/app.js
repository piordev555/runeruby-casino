require('./bootstrap');

window.Vue = require('vue');

import router from './routes';
import store from './store';
import i18n from './i18n';

import Vuex from 'vuex';
import VueRouter from 'vue-router';
import VueI18n from 'vue-i18n';
import VueIziToast from 'vue-izitoast';
import vueHljs from "vue-hljs";
import VueFeather from 'vue-feather';
import VueMask from 'v-mask';
import money from 'v-money';
import vClickOutside from 'v-click-outside';
import infiniteScroll from 'vue-infinite-scroll';
import ToggleButton from 'vue-js-toggle-button';
import VueCountdown from '@chenfengyuan/vue-countdown';
import VTooltip from 'v-tooltip';
import VueContext from 'vue-context';

import VueContentPlaceholders from 'vue-content-placeholders';
import { OverlayScrollbarsPlugin } from 'overlayscrollbars-vue';
import VueCompositionAPI from '@vue/composition-api';

Vue.use(VueCompositionAPI);
Vue.use(VueRouter);
Vue.use(Vuex);
Vue.use(VueI18n);
Vue.use(OverlayScrollbarsPlugin);
Vue.use(VueContentPlaceholders);
Vue.use(vueHljs);
Vue.use(VueMask);
Vue.use(money, { precision: 4 });
Vue.use(VueIziToast, { position: 'topRight' });
Vue.use(vClickOutside);
Vue.use(VueFeather);
Vue.use(infiniteScroll);
Vue.use(ToggleButton);
Vue.use(VTooltip);

Vue.component(VueCountdown.name, VueCountdown);
Vue.component('vue-context', VueContext);

import ApexCharts from 'apexcharts';
window.ApexCharts = ApexCharts;

require('./mixins/whisper');
require('./mixins/game');

const files = require.context('./', true, /\.vue$/i);
files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

const app = new Vue({
    el: '#app',
    router,
    store,
    i18n
});

Vue.prototype.$isDebug = () => (process.env.MIX_APP_DEBUG === 'true' || process.env.MIX_APP_DEBUG === true) || (store.state.user && store.state.user.user.access === 'admin');

if(!app.$isDebug())
    setInterval(() => {
        console.clear();
        console.log('%cStop!', 'background: black; color: red; font-size: 3.5em');
        console.log('%cThis is a browser feature intended for developers. If someone told you to copy and paste something here to enable a feature or \'hack\' someone\'s account, it is a scam and will give them access to your account.', 'font-size: 1.5em');
    }, 1000);

