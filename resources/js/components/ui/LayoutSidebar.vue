<template>
    <div :class="'sidebar ' + (sidebar ? 'visible' : 'hidden')">
        <div class="fixed">
            <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }" class="games">
                <router-link tag="div" to="/casino/game/category/favorite" class="game">
                    <icon icon="fal fa-star"></icon>
                    <div>{{ $t('general.sidebar.favorite') }}</div>
                </router-link>

                <router-link tag="div" to="/casino/game/category/recent" class="game">
                    <icon icon="fal fa-history"></icon>
                    <div>{{ $t('general.sidebar.recent') }}</div>
                </router-link>

                <router-link tag="div" to="/admin" v-if="!isGuest && user.user.access === 'admin'" class="game">
                    <i class="fas fa-cog"></i>
                    <div>{{ $t('general.sidebar.admin') }}</div>
                </router-link>

                <div class="divider"></div>

                <router-link tag="div" to="/bonus" class="game highlight">
                    <icon icon="fas fa-stars"></icon>
                    <div>{{ $t('general.head.bonus') }}</div>
                </router-link>

                <router-link tag="div" to="/partner" class="game">
                    <icon icon="fas fa-user-secret"></icon>
                    <div>{{ $t('footer.affiliates') }}</div>
                </router-link>

                <router-link tag="div" to="/fairness" class="game">
                    <icon icon="fal fa-badge-check"></icon>
                    <div>{{ $t('fairness.title') }}</div>
                </router-link>

                <div class="divider"></div>

                <router-link v-for="game in games" v-if="!game.isDisabled" :key="game.id" tag="div" :to="`/casino/game/` + game.id" class="game">
                    <icon :icon="game.icon"></icon>
                    <div>{{ game.name }}</div>
                </router-link>

                <content-placeholders class="game" v-for="n in 17" :key="n" v-if="games.length === 0">
                    <content-placeholders-img/>
                </content-placeholders>

                <div class="recentTpGames">
                    <loader v-if="!lastGames"></loader>
                    <template v-else>
                        <div class="recentTpGame" v-for="game in lastGames" :key="game.game._id">
                            <div class="info">
                                <router-link tag="div" :to="`/casino/game/${game.metadata.id}`" class="image">
                                    <icon :icon="game.metadata.icon"></icon>
                                </router-link>
                                <div class="meta">
                                    <router-link :to="`/profile/${game.game.user}`" class="player">{{ game.user.name }}</router-link>
                                    <div class="currency"><icon :icon="currencies[game.game.currency].icon" :style="{ color: currencies[game.game.currency].style }"></icon> <unit :to="game.game.currency" :value="game.game.profit"></unit></div>
                                    <router-link :to="`/casino/game/${game.metadata.id}`" class="gameName">{{ game.metadata.name }}</router-link>
                                </div>
                            </div>
                            <router-link tag="div" :to="`/casino/game/${game.metadata.id}`" class="btn btn-primary">{{ $t('general.play_now') }}</router-link>
                        </div>
                    </template>
                </div>
            </overlay-scrollbars>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
