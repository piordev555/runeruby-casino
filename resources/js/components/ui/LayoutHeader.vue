<template>
    <header>
        <div class="fixed">
            <div class="container-fluid">
                <div class="header-container">
                    <router-link to="/" tag="div" class="logo"></router-link>
                    <icon :icon="sidebar ? 'fal fa-times' : 'fal fa-bars'" class="sidebar-switch" @click.native="$store.dispatch('toggleSidebar')"></icon>
                    <div class="menu">
                        <router-link tag="div" to="/" :class="($route.path.startsWith('/casin')) ? 'active' : ''">{{ $t('general.head.casino') }}</router-link>
                    </div>
                    <content-placeholders v-if="!isGuest && !currencies" class="wallet_loader">
                        <content-placeholders-img/>
                    </content-placeholders>
                    <div class="wallet" v-if="!isGuest && currencies" v-click-outside="() => expand = false">
                        <div :class="`wallet-switcher ${expand ? 'active' : ''}`">
                            <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                                <div v-for="(currency, i) in currencies" v-if="currency.balance" class="option" :key="i" @click="$store.commit('setCurrency', currency.id); setCookie('currency', currency.id, 365); expand = false">
                                    <div class="wallet-switcher-icon">
                                        <icon :icon="currency.icon" :style="{ color: currency.style }"></icon>
                                    </div>
                                    <div class="wallet-switcher-content">
                                        <div><unit :to="currency.id" :value="demo ? currency.balance.demo : currency.balance.real"></unit></div>
                                        <span>{{ currency.name }}</span>
                                    </div>
                                </div>
                            </overlay-scrollbars>
                            <div class="option mt-2" @click="$store.commit('setDemo', !demo)">
                                <div class="wallet-switcher-icon">
                                    <i :class="demo ? 'fas fa-check-square' : 'far fa-square'"></i>
                                </div>
                                <div class="wallet-switcher-content">
                                    {{ $t('general.head.wallet_demo') }}
                                </div>
                            </div>
                            <div class="option select-option mt-1">
                                <div class="wallet-switcher-icon">
                                    <i class="fas fa-dot-circle"></i>
                                </div>
                                <div class="wallet-switcher-content" style="padding-right: 15px; width: 100%">
                                    {{ $t('general.unit') }}:
                                    <select @change="$store.commit('setUnit', selectedUnit)" v-model="selectedUnit">
                                        <option value="normal">Normal</option>
                                        <option value="k">K</option>
                                        <option value="m">M</option>
                                        <option value="b">B</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="btn btn-secondary icon" @click="expand = !expand">
                            <icon :icon="currencies[currency].icon" v-if="currencies[currency]" :style="{ color: currencies[currency].style }"></icon>
                            <i class="fal fa-angle-down"></i>
                        </div>
                        <div class="balance" @click="demo ? openDemoBalanceModal() : $router.push('/wallet')">
                            <unit :to="currency" :value="currencies[currency].balance[demo ? 'demo' : 'real']"></unit>
                            <transition-group mode="out-in" name="balance-a" :style="{ position: 'absolute' }">
                                <span :key="`key-${i}`" v-for="(animate, i) in animated" :class="`animated text-${animate.diff.action === 'subtract' ? 'danger' : 'success'}`">
                                    <unit :to="currency" :value="animate.diff.value"></unit>
                                </span>
                            </transition-group>
                        </div>
                    </div>
                    <div v-if="isGuest" :class="`right ${isGuest ? 'ml-auto' : ''}`">
                        <button class="btn btn-transparent" @click="openAuthModal('auth')">{{ $t('general.auth.login') }}</button>
                        <button class="btn btn-primary" @click="openAuthModal('register')">{{ $t('general.auth.register') }}</button>
                    </div>
                    <div v-else :class="`right ${isGuest ? 'ml-auto' : ''}`">
                        <div class="action" data-notification-view @click="displayNotifications()">
                            <i class="fas fa-bell"></i>
                        </div>
                        <router-link tag="img" :to="`/profile/${user.user._id}`" :src="user.user.avatar"></router-link>
                    </div>
                </div>
            </div>
        </div>
    </header>
</template>

