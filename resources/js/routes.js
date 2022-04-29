import VueRouter from 'vue-router';
import AuthModal from "./components/modals/AuthModal";

const routes = [
    { path: '*', component: require('./components/views/PageNotFound.vue').default },

    { path: '/', component: require('./components/views/CasinoIndex.vue').default },
    { path: '/casino/', component: require('./components/views/CasinoIndex.vue').default },

    { path: '/casino/game/category/:category', component: require('./components/views/GameCategory.vue').default },
    { path: '/casino/game/:id', component: require('./components/views/Game.vue').default, prop: true },

    { path: '/password/reset/:user/:token', component: require('./components/views/CasinoIndex.vue').default },
    { path: '/fairness', component: require('./components/views/Fairness.vue').default },
    { path: '/partner', component: require('./components/views/Affiliate.vue').default },
    { path: '/profile/:tag', component: require('./components/views/Profile.vue').default, props: true },
    { path: '/wallet', component: require('./components/views/Wallet.vue').default, meta: { requiresAuth: true } },
    { path: '/bonus', component: require('./components/views/Bonus.vue').default },

    { path: '/admin', component: require('./components/views/admin/Dashboard.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/promo', component: require('./components/views/admin/Promo.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/settings', component: require('./components/views/admin/Settings.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/notifications', component: require('./components/views/admin/Notifications.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/users', component: require('./components/views/admin/Users.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/user/:id', component: require('./components/views/admin/User.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/wallet', component: require('./components/views/admin/Wallet.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/wallet_ignored', component: require('./components/views/admin/WalletIgnored.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/modules', component: require('./components/views/admin/Modules.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/currency', component: require('./components/views/admin/Currency.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/activity', component: require('./components/views/admin/Activity.vue').default, meta: { requiresAccess: 'admin' } },
    { path: '/admin/bot', component: require('./components/views/admin/Bot.vue').default, meta: { requiresAccess: 'admin' } },

    /*
    { path: '/coming-soon', component: require('./components/views/ComingSoon.vue').default },
    { path: '/coming-soon-avoid', component: require('./components/views/ComingSoon.vue').default }
     */
];

const router = new VueRouter({
    routes,
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) return savedPosition;
        else return { x: 0, y: 0 };
    }
});

router.beforeEach((to, from, next) => {
    const user = (JSON.parse(localStorage.getItem('vuex')) ?? []).user;

    $('.flatpickr-calendar, .modal-backdrop').remove();
    $('body').removeClass('modal-open');

    let countDownDate = new Date("Jan 9, 2021 13:00:00 UTC").getTime(), now = new Date().getTime(), distance = countDownDate - now;
    if(document.cookie.indexOf('avoidComingSoon') === -1 && distance > 0 && !to.fullPath.startsWith('/coming-soon') && !window.allowed) return false;

    const redirect = () => {
        if(window.Layout.Previous && ((to.fullPath.startsWith('/admin') && from.fullPath.startsWith('/admin')) || (!to.fullPath.startsWith('/admin') && !from.fullPath.startsWith('/admin'))))
            return next();

        window.Layout.Previous = true;
        $('[data-rendered-layout]').remove();
        $('head').append($('<style>').attr('data-rendered-layout', 1).html(atob(window.Layout[to.fullPath.startsWith('/admin') && (user && user.user.access === 'admin') ? 'Backend' : 'Frontend'])));
        next();
    };

    if(to.matched.some(record => record.meta.requiresAuth)) {
        if(!(JSON.parse(localStorage.getItem('vuex')) ?? []).user) {
            AuthModal.methods.open('auth');
            return false;
        } else redirect();
    }

    if(to.matched.some(record => record.meta.requiresAccess)) {
        const access = {
            user: 0,
            moderator: 1,
            admin: 2
        };

        if(!(!user || access[user.user.access ?? 'user'] < access[to.meta.requiresAccess])) redirect();
    } else redirect();
});

export default router;
