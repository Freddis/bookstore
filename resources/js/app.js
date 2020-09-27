require('./bootstrap');

window.Vue = require('vue');
import router from './router';

Vue.component('front', require("./components/front.vue").default);
window.onload = function () {
    var app = new Vue({
        el: '#app',
        data: {
        },
        router
    });
};
