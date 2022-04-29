<template>
    <div>
        <div class="vpCards">
            <div :data-card-id="i - 1" v-for="i in 5" class="vpCard" @click="cardClick(i - 1)">
                <div class="face back"></div>
                <div class="face front">
                    <div class="vpCardValue"></div>
                    <div class="vpCardType"></div>
                </div>
            </div>
        </div>
        <button class="btn btn-primary disabled" data-deal @click="deal">{{ $t('general.deal') }}</button>
    </div>
</template>

<script>
    import gsap from 'gsap';

    const deck = {
        1: {type: 'spades', value: 'A'},
        2: {type: 'spades', value: '2'},
        3: {type: 'spades', value: '3'},
        4: {type: 'spades', value: '4'},
        5: {type: 'spades', value: '5'},
        6: {type: 'spades', value: '6'},
        7: {type: 'spades', value: '7'},
        8: {type: 'spades', value: '8'},
        9: {type: 'spades', value: '9'},
        10: {type: 'spades', value: '10'},
        11: {type: 'spades', value: 'J'},
        12: {type: 'spades', value: 'Q'},
        13: {type: 'spades', value: 'K'},
        14: {type: 'hearts', value: 'A'},
        15: {type: 'hearts', value: '2'},
        16: {type: 'hearts', value: '3'},
        17: {type: 'hearts', value: '4'},
        18: {type: 'hearts', value: '5'},
        19: {type: 'hearts', value: '6'},
        20: {type: 'hearts', value: '7'},
        21: {type: 'hearts', value: '8'},
        22: {type: 'hearts', value: '9'},
        23: {type: 'hearts', value: '10'},
        24: {type: 'hearts', value: 'J'},
        25: {type: 'hearts', value: 'Q'},
        26: {type: 'hearts', value: 'K'},
        27: {type: 'clubs', value: 'A'},
        28: {type: 'clubs', value: '2'},
        29: {type: 'clubs', value: '3'},
        30: {type: 'clubs', value: '4'},
        31: {type: 'clubs', value: '5'},
        32: {type: 'clubs', value: '6'},
        33: {type: 'clubs', value: '7'},
        34: {type: 'clubs', value: '8'},
        35: {type: 'clubs', value: '9'},
        36: {type: 'clubs', value: '10'},
        37: {type: 'clubs', value: 'J'},
        38: {type: 'clubs', value: 'Q'},
        39: {type: 'clubs', value: 'K'},
        40: {type: 'diamonds', value: 'A'},
        41: {type: 'diamonds', value: '2'},
        42: {type: 'diamonds', value: '3'},
        43: {type: 'diamonds', value: '4'},
        44: {type: 'diamonds', value: '5'},
        45: {type: 'diamonds', value: '6'},
        46: {type: 'diamonds', value: '7'},
        47: {type: 'diamonds', value: '8'},
        48: {type: 'diamonds', value: '9'},
        49: {type: 'diamonds', value: '10'},
        50: {type: 'diamonds', value: 'J'},
        51: {type: 'diamonds', value: 'Q'},
        52: {type: 'diamonds', value: 'K'},
        toIcon: function(card) {
            let icons = {
                'spades': 'fas fa-spade',
                'hearts': 'fas fa-heart',
                'clubs': 'fas fa-club',
                'diamonds': 'fas fa-diamond'
            };
            return icons[card.type];
        },
        toString: function(card) {
            return `<span class="${card.type === 'hearts' || card.type === 'diamonds' ? 'text-danger' : ''}">${card.value} <i class="${deck.toIcon(card)}"></i></span>`;
        }
    };

    export default {
        data() {
            return {
                flipState: {
                    0: false,
                    1: false,
                    2: false,
                    3: false,
                    4: false
                }
            }
        },
		mounted() {
setTimeout(() => {
Bus.$emit('videopoker:history:addEntry', { html: `<div>Royal Flush x800<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Straight Flush x800<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Four of a kind x22<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Full House x9<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Flush x6<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Straight x4<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Three of a kind x3<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Two pair x2<div>`, type: 'append' });
Bus.$emit('videopoker:history:addEntry', { html: `<div>Pair (J, Q, K, A) x1<div>`, type: 'append' });
}, 1000);
		},
        methods: {
            deal() {
                if($('[data-deal]').hasClass('disabled')) return;
                let hold = [];
                _.forEach($('.vpCard.selected'), (e) => hold.push(parseInt($(e).attr('data-card-id'))));

                this.updateGameInstance((i) => i.playTimeout = true);
                $('.game-content-videopoker button').toggleClass('disabled', true);
                $('.vpCard.active').removeClass('active');

                this.sendTurn({ hold: hold.length === 0 ? [-1, -1, -1, -1, -1, -1] : hold }, (response) => {
                    for(let i = 0; i < 5; i++) {
                        if(hold.includes(i)) continue;
                        const card = deck[response.data.deck[i] + 1];
                        this.flipCard(i);
                        setTimeout(() => {
                            $(`[data-card-id="${i}"] .vpCardValue`).attr('class', `vpCardValue ${card.type === 'hearts' || card.type === 'diamonds' ? 'text-danger' : ''}`).html(card.value);
                            $(`[data-card-id="${i}"] .vpCardType`).html(`<i class="${deck.toIcon(card)} ${card.type === 'hearts' || card.type === 'diamonds' ? 'text-danger' : ''}"></i>`);
                            $(`[data-card-id="${i}"]`).addClass('active');
                            this.flipCard(i);
                        }, hold.length === 5 ? 0 : 1000);
                    }
                    setTimeout(() => {
                        this.finishExtended(false);
                        this.updateGameInstance((i) => i.playTimeout = false);
                        this.resultPopup(response.game);
                    }, hold.length === 5 ? 0 : 2000);
                });
            },
            cardClick(i) {
                const e = $(`[data-card-id="${i}"]`);
                if(e.hasClass('active')) e.toggleClass('selected');
            },
            flipCard(id, saveState = true) {
                gsap.to($('.game-videopoker').find(`[data-card-id="${id}"]`), {duration: 1, css: {rotationY:`+=180`}, ease: 'power2.easeInOut'});
                if(saveState) this.flipState[id] = !this.flipState[id];
            },
            reset() {
                $(`[data-card-id]`).removeClass('active').removeClass('selected');
            },
            restore(game) {
                for(let i = 0; i < 5; i++) {
                    const card = deck[game.history[0].deck[i] + 1];
                    $('.game-videopoker').find(`[data-card-id="${i}"] .vpCardValue`).attr('class', `vpCardValue ${card.type === 'hearts' || card.type === 'diamonds' ? 'text-danger' : ''}`).html(card.value);
                    $(`[data-card-id="${i}"] .vpCardType`).html(`<i class="${deck.toIcon(card)} ${card.type === 'hearts' || card.type === 'diamonds' ? 'text-danger' : ''}"></i>`);
                    $(`[data-card-id="${i}"]`).addClass('active');
                    this.flipCard(i);
                }
                $('.game-content-videopoker button').removeClass('disabled');
            },
            callback(response) {
                this.reset();
                if(this.isExtendedGameStarted()) {
                    let timeout = false;
                    for(let i = 0; i < 5; i++) if(this.flipState[i]) {
                        timeout = true;
                        this.flipCard(i);
                    }

                    setTimeout(() => {
                        this.updateGameInstance((i) => i.playTimeout = false);

                        this.sendTurn({}, (response) => {
                            for (let i = 0; i < 5; i++) {
                                const card = deck[response.data.deck[i] + 1];
                                $(`[data-card-id="${i}"] .vpCardValue`).attr('class', `vpCardValue ${card.type === 'hearts' || card.type === 'diamonds' ? 'text-danger' : ''}`).html(card.value);
                                $(`[data-card-id="${i}"] .vpCardType`).html(`<i class="${deck.toIcon(card)} ${card.type === 'hearts' || card.type === 'diamonds' ? 'text-danger' : ''}"></i>`);
                                $(`[data-card-id="${i}"]`).addClass('active');
                                this.flipCard(i);
                            }

                            setTimeout(() => {
                                if(!this.isExtendedGameStarted()) return;
                                $('.game-content-videopoker button').toggleClass('disabled', false);
                                this.updateGameInstance((i) => i.playTimeout = false);
                            }, 1000);
                        });
                    }, timeout ? 1000 : 0);
                } else {
                    $('[data-card-id]').removeClass('active');
                    $('.game-content-videopoker button').toggleClass('disabled', true);
                    if(response) this.resultPopup(response.game);
                }
            },
            getClientData() {
                return {}
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } },
                    { name: 'history', data: { scrollable: false } }
                ];
            }
        }
    }
