import Vue from 'vue'
import Router from 'vue-router'
import Catalogue from './components/catalogue';
import Upload from './components/upload';
import NotFound from './components/notFound';

Vue.use(Router);

const routes = [
    {
        path: '/catalogue',
        component: Catalogue
    },
    {
        path: '/catalogue/:page',
        component: Catalogue
    },
    {
        path: '/',
        component: Upload
    },
    {
        path: '*',
        component: NotFound
    },
];

export default new Router({
    mode: "history",
    routes
});