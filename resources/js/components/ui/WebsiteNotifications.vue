<template>
    <transition mode="out-in" name="fade">
        <div class="notificationsContainer" v-if="show" :key="1">
            <div class="notifications">
                <i class="fal fa-times" data-close-notifications @click="() => show = false"></i>
                <div class="title">{{ $t('general.notifications.title') }}</div>
                <div class="notifications-content os-host-flexbox">
                    <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                        <div role="alert" class="toast" data-autohide="false" v-for="notification in log">
                            <div class="toast-header">
                                <icon :icon="notification.icon" style="margin-right: 5px"></icon>
                                <span class="mr-auto" v-html="$t(notification.title)"></span>
                                <i @click="dismiss(notification._id)" class="fal fa-times" data-dismiss="toast"></i>
                            </div>
                            <div class="toast-body" v-html="$t(notification.message, notification.data)"></div>
                        </div>
                    </overlay-scrollbars>
                </div>
            </div>
            <div class="notifications-overlay" v-if="show" @click="() => show = !show"></div>
        </div>
    </transition>
</template>

<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    const defaultNotificationHandler = (notification) => { return { title: notification.title, message: notification.message, data: notification.data } };

    export default {
        data() {
            return {
                show: false,

                log: [],

                notifications: {
                    'App\\Notifications\\CustomNotification': defaultNotificationHandler,
                    'App\\Notifications\\WithdrawAccepted': defaultNotificationHandler,
                    'App\\Notifications\\WithdrawDeclined': defaultNotificationHandler,
                    'App\\Notifications\\VipDiscordNotification': defaultNotificationHandler,
                    'App\\Notifications\\TipNotification': defaultNotificationHandler,
                    default_icon: 'fad fa-galaxy'
                }
            }
        },
        computed: {
            ...mapGetters(['isGuest', 'user'])
        },
        methods: {
            dismiss(id) {
                this.log = this.log.filter((e) => e._id !== id);
                axios.post('/api/notifications/mark', { id: id });

                if($('.notifications-content .toast.show').length <= 1) $('[data-notification-attention]').fadeOut('fast', function() {
                    $(this).remove();
                });
            },
            add(notification, meta) {
                this.log.push({
                    _id: notification.id,
                    title: meta.title,
                    message: meta.message,
                    icon: meta.icon ? meta.icon : this.notifications.default_icon,
                    data: meta.data
                });

                if($('[data-notification-attention]').length === 0) $('[data-notification-view]').prepend(`<span class="notification pulsating-circle" data-notification-attention></span>`);
            }
        },
        created() {
            Bus.$on('notifications:toggle', () => this.show = !this.show);

            if(!this.isGuest) {
                Bus.$on('event:notification', (notification) => {
                    const meta = this.notifications[notification.type](notification);
                    this.add(notification, meta);
                });

                axios.post('/api/notifications/unread').then(({ data }) => {
                    _.forEach(data.notifications, (notification) => {
                        const meta = this.notifications[notification.type](notification.data);
                        this.add(notification, meta);
                    });
                });
            }
        }
    }
</script>

<style lang="scss">
    .notificationsContainer {
        z-index: 50000;

        .toast {
            opacity: 1 !important;
        }

        .os-host {
            width: 100%;
        }
    }

    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
        opacity: 1;
    }

    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
</style>
