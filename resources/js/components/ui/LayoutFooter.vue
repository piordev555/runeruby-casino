<template>
    <footer>
        <div class="container-fluid">
            <div class="footer">
                <div class="column">
                    <div class="logo">
                        <div class="logo"></div>
                    </div>
                    <div class="links">
                        <div class="group">
                            <a href="javascript:void(0)" @click="openTerms('agreement')">{{ $t('footer.user_agreement') }}</a>
                            <a href="javascript:void(0)" @click="openTerms('faq')">{{ $t('general.head.help') }}</a>
                        </div>
                        <div class="group">
                            <a href="javascript:void(0)" @click="openTerms('privacy_policy')">{{ $t('footer.privacy_policy') }}</a>
                            <router-link to="/partner">{{ $t('footer.affiliates') }}</router-link>
                        </div>
                    </div>
                    <div class="info">
                        <div class="title">{{ $t('footer.accepted_currencies') }}</div>
                        <div class="currencies">
                            <div class="currency" v-for="currency in currencies" v-if="currency.balance" :title="currency.name">
                                <icon :icon="currency.icon" :style="{ color: currency.style }"></icon>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="copy">
                        &copy; 2021 RuneRuby
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script>
    import { mapGetters } from 'vuex';
    import TermsModal from "../modals/TermsModal";

    export default {
        computed: {
            ...mapGetters(['isGuest', 'currencies'])
        },
        methods: {
            openTerms(type) {
                TermsModal.methods.open(type);
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    footer {
        @include themed() {
            background: rgba(t('body'), .8);
            backdrop-filter: blur(20px);

            .footer {
                display: flex;
                flex-direction: column;
                padding: 0 50px;

                .column {
                    display: flex;
                    width: 100%;
                    align-items: center;
                    padding: 40px 0;
                    border-bottom: 1px solid t('sidebar');

                    &:last-child {
                        border-bottom: none;
                        padding: 20px 0 20px 0;
                    }

                    .logo {
                        display: block !important;
                    }

                    .logo, .links, .info {
                        display: inline-flex;
                        width: 100%;
                        flex-direction: column;
                        align-items: center;
                    }

                    .copy {
                        padding-right: 10px;
                    }

                    .links {
                        flex-direction: row;
                        text-align: center;

                        .group {
                            display: inline-flex;
                            flex-direction: column;
                            width: 100%;
                            align-items: center;

                            a {
                                margin-bottom: 10px;

                                &:last-child {
                                    margin-bottom: 0;
                                }
                            }
                        }
                    }

                    .logo {
                        .logo {
                            width: 190px;
                            height: 100px;
                            background: url('/img/misc/logo.png') no-repeat center;
                            background-size: contain;
                        }

                        .socials {
                            display: flex;
                            width: 200px;
                            flex-wrap: wrap;
                            align-items: center;
                            justify-content: center;
                            user-select: none;
                            margin: 15px 0;

                            .social {
                                cursor: pointer;
                                margin-bottom: 5px;
                                margin-right: 5px;
                                width: 25px;
                                height: 25px;
                                background: rgba(t('sidebar'), .5);
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.9em;
                                padding-bottom: 3px;
                                transition: background .3s ease, opacity .3s ease;
                                opacity: 0.2;

                                &:hover {
                                    background: t('sidebar');
                                    opacity: 1;
                                }

                                &:last-child {
                                    font-size: 0.7em;
                                    cursor: default !important;
                                }
                            }
                        }
                    }

                    .info {
                        .currencies {
                            display: flex;
                            width: 200px;
                            flex-wrap: wrap;
                            align-items: center;
                            justify-content: center;
                            user-select: none;
                            margin: 15px 0;

                            .currency {
                                margin-bottom: 5px;
                                margin-right: 5px;
                                width: 25px;
                                height: 25px;
                                background: rgba(t('sidebar'), .5);
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 0.9em;
                                padding-bottom: 3px;
                                transition: background .3s ease;

                                &:hover {
                                    background: t('sidebar');
                                }
                            }
                        }

                        .title {
                            margin-bottom: 5px;
                            font-weight: 600;
                        }
                    }

                    .logoLinks {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        width: 100%;

                        .logoLink {
                            width: 20%;
                            margin-right: 25px;

                            &:last-child {
                                margin-right: 0;
                            }

                            .logo {
                                width: 100%;
                                height: 100px;
                                background-size: contain;
                                background-repeat: no-repeat;
                                background-position: center;
                                filter: grayscale(100%);
                                cursor: pointer;
                                transition: filter .3s ease;

                                &:hover {
                                    filter: none;
                                }
                            }
                        }
                    }

                    .copy {
                        opacity: .3;
                    }

                    .fk {
                        margin-left: auto;
                        display: none;

                        img {
                            width: 175px;
                            height: 50px;
                            filter: invert(1) grayscale(1);
                        }
                    }

                    .w5x-team {
                        opacity: .3;
                        font-size: 0.8em;
                        font-weight: 600;
                        transition: opacity .3s ease;
                        cursor: pointer;

                        &:hover {
                            opacity: 1;
                        }

                        img {
                            width: 15px;
                            height: 11px;
                            margin-top: -1px;
                            margin-right: 2px;
                        }
                    }
                }
            }
        }
    }

    @include media_breakpoint_down(md) {
        footer .footer {
            padding-bottom: 53px !important;

            .column {
                flex-direction: column;
                padding: 20px 0 !important;

                .info {
                    .currencies {
                        width: 100% !important;
                    }
                }

                .logo, .links, .info {
                    margin: 5px 0 15px;
                }

                .logo .logo, .logo .socials {
                    margin-left: auto !important;
                    margin-right: auto !important;
                }

                .links {
                    flex-direction: column !important;

                    .group {
                        margin-bottom: 10px !important;
                    }
                }

                .logoLinks {
                    flex-wrap: wrap !important;

                    .logoLink {
                        width: 100% !important;
                    }
                }

                .copy {
                    margin-right: 0;
                    padding-right: 0 !important;
                    margin-bottom: 5px;
                    margin-left: 4px;
                }
            }
        }
    }
</style>