import AuthModal from "../modals/AuthModal";
    import Bus from "../../bus";

    export default {
        computed: {
            ...mapGetters(['isGuest', 'user', 'theme', 'games', 'currencies', 'sidebar'])
        },
        data() {
            return {
                lastGames: null,
                maxEntries: 25
            }
        },
        created() {
            this.getGames();

            Bus.$on('event:liveGame', (e) => setTimeout(() => this.lastGames.unshift(e), e.delay));
        },
        watch: {
            lastGames() {
                if(this.lastGames && this.lastGames.length >= this.maxEntries) this.lastGames.pop();
            }
        },
        methods: {
            openAuthModal(type) {
                AuthModal.methods.open(type);
            },
            getGames() {
                this.lastGames = null;
                axios.post('/api/data/latestGames', {
                    type: 'all',
                    count: this.maxEntries
                }).then(({ data }) => this.lastGames = data.reverse());
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .sidebar.visible {
        width: 180px;

        .recentTpGames {
            display: flex !important;
        }

        .fixed {
            width: 180px;

            .game.router-link-exact-active {
                &:before {
                    background: rgba(black, .2);
                }
            }

            .game {
                justify-content: unset;
                padding-left: 17px;
                padding-right: 17px;
                position: relative;

                i {
                    width: 25px;
                }

                svg {
                    margin-right: 11px;
                }

                div {
                    display: block;
                    opacity: 1;
                }
            }
        }
    }

    .game.highlight {
        @include themed() {
            opacity: 1 !important;
            color: t('secondary') !important;
        }
    }

    .sidebar.visible ~.pageWrapper {
        padding-left: 180px;
    }

    .sidebar {
        width: $sidebar-width;
        flex-shrink: 0;
        z-index: 38002;
        transition: width 0.3s ease;

        .fixed {
            position: fixed;
            top: 0;
            width: $sidebar-width;
            height: 100%;
            border-radius: 3px;
            padding: 15px 0;

            @include themed() {
                border-right: 2px solid t('border');
                background: rgba(t('sidebar'), .8);
                backdrop-filter: blur(20px);
                transition: background 0.15s ease, width .3s ease;

                .games {
                    //height: calc(100% - 35px);
                    height: 100%;
                    border-radius: 3px;

                    .divider {
                        margin-top: 5px !important;
                    }

                    .recentTpGames {
                        display: none;
                        width: 100%;
                        flex-direction: column;
                        margin-top: 10px;
                        border-top: 2px solid t('border');
                        padding-top: 15px;

                        .loaderContainer {
                            width: 100%;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            transform: scale(0.6);
                        }

                        .btn {
                            text-transform: uppercase;
                            margin-top: 10px;
                        }

                        .recentTpGame {
                            display: flex;
                            flex-direction: column;

                            .info {
                                display: flex;
                                padding-left: 15px;
                                padding-right: 15px;

                                .image {
                                    width: 50px;
                                    height: 50px;
                                    background-position: center;
                                    background-size: cover;
                                    margin-right: 10px;
                                    border-radius: 3px;
                                    cursor: pointer;
                                    display: flex;

                                    svg, i {
                                        margin: auto;
                                        font-size: 1.8em;
                                        color: t('secondary');
                                        opacity: .8;
                                    }
                                }

                                .meta {
                                    width: calc(100% - 50px);
                                    font-size: 0.8em;
                                    font-weight: 600;

                                    .gameName {
                                        text-transform: uppercase;
                                    }
                                }
                            }
                        }
                    }

                    .btn {
                        width: calc(100% - 30px);
                        margin-left: 15px;
                        margin-right: 15px;
                        margin-bottom: 15px;
                        border-radius: 20px;

                        &.btn-primary {
                            border-bottom: 3px solid darken(t('secondary'), 5%);
                        }

                        &.btn-secondary {
                            border-bottom: 3px solid darken($gray-600, 5%);
                        }
                    }
                }
            }

            .game {
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0.4;
                transition: opacity 0.3s ease;
                width: 100%;
                height: 40px;
                font-size: 14px;
                cursor: pointer;
                position: relative;

                &:before {
                    transition: background .3s ease;
                    content: '';
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                    height: 100%;
                }

                div {
                    display: none;
                    opacity: 0;
                    transition: opacity 1s ease;
                }

                .vue-content-placeholders-img {
                    height: 15px;
                    width: 15px;
                    border-radius: 3px;
                }

                img {
                    width: 14px;
                    height: 14px;
                }

                i {
                    cursor: pointer;
                }

                &:hover {
                    opacity: 1;
                }

                .online {
                    position: absolute !important;
                    top: 4px !important;
                    left: 17px !important;
                    border-radius: 50%;
                    width: 15px;
                    @include themed() {
                        background: t('secondary');
                    }
                    color: white;
                    height: 15px;
                    font-size: 0.5em;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                }
            }

            .game.router-link-exact-active {
                opacity: 1;
            }
        }
    }

    @include media-breakpoint-down(md) {
        .sidebar {
            display: none;
        }
    }
</style>
