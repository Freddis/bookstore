import Vue from 'vue'
import Router from 'vue-router'
import Catalogue from './components/catalogue';
import Upload from './components/upload';

Vue.use(Router);

const routes = [
    {
        path: '/catalogue',
        component: Catalogue
    },
    {
        path: '/',
        component: Upload
    },
];

export default new Router({
    mode: "history",
    routes
});