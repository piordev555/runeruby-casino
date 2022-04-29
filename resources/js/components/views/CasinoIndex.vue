<template>
    <div class="container-fluid">
        <div class="slider">
            <div class="glide" id="slider">
                <div class="glide__track" data-glide-el="track">
                    <ul class="glide__slides">
                        <li class="glide__slide" style="background-image: url('/img/misc/slider/slider1.png')"></li>
                        <li class="glide__slide" style="background-image: url('/img/misc/slider/slider2.png')"></li>
                    </ul>
                </div>
                <div class="glide__arrows" data-glide-el="controls">
                    <button class="glide__arrow glide__arrow--left" data-glide-dir="<"></button>
                    <button class="glide__arrow glide__arrow--right" data-glide-dir=">"></button>
                </div>
                <div class="glide__bullets" data-glide-el="controls[nav]">
                    <button class="glide__bullet" data-glide-dir="=0"></button>
                    <button class="glide__bullet" data-glide-dir="=1"></button>
                </div>
            </div>
        </div>

        <games :categories="categoryGames"></games>
    </div>
</template>

<script>
    import Bus from '../../bus';
    import Glide from '@glidejs/glide';
    import { mapGetters } from 'vuex';
    import AuthModal from "../modals/AuthModal";
    import PasswordResetModal from "../modals/PasswordResetModal";
    import TermsModal from "../modals/TermsModal";

    export default {
        computed: {
            ...mapGetters(['games', 'isGuest'])
        },
        data() {
            return {
                categoryGames: {}
            };
        },
        watch: {
            games() {
                this.categoryGames = {};
                this.load();
            }
        },
        methods: {
            openNotice() {
                TermsModal.methods.open('tpNotice');
            },
            openFaucetModal() {
                this.$router.push('/bonus');
            },
            openAuthModal(type) {
                AuthModal.methods.open(type);
            },
            load() {
                let duplicates = [];
                _.forEach(this.games, (game) => {
                    _.forEach(game.category, (category) => {
                        if(category === 'evo-play') return;

                        if (duplicates.includes(game.id)) return;
                        duplicates.push(game.id);

                        if (!this.categoryGames[category]) this.categoryGames[category] = [game];
                        else this.categoryGames[category].push(game);
                    });
                });
            },
            initSlider() {
                try {
                    if(window.$indexGlide) window.$indexGlide.destroy();
                    const glide = new Glide('#slider', {
                        type: 'carousel',
                        perView: 1,
                        focusAt: 'center',
                        gap: 0,
                        autoplay: 4000,
                        keyboard: false
                    });
                    glide.mount();
                    window.$indexGlide = glide;
                } catch (ignored) {}
            }
        },
        created() {
            this.load();

            if(this.$route.params.user && this.$route.params.token)
                PasswordResetModal.methods.open(this.$route.params.user, this.$route.params.token);
        },
        mounted() {
            this.initSlider();

            Bus.$on('layoutSizeChange', () => setTimeout(this.initSlider, 301), true);

            const resize = () => {
                this.initSlider();
                setTimeout(this.initSlider, 301);
            };

            resize();

            $(window).on('resize', this.initSlider);
            $(window).on('load', resize);
            $(document).ready(resize);
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    @import "~@glidejs/glide/dist/css/glide.core.min.css";
    @import "~@glidejs/glide/dist/css/glide.theme.min.css";

    .indexCategories {
        height: 75px;
        margin-bottom: 40px;

        .os-host {
            flex: 1;
            width: 0;

            .os-content {
                display: flex;
            }
        }

        @include themed() {
            display: flex;
            padding-left: 40px;
            padding-right: 40px;
            background: t('sidebar');

            .category {
                cursor: pointer;
                transition: color .3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 25px 20px;
                white-space: nowrap;

                svg, i {
                    margin-right: 10px;
                }

                &:hover {
                    color: t('secondary');
                }

                &.active {
                    color: t('secondary');
                }
            }
        }
    }

    .providers {
        display: flex;
        margin-bottom: 50px !important;

        .os-host {
            flex: 1;
            width: 0;

            .os-content {
                display: flex;
            }
        }

        .provider {
            @include themed() {
                background: t('sidebar');
                border-radius: 6px;
                padding: 10px 40px;
                margin-right: 25px;
                cursor: pointer;
                transition: background .3s ease;

                &:hover {
                    background: lighten(t('sidebar'), 2.5%);
                }

                div {
                    width: 100px;
                    height: 70px;
                    background-size: contain;
                    background-repeat: no-repeat;
                    background-position: center;
                }
            }
        }
    }

    .slider {
        min-height: 300px;
        height: 18vmax;
        max-height: 500px;
        display: flex;
        margin-bottom: 15px;
        margin-top: -15px;
        margin-left: -15px;
        width: calc(100% + 30px) !important;

        #slider {
            width: 0;
            flex: 1;
        }

        .glide {
            height: 100%;

            .glide__track {
                height: 100%;
            }

            .glide__slides {
                height: 100%;
            }

            .glide__arrow--left, .glide__arrow--right {
                padding: 0 !important;
                border: none !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                height: 35px;
                width: 15px;
                opacity: 0.5 !important;
                transition: opacity 0.3s ease;
                &:hover {
                    opacity: 1 !important;
                }
            }

            .glide__arrow--right {
                background: url("data:image/svg+xml;charset=utf-8,%3Csvg width='14' height='37' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M13.738 18.663l-12.448 18L0 34.8l11.176-16.136L0 2.527 1.29.663l12.448 18z' fill='%23fff'/%3E%3C/svg%3E");
            }

            .glide__arrow--left {
                background: url("data:image/svg+xml;charset=utf-8,%3Csvg width='16' height='36' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M.994 18l12.928 18 1.34-1.864L3.655 18 15.262 1.864 13.922 0 .994 18z' fill='%23fff'/%3E%3C/svg%3E");
            }

            .glide__slide {
                width: 100%;
                height: 100% !important;
                position: relative;
                background-size: cover !important;

                @include themed() {
                    background-color: rgba(t('sidebar'), .8);
                    backdrop-filter: blur(20px);
                    border-bottom: 2px solid t('border');
                }

                background-position: center;
                background-repeat: no-repeat;

                .image {
                    background-size: cover;
                    background-repeat: no-repeat;
                    height: 90px;
                    width: 167px;
                    z-index: 1;
                    filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, .2));
                }

                .slideContent {
                    width: 70%;
                    display: flex;
                    margin-left: auto;
                    margin-right: auto;
                    height: 100%;
                    flex-direction: column;
                    color: white;
                    position: relative;
                    z-index: 2;

                    &.right {
                        align-items: flex-end;
                        text-align: right;

                        .slideContentWrapper {
                            align-items: flex-end;
                        }
                    }

                    .slideContentWrapper {
                        display: flex;
                        flex-direction: column;
                        margin-top: auto;
                        margin-bottom: auto;
                    }

                    .header, .description {
                        text-shadow: 1px 1px 3px rgba(black, 0.2);
                    }

                    .header {
                        font-weight: 600;
                        font-size: 2em;
                        margin-bottom: 25px;
                    }

                    .description {
                        font-size: 1.5em;
                        margin-bottom: 25px;
                        width: 320px;
                    }

                    .button {
                        width: 150px;
                        text-align: center;
                        padding: 10px 25px;
                        background: rgba(white, 0.1);
                        border: 1px solid rgba(white, 0.15);
                        cursor: pointer;
                        transition: background 0.3s ease, border-color 0.3s ease;

                        &:hover {
                            background: rgba(white, 0.2);
                            border-color: rgba(white, 0.25);
                        }
                    }
                }
            }
        }
    }

    @include media-breakpoint-up(lg) {
        .slider {
            width: 100%;
        }
    }

    @include media-breakpoint-down(md) {
        .slider {
            margin-left: -15px;
            width: calc(100% + 30px) !important;
            height: 200px !important;
            min-height: 200px !important;

            .image {
                height: 45px !important;
                width: 95px !important;
            }

            .button {
                width: 100px !important;
                font-size: 0.8em !important;
                padding: 5px !important;
            }

            .glide__arrow--left {
                left: 2em !important;
            }

            .glide__slide {
                background-size: contain !important;
            }

            .glide__slide {
                border-radius: 0;
            }

            .slideContentWrapper {
                .header {
                    font-size: 1.6em !important;
                }

                .description {
                    font-size: 1em !important;
                }
            }
        }
    }

    @include media-breakpoint-down(sm) {
        .image {
            width: 100%;
            background-position: center;
        }

        .slideContent .description {
            width: calc(100% - 95px) !important;
            margin-bottom: 15px !important;
        }

        .slideContent .header {
            margin-bottom: 15px !important;
        }

        .glide__bullets, .glide__arrows {
            display: none;
        }
    }

    @media(max-width: 450px) {
        .indexCategories {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }
    }
</style>
