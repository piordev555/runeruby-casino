<template>
    <div class="container-fluid">
        <transition name="fade" mode="out-in">
            <div class="promotions-modal-wrap" v-if="toggleSidebar" :key="1">
                <div class="promotions-modal-back" v-if="toggleSidebar" @click="toggleSidebar = false"></div>
                <div class="promotions-modal" v-if="toggleSidebar">
                    <icon icon="fal fa-times" class="promotions-modal-close" @click.native="toggleSidebar = false"></icon>
                    <component :is="sidebarComponent"></component>
                </div>
            </div>
        </transition>

        <div class="promotions-title">{{ $t('general.promotions.title') }}</div>
        <div class="promotions">
            <div class="promotion" :style="{ 'background-image': `url('/img/misc/promotions/faucet.png')`, color: 'white' }" @click="faucet()">
                <div class="title">{{ $t('general.promotions.faucet.title') }}</div>
                <div class="description">{{ $t('general.promotions.faucet.description') }}</div>
                <button class="btn btn-primary">{{ $t('general.promotions.faucet.btn') }}</button>
            </div>
            <div class="promotion" :style="{ 'background-image': `url('/img/misc/promotions/vip.png')`, color: 'white' }" @click="vip()">
                <div class="title">{{ $t('general.promotions.vip.title') }}</div>
                <div class="description">{{ $t('general.promotions.vip.description') }}</div>
                <button class="btn btn-primary">{{ $t('general.promotions.vip.btn') }}</button>
            </div>
            <div class="promotion" :style="{ 'background-image': `url('/img/misc/promotions/notification.png')`, color: 'white' }" @click="notifications()">
                <div class="title">{{ $t('general.promotions.notification.title') }}</div>
                <div class="description">{{ $t('general.promotions.notification.description') }}</div>
                <button class="btn btn-primary" data-notification-button>{{ $t('general.promotions.notification.btn') }}</button>
            </div>
            <div class="promotion" :style="{ 'background-image': `url('/img/misc/promotions/promo.png')`, color: 'white' }" @click="promo()">
                <div class="title">{{ $t('general.promotions.promo.title') }}</div>
                <div class="description">{{ $t('general.promotions.promo.description') }}</div>
                <button class="btn btn-primary">{{ $t('general.promotions.promo.btn') }}</button>
            </div>
            <div class="promotion" :style="{ 'background-image': `url('/img/misc/promotions/rain.png')`, color: 'white' }" @click="rain()">
                <div class="title">{{ $t('general.promotions.rain.title') }}</div>
                <div class="description">{{ $t('general.promotions.rain.description') }}</div>
                <button class="btn btn-primary">{{ $t('general.promotions.rain.btn') }}</button>
            </div>
            <div class="promotion" :style="{ 'background-image': `url('/img/misc/promotions/affiliate.png')`, color: 'white' }" @click="affiliates()">
                <div class="title">{{ $t('general.promotions.affiliates.title') }}</div>
                <div class="description">{{ $t('general.promotions.affiliates.description') }}</div>
                <router-link tag="button" to="/partner" class="btn btn-primary">{{ $t('general.promotions.affiliates.btn') }}</router-link>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import AuthModal from "../modals/AuthModal";
    import VipModal from "../modals/VipModal";
    import VipBonusModal from "../modals/VipBonusModal";
    import TermsModal from "../modals/TermsModal";

    export default {
        data() {
            return {
                toggleSidebar: false,
                sidebarComponent: ''
            }
        },
        computed: {
            ...mapGetters(['user', 'isGuest'])
        },
        methods: {
            faucet() {
                if(this.isGuest) AuthModal.methods.open('auth');
                else {
                    this.sidebarComponent = 'promotion-faucet';
                    this.toggleSidebar = true;
                }
            },
            vip() {
                if(this.isGuest) AuthModal.methods.open('auth');
                else if(this.user.user.vipLevel === 0) VipModal.methods.open();
                else VipBonusModal.methods.open();
            },
            affiliates() {
                this.$router.push('/partner');
            },
            rain() {
                TermsModal.methods.open('rain');
            },
            promo() {
                if(this.isGuest) AuthModal.methods.open('auth');
                else {
                    this.sidebarComponent = 'promotion-promocode';
                    this.toggleSidebar = true;
                }
            },
            notifications() {
                if(this.isGuest) AuthModal.methods.open('auth');
                else {
                    if('serviceWorker' in navigator) {
                        const urlBase64ToUint8Array = function(base64String) {
                            const padding = '='.repeat((4 - base64String.length % 4) % 4);
                            const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
                            const rawData = window.atob(base64);
                            const outputArray = new Uint8Array(rawData.length);
                            for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
                            return outputArray
                        };

                        const subscribe = () => {
                            navigator.serviceWorker.ready.then(registration => {
                                const options = { userVisibleOnly: true };
                                const vapidPublicKey = window.Notification.vapidPublicKey;

                                if(vapidPublicKey) options.applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);

                                registration.pushManager.subscribe(options).then(subscription => {
                                    updateSubscription(subscription);
                                }).catch(e => {
                                    if(Notification.permission === 'denied') {
                                        console.log('Permission for Notifications was denied');
                                        this.$toast.error('Notifications are disabled');
                                    } else {
                                        this.$toast.error('Notifications are disabled');
                                        console.error('Unable to subscribe to push', e);
                                    }
                                });
                            });
                        };

                        const updateSubscription = (subscription) => {
                            const key = subscription.getKey('p256dh');
                            const token = subscription.getKey('auth');
                            const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0];
                            const data = {
                                endpoint: subscription.endpoint,
                                publicKey: key ? btoa(String.fromCharCode.apply(null, new Uint8Array(key))) : null,
                                authToken: token ? btoa(String.fromCharCode.apply(null, new Uint8Array(token))) : null,
                                contentEncoding
                            };

                            axios.post('/api/subscription/update', data).then(() => $('[data-notification-button]').attr('disabled', 'disabled').addClass('disabled'));
                        };

                        navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(() => {
                            if(!('showNotification' in ServiceWorkerRegistration.prototype)) {
                                console.error('Notifications aren\'t supported');
                                return;
                            }

                            if(!('PushManager' in window)) {
                                console.error('Push messaging isn\'t supported');
                                return;
                            }

                            navigator.serviceWorker.ready.then(registration => {
                                registration.pushManager.getSubscription().then(subscription => {
                                    if(!subscription) return;

                                    updateSubscription(subscription);
                                }).catch(e => {
                                    console.error('Error during getSubscription()', e);
                                });
                            });
                        });

                        subscribe();
                    } else console.error('ServiceWorker isn\'t supported');
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    @import "resources/sass/variables";

    .promotions-modal-back {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 99998;
        background: rgba(black, .5);
        height: 100%;
        width: 100%;
    }

    .promotions-modal-wrap {
        z-index: 99999 !important;
    }

    .promotions-modal {
        position: fixed;
        top: 0;
        right: 0;
        z-index: 99999;
        background: white;
        height: 100%;
        width: 50%;
        padding: 20px;
        border-left: 2px solid rgba(black, .5);
        color: black;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;

        .fa-times {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 1.4em;
            cursor: pointer;
            opacity: 0.8;
            transition: opacity .3s ease;

            &:hover {
                opacity: 1;
            }
        }
    }

    .promotions-title {
        text-align: center;
        font-size: 3em;
        margin-top: 35px;
        margin-bottom: 75px;
    }

    .promotions {
        padding: 0 100px;
        margin-top: 25px;
        margin-bottom: 35px;

        @include themed() {
            .promotion {
                display: inline-flex;
                width: calc(50% - 24px);
                border-radius: 6px;
                margin: 11px 11px 13px;
                padding: 50px;
                flex-direction: column;
                background-color: t('sidebar');
                height: 360px;
                background-size: cover;
                background-repeat: no-repeat;
                background-position: center;

                .title {
                    font-weight: 600;
                    font-size: 2.5em;
                    width: 55%;
                    text-shadow: 1px 1px 2px rgba(black, .4);
                }

                .description {
                    font-weight: 100;
                    font-size: 1.2em;
                    width: 60%;
                    margin-top: 15px;
                    margin-bottom: 25px;
                    text-shadow: 1px 1px 2px rgba(black, .4);
                }

                .btn {
                    border-radius: 50px;
                    width: 180px;
                    padding: 13px;
                }
            }
        }
    }

    @media(max-width: 1500px) {
        .promotions {
            padding: unset;
        }
    }


    @media(max-width: 1100px) {
        .promotions-title {
            font-size: 2.2em;
            margin-top: 15px;
            margin-bottom: 30px;
        }

        .promotions {
            @include themed() {
                .promotion {
                    width: calc(100%);
                    margin: unset;
                    margin-bottom: 15px;
                }
            }
        }
    }

    @media(max-width: 991px) {
        .promotions-modal {
            width: 100% !important;
            left: 0 !important;
            right: unset !important;
            border-left: unset !important;
        }

        .promotions {
            .promotion {
                .title {
                    font-size: 2em !important;
                    width: 100% !important;
                }

                .description {
                    font-size: 1em !important;
                }
            }
        }
    }
</style>