<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';
    import AuthModal from "../modals/AuthModal";
    import DemoBalanceModal from "../modals/DemoBalanceModal";
    import RankingsModal from "../modals/RankingsModal";
    import TermsModal from "../modals/TermsModal";

    export default {
        computed: {
            ...mapGetters(['user', 'isGuest', 'demo', 'unit', 'currency', 'currencies', 'sidebar'])
        },
        data() {
            return {
                expand: false,
                selectedUnit: 'btc',
                animated: [],

                nonLocalFound: false
            }
        },
        mounted() {
            this.selectedUnit = this.unit;

            Bus.$on('event:balanceModification', (e) => {
                setTimeout(() => {
                    const currencies = this.currencies;
                    currencies[e.currency].balance = {
                        real: e.balance,
                        demo: e.demo_balance
                    };
                    this.$store.dispatch('setCurrencies', currencies);

                    this.animated.push(e);
                    setTimeout(() => this.animated = this.animated.filter((a) => a !== e), 1000);
                }, e.delay);
            });
        },
        methods: {
            displayNotifications() {
                Bus.$emit('notifications:toggle');
            },
            openAuthModal(type) {
                AuthModal.methods.open(type);
            },
            openDemoBalanceModal() {
                DemoBalanceModal.methods.open();
            },
            openRankingsModal() {
                RankingsModal.methods.open(this.currencies);
            },
            openFaucetModal() {
                if(this.isGuest) return this.openAuthModal('auth');
                this.$router.push('/bonus');
            },
            openTerms(type) {
                TermsModal.methods.open(type);
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    header {
        height: $header-height;
        display: initial !important;
        flex-shrink: 0;
        z-index: 38001;

        button {
            font-size: 0.9em !important;
            padding: 10px 20px !important;
        }

        .fixed {
            height: $header-height;
            position: sticky;
            left: 0;
            top: 0;
            width: 100%;
            z-index: 10001;

            .sidebar-switch {
                opacity: .4;
                cursor: pointer;
                margin-left: 7px;
            }

            .header-container {
                display: flex;
                align-items: center;
                height: $header-height;

                .menuSwitcher {
                    display: none;
                    margin-left: 15px;
                    opacity: .5;
                    transition: opacity .3s ease;
                    cursor: pointer;

                    &:hover {
                        opacity: 1;
                    }
                }
            }

            @include themed() {
                background: rgba(t('header'), .8);
                backdrop-filter: blur(20px);

                .logo {
                    width: 155px;
                    height: 50px;
                    margin-left: 20px;
                    display: flex;
                    cursor: pointer;
                    background: url('/img/misc/logo.png') no-repeat center;
                    background-size: contain;
                }
            }

            .menu {
                position: absolute;
                left: 235px;
                display: flex;

                @include themed() {
                    div {
                        margin: 10px;
                        color: t('text');
                        transition: color 0.3s ease;
                        position: relative;
                        cursor: pointer;
                        text-transform: uppercase;
                        font-weight: 600;

                        &:first-child {
                            margin-left: 0;
                        }

                        &:last-child {
                            margin-right: 0;
                        }

                        &:after {
                            content: '';
                            position: absolute;
                            bottom: -25px;
                            left: 0;
                            height: 2px;
                            width: 100%;
                            background: transparent;
                            transition: background 0.3s ease;
                        }
                    }

                    div.active {
                        color: t('secondary');

                        &:after {
                            background: t('secondary');
                        }
                    }
                }
            }

            .right {
                display: flex;
                margin-left: 10px;
                align-items: center;

                img {
                    width: 35px;
                    height: 35px;
                    border-radius: 50%;
                    cursor: pointer;
                    margin-left: 10px;
                }

                .action {
                    display: flex;
                    align-content: center;
                    position: relative;

                    .notification {
                        position: absolute !important;
                        top: 7px !important;
                        left: 19px !important;
                        width: 8px !important;
                        height: 8px !important;
                    }

                    i {
                        font-size: 1.25em;
                        margin: 10px;
                        opacity: 0.65;
                        transition: opacity 0.3s ease;

                        &:hover {
                            opacity: 1;
                            cursor: pointer;
                        }
                    }
                }

                .btn {
                    padding: 10px 30px !important;
                    border-radius: 200px !important;
                }
            }

            @include themed() {
                border-bottom: 2px solid t('border');
                transition: border-bottom 0.15s ease;
            }
        }
    }

    @include only_safari('header', (
        display: contents !important
    ));

    @media(max-width: 991px) {
        header .sidebar-switch {
            display: none;
        }
    }

    @media(max-width: 480px) {
        header .logo {
            display: none !important;
        }
    }

    .balance-a-enter-active, .balance-a-leave-active {
        transition: all 1s ease;
    }

    .balance-a-enter {
        margin-top: 25px;
        opacity: 1 !important;
    }

    .balance-a-enter-to {
        margin-top: 0;
        opacity: 0 !important;
    }

    .balance-a-leave, .balance-a-leave-to {
        opacity: 0 !important;
    }
</style>
