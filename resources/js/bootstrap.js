window._ = require('lodash');
window.Popper = require('popper.js').default;
window.$ = window.jQuery = require('jquery');

import TwoFactorAuthModal from "./components/modals/TwoFactorAuthModal";
import InvalidTokenModal from "./components/modals/InvalidTokenModal";

require('bootstrap');

window.axios = require('axios');

window.axios.defaults.withCredentials = true;
window.axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
};

window.axios.interceptors.response.use((response) => response, (error) => {
    if(error.response) {
        if(error.response.status === 419) { // Invalid CSRF Token
            if(!window.$reloading) {
                InvalidTokenModal.methods.open();
                window.location.reload();
            }

            window.$reloading = true;
        }

        if(error.response.data && error.response.data.code === -1024) return new Promise((resolve, reject) => TwoFactorAuthModal.methods.open(error, resolve, reject));
    }

    return Promise.reject(error);
});

let vuex = null;
if(localStorage.getItem('vuex')) {
    vuex = JSON.parse(localStorage.getItem('vuex'));
    if(vuex.user) window.axios.defaults.headers.common['Authorization'] = `Bearer ${vuex.user.token}`;
}

import Echo from 'laravel-echo';

window.io = require('socket.io-client');

window.LaravelEcho = Echo;

window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: `${window.location.hostname}:2087`,
    auth: {
        headers: {
            Authorization: `Bearer ${vuex && vuex.user ? vuex.user.token : null}`
        }
    }
});

// Polyfills

String.prototype.replaceAll = String.prototype.replaceAll || function(str, find, replace) {
    return str.replace(new RegExp(find, 'g'), replace);
};
