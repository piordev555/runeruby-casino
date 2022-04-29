<template>
    <div>
        <template v-for="(category, key) in categories">
            <div class="index_cat">
                <i class="fab fa-gripfire"></i> {{ $t('general.sidebar.' + key) }}
            </div>

            <div class="games">
                <div v-for="game in category" :key="game.id" :class="`game_poster game-${game.id} game_type-local`">
                    <div :class="`game_poster-image game-${game.id}-image`" @click="game.isDisabled ? '' : $router.push(`/casino/game/${game.id}`)">
                        <div class="unavailable" v-if="game.isDisabled">
                            <div class="slanting">
                                <div class="content">
                                    {{ $t(game.isPlaceholder ? 'general.coming_soon' : 'general.not_available') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="game_poster-houseEdge" v-if="game.houseEdge" @click="game.isDisabled ? '' : $router.push(`/casino/game/${game.id}`)">
                        {{ game.houseEdge }}% House Edge
                    </div>
                    <div class="game_poster-name" @click="game.isDisabled ? '' : $router.push(`/casino/game/${game.id}`)">
                        {{ game.name }}
                    </div>
                    <div class="game_poster-footer" @click="toggleFavoriteGame(game.id)" v-if="!isGuest">
                        <template v-if="!favMarkLoading">
                            <i :class="`fa${!user.user.favoriteGames || !user.user.favoriteGames.includes(game.id) ? 'l' : 's'} fa-star`"></i>
                            {{ $t(!user.user.favoriteGames || !user.user.favoriteGames.includes(game.id) ? 'general.sidebar.mark_as_favorite' : 'general.sidebar.remove_from_favorite') }}
                        </template>
                        <loader v-else></loader>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>

<script type="text/javascript">
    import { mapGetters } from 'vuex';

    export default {
        props: ['categories'],
        computed: {
            ...mapGetters(['user', 'isGuest'])
        },
        data() {
            return {
                favMarkLoading: false
            }
        },
        methods: {
            toggleFavoriteGame(id) {
                if(this.favMarkLoading) return;
                this.favMarkLoading = true;
                axios.post('/api/user/markGameAsFavorite', { id: id }).then(() => {
                    this.$store.dispatch('update');
                    this.favMarkLoading = false;
                }).catch(() => this.favMarkLoading = false);
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .index_cat {
        font-size: 1.3em;
        font-weight: 600;
        margin-bottom: 25px;
        margin-top: 25px;
        padding-left: 40px;
        display: flex;
        flex-direction: row;
        align-items: center;
        cursor: pointer;

        i, svg {
            margin-right: 10px;
            width: 30px;
            text-align: center;
            max-height: 30px;
        }
    }

    .game_poster, .game_placeholder {
        display: inline-flex;
        flex-direction: column;
        width: 250px;
        min-width: 220px;
        position: relative;

        @include themed() {
            background: rgba(t('sidebar'), .8);
            backdrop-filter: blur(20px);
        }

        &.game_type-local {
            width: 18%;

            &-image {
                height: 25vh;
            }
        }

        &-image {
            width: 100%;
            height: 11vw;
            position: relative;
            z-index: 5;
            background-image: url('/img/misc/unknown-game-image.jpg');
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            transform: scale(1);
            transition: all 0.4s ease;

            .game_tp-image {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                z-index: 1;
            }

            &:before, &:after {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-position: center;
                background-size: cover;
                background-repeat: no-repeat;

                transform: scale(1);
                transition: all 0.25s ease;
            }

            @include themed() {
                &:before {
                    z-index: 2;
                }
            }

            &:after {
                z-index: 1;
            }
        }

        &-houseEdge {
            font-size: 0.8em;
            position: absolute;
            bottom: 55px;
            right: 0;

            @include themed() {
                background: linear-gradient(to right, transparent 0%, t('sidebar') 170%);
            }

            padding: 10px 25px;
            padding-right: 15px;
        }

        &-name {
            font-size: 1.1em;
            padding: 15px 20px;
        }

        &-footer {
            position: absolute;
            top: 0;
            left: 0;
            height: 45px;
            z-index: 7;
            padding: 10px 20px;
            background: rgba(black, .2);
            width: 100%;
            opacity: 0;
            transition: opacity .3s ease;
            transition-delay: .3s;
            font-size: 0.9em;
            display: flex;
            align-items: center;

            .loaderContainer {
                transform: scale(.2) translate(-200%);
            }

            i {
                margin-right: 5px;
            }
        }

        @include only_safari('.game_poster-name', (
            font-weight: unset
        ));

        &-name, &-houseEdge {
            z-index: 6;
        }

        .vue-content-placeholders-img {
            height: 100% !important;
        }

        margin: 7px;
        z-index: 1;
        transition: all 0.3s ease;

        &:hover {
            transform: scale(1.1);
            z-index: 5;

            .game_poster-footer {
                opacity: 1;
            }
        }

        cursor: pointer;
        border-radius: 5px;
    }

    .game_poster {
        .unavailable {
            z-index: 4;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(black, 0.4);
            color: white;

            .slanting {
                transform: skewY(-5deg) translateY(-50%);
                padding: 25px;
                position: absolute;
                top: 50%;
                background: rgba(black, 0.85);
                width: 100%;

                .content {
                    font-size: 15px;
                    transform: skewY(5deg);
                    text-align: center;
                }
            }
        }
    }

    .game-bullvsbear {
        &-image {
            &:after {
                background-image: url('/img/game/bullvsbear.svg');
                background-color: #3178f4;
            }
        }
    }

    .game-limbo {
        &-image {
            &:after {
                background-image: url('/img/game/limbo.svg');
            }

            &:before {
                background-image: url('/img/game/limbo_overlay.svg');
            }
        }

        &:hover &-image {
            &:before {
                transform: scale(1.1);
            }
        }
    }

    .game-mines {
        &-image {
            &:after {
                background-image: url('/img/game/mines.svg');
            }

            &:before {
                background-image: url('/img/game/mines_overlay.svg');
            }
        }
    }

    .game-tower {
        &-image {
            &:after {
                background-image: url('/img/game/tower.svg');
            }

            &:before {
                background-image: url('/img/game/tower_overlay.svg');
            }
        }
    }

    .game-plinko {
        &-image {
            &:after {
                background-image: url('/img/game/plinko.svg');
            }

            &:before {
                background-image: url('/img/game/plinko_overlay.svg');
            }
        }
    }

    .game-dice {
        &-image {
            &:after {
                background-image: url('/img/game/dice.svg');
            }

            &:before {
                background-image: url('/img/game/dice_overlay.svg');
            }
        }
    }

    .game-stairs {
        &-image {
            &:after {
                background-image: url('/img/game/stairs.svg');
            }

            &:before {
                background-image: url('/img/game/stairs_overlay.svg');
            }
        }
    }

    .game-keno {
        &-image {
            &:after {
                background-image: url('/img/game/keno.svg');
            }

            &:before {
                background-image: url('/img/game/keno_overlay.svg');
            }
        }
    }

    .game-baccarat {
        &-image {
            &:after {
                background-image: url('/img/game/baccarat.png');
            }

            &:before {

            }
        }
    }

    .game-crash {
        &-image {
            &:after {
                background-image: url('/img/game/crash.svg');
            }
        }
    }

    .game-videopoker {
        &-image {
            &:after {
                background-image: url('/img/game/videopoker.svg');
            }

            &:before {
                background-image: url('/img/game/videopoker_overlay.svg');
            }
        }

        &:hover &-image {
            &:before {
                transform: translateY(-2%) rotate(-2deg) scale(1.05);
            }
        }
    }

    .game-slide {
        &-image {
            &:after {
                background-image: url('/img/game/slide.svg');
            }
        }
    }

    .game-slots {
        &-image {
            &:after {
                background-image: url('/img/game/slots.svg');
            }
        }
    }

    .game-wheel {
        &-image {
            &:after {
                background-image: url('/img/game/wheel.svg');
            }
        }
    }

    .game-hilo {
        &-image {
            &:after {
                background-image: url('/img/game/hilo.svg');
            }
        }
    }

    .game-blackjack {
        &-image {
            &:after {
                background-image: url('/img/game/blackjack.svg');
            }

            &:before {
                background-image: url('/img/game/blackjack_overlay.svg');
                opacity: 0.8;
            }
        }
    }

    .game-roulette {
        &-image {
            &:after {
                background-image: url('/img/game/roulette.svg');
            }
        }
    }

    .game-coinflip {
        &-image {
            &:after {
                background-image: url('/img/game/coinflip.png');
            }
        }
    }

    .game-diamonds {
        &-image {
            &:after {
                background-image: url('/img/game/diamonds.svg');
            }

            &:before {
                background-image: url('/img/game/diamonds_overlay.svg');
            }
        }
    }

    .games {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }

    @include media-breakpoint-down(md) {
        .game_poster, .game_placeholder {
            &-image {
                height: 200px !important;
            }
        }
    }

    @include media-breakpoint-down(sm) {
        .game_poster, .game_placeholder {
            width: calc(50% - 15px) !important;
            min-width: unset !important;

            &-image {
                height: 170px !important;
                min-height: unset !important;
            }
        }

        .image {
            width: 100%;
            background-position: center;
        }

        .slideContent .description {
            width: calc(100% - 15px) !important;
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
        .index_cat {
            padding-left: 0 !important;
        }

        .game_poster, .game_placeholder {
            height: 170px !important;
            width: calc(50vw - 30px) !important;
            background-position-y: center !important;
        }
    }

    @media(max-width: 991px) {
        .index_cat {
            padding-left: 10px;
            margin-bottom: 15px;
            margin-top: 25px;
        }
    }

    @media(max-width: 450px) {
        .index_cat {
            padding-left: 0 !important;
        }
    }
</style>