</script>

<style lang="scss">
    @import 'resources/sass/variables';

    .game-videopoker {
        $history-height: 76px;

        .game-content-videopoker {
            display: flex;
            align-content: center;
            justify-content: center;
            flex-direction: column;

            button {
                width: 200px;
                left: 50%;
                position: relative;
                transform: translateX(-50%);
            }

            .history-videopoker {
                display: flex;
                flex-direction: column;

                &:last-child {
                    margin-right: 15px !important;
                }

                @include themed() {
                    div {
                        text-align: center;
                    }

                    div:first-child {
                        text-transform: uppercase;
                        color: rgba(t('text'), 0.55);
                    }

                    div:last-child {
                        color: rgba(t('text'), 0.4);
                    }
                }
            }
        }

        .vpCards {
            display: flex;
            flex-direction: row;
            align-content: center;
            justify-content: center;

            .vpCard.active {
                &:hover {
                    top: -15px;
                    cursor: pointer;
                    * {
                        cursor: pointer;
                    }
                }
            }

            .vpCard.selected {
                @include themed() {
                    box-shadow: 0 0 1px 3px t('secondary');
                }

                &:after {
                    content: 'HOLD';
                    position: absolute;
                    bottom: 0;
                    left: 50%;
                    transform: translate(-50%) rotateY(180deg);
                    padding: 1px 8px;
                    font-size: 0.7em;
                    border-top-left-radius: 3px;
                    border-top-right-radius: 3px;
                    @include themed() {
                        box-shadow: 0 0 1px 3px t('secondary');
                        background: t('secondary');
                    }
                    color: white;
                }
            }

            .vpCard {
                display: inline-flex;

                top: calc(50% - #{$history-height});
                transform: translateY(-50%);

                position: relative;
                transform-style: preserve-3d;
                width: 85px;
                height: 140px;
                margin-right: 75px;
                @include themed() {
                    box-shadow: 0 0 1px 3px t('text');
                }
                border-radius: 3px;
                transition: top 0.3s ease, box-shadow 0.3s ease;

                &:last-child {
                    margin-right: 0;
                }

                .face {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    backface-visibility: hidden;
                }

                .face.back {
                    background: url('/img/misc/cardback.svg');
                    background-size: cover;
                }

                .face.front {
                    transform: rotateY(180deg);
                    display: flex;
                    flex-direction: column;
                    padding: 10px;

                    background: white;
                    color: black;

                    .vpCardValue {
                        font-size: 2em;
                    }

                    .vpCardType {
                        font-size: 1.5em;
                    }

                    .vpCardValue, .vpCardType {
                        user-select: none;
                        -webkit-user-drag: none;
                        cursor: default;
                    }
                }
            }
        }

        .overview-render-target {
            .vpCards {
                padding-top: 55px !important;
                .vpCard {
                    margin-right: 10px !important;
                    width: 35px !important;
                    height: 55px !important;
                    border-radius: 1px !important;
                    @include themed() {
                        box-shadow: 0 0 1px 2px t('text');
                    }

                    .face.front {
                        padding: 5px !important;

                        .vpCardValue {
                            font-size: 1em !important;
                        }

                        .vpCardType {
                            font-size: 0.5em !important;
                        }
                    }
                }
            }
        }
    }

    @media(max-width: 1500px) {
        .vpCard {
            margin-right: 14px !important;
            width: 60px !important;
            height: 100px !important;
            border-radius: 2px !important;
            @include themed() {
                box-shadow: 0 0 1px 2px t('text');
            }

            .face.front {
                padding: 10px !important;

                .vpCardValue {
                    font-size: 1.5em !important;
                }

                .vpCardType {
                    font-size: 1em !important;
                }
            }
        }
    }

    @media(max-width: 991px) {
        .game-content-videopoker {
            padding-top: 170px !important;

            .vpCards {
                margin-top: -90px;
                margin-bottom: -30px;

                .vpCard {
                    top: calc(50% - 120px);
                }

                .vpCard.active {
                    &:hover {
                        top: calc(50% - 120px) !important;
                    }
                }
            }

            button {
                position: relative;
                top: 0;
            }
        }
    }

    @media(max-width: 480px) {
        .game-content-videopoker {
            padding-top: 195px !important;

            .vpCards {
                margin-top: -120px !important;
                margin-bottom: -20px !important;
            }

            .vpCard {
                margin-right: 10px !important;
                width: 35px !important;
                height: 55px !important;
                border-radius: 1px !important;
                @include themed() {
                    box-shadow: 0 0 1px 2px t('text');
                }

                .face.front {
                    padding: 5px !important;

                    .vpCardValue {
                        font-size: 1em !important;
                    }

                    .vpCardType {
                        font-size: 0.5em !important;
                    }
                }
            }

            button {
                top: 30px !important;
            }
        }
    }
</style>