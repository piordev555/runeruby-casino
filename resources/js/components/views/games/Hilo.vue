<template>
    <div class="hilo_card">
        <div class="hilo-card-value"></div>
        <i id="card_icon"></i>
        <div class="hilo-replace" @click="replace">
            <i class="far fa-redo"></i>
        </div>
    </div>
</template>

<script>
    import Bus from '../../../bus';

    const deck = {
        1: {type: 'spades', value: 'A', slot: 1},
        2: {type: 'spades', value: '2', slot: 2},
        3: {type: 'spades', value: '3', slot: 3},
        4: {type: 'spades', value: '4', slot: 4},
        5: {type: 'spades', value: '5', slot: 5},
        6: {type: 'spades', value: '6', slot: 6},
        7: {type: 'spades', value: '7', slot: 7},
        8: {type: 'spades', value: '8', slot: 8},
        9: {type: 'spades', value: '9', slot: 9},
        10: {type: 'spades', value: '10', slot: 10},
        11: {type: 'spades', value: 'J', slot: 11},
        12: {type: 'spades', value: 'Q', slot: 12},
        13: {type: 'spades', value: 'K', slot: 13},
        14: {type: 'hearts', value: 'A', slot: 1},
        15: {type: 'hearts', value: '2', slot: 2},
        16: {type: 'hearts', value: '3', slot: 3},
        17: {type: 'hearts', value: '4', slot: 4},
        18: {type: 'hearts', value: '5', slot: 5},
        19: {type: 'hearts', value: '6', slot: 6},
        20: {type: 'hearts', value: '7', slot: 7},
        21: {type: 'hearts', value: '8', slot: 8},
        22: {type: 'hearts', value: '9', slot: 9},
        23: {type: 'hearts', value: '10', slot: 10},
        24: {type: 'hearts', value: 'J', slot: 11},
        25: {type: 'hearts', value: 'Q', slot: 12},
        26: {type: 'hearts', value: 'K', slot: 13},
        27: {type: 'clubs', value: 'A', slot: 1},
        28: {type: 'clubs', value: '2', slot: 2},
        29: {type: 'clubs', value: '3', slot: 3},
        30: {type: 'clubs', value: '4', slot: 4},
        31: {type: 'clubs', value: '5', slot: 5},
        32: {type: 'clubs', value: '6', slot: 6},
        33: {type: 'clubs', value: '7', slot: 7},
        34: {type: 'clubs', value: '8', slot: 8},
        35: {type: 'clubs', value: '9', slot: 9},
        36: {type: 'clubs', value: '10', slot: 10},
        37: {type: 'clubs', value: 'J', slot: 11},
        38: {type: 'clubs', value: 'Q', slot: 12},
        39: {type: 'clubs', value: 'K', slot: 13},
        40: {type: 'diamonds', value: 'A', slot: 1},
        41: {type: 'diamonds', value: '2', slot: 2},
        42: {type: 'diamonds', value: '3', slot: 3},
        43: {type: 'diamonds', value: '4', slot: 4},
        44: {type: 'diamonds', value: '5', slot: 5},
        45: {type: 'diamonds', value: '6', slot: 6},
        46: {type: 'diamonds', value: '7', slot: 7},
        47: {type: 'diamonds', value: '8', slot: 8},
        48: {type: 'diamonds', value: '9', slot: 9},
        49: {type: 'diamonds', value: '10', slot: 10},
        50: {type: 'diamonds', value: 'J', slot: 11},
        51: {type: 'diamonds', value: 'Q', slot: 12},
        52: {type: 'diamonds', value: 'K', slot: 13},
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
            return card.value + ' <i class="' + deck.toIcon(card) + '"></i>';
        }
    };

    export default {
        data() {
            return {
                startingCardIndex: null,
                turnId: 0
            }
        },
        mounted() {
            this.replace();
            this.recalculateMultipliers();
        },
        methods: {
            turn(type) {
                this.turnId++;
                if(this.turnId >= 3) $('.hilo-skip .game-sidebar-buttons-container-button').toggleClass('disabled', true);

                this.sendTurn({ type: type }, (response) => {
                    if(response.type === 'fail') return;

                    this.setCard(deck[response.data.current]);

                    if(response.type === 'lose') {
                        this.finishExtended(false);
                        this.playSound('/sounds/lose.mp3');
                        this.resultPopup(response.game);
                    } else this.playSound('/sounds/card_slide.mp3');
                });
            },
            setCard(card) {
                if(card === undefined) {
                    console.error('Tried to set undefined card');
                    card = deck[1];
                }

                this.startingCardIndex = _.findKey(deck, card);

                $('.hilo-card-value').fadeOut('fast', function() {
                    $(this).html(card.value);
                    $(this).fadeIn('fast');
                });

                $('#card_icon').fadeOut('fast', function () {
                    $('#card_icon').attr('class', deck.toIcon(card));
                    $('#card_icon').fadeIn('fast');
                    setTimeout(function() {
                        $('#card_icon').css({opacity: 1});
                    }, 200);

                    let isRed = card.type === 'hearts' || card.type === 'diamonds';
                    $('.hilo_card').toggleClass('card_history_red', isRed);
                    $('.hilo_card').toggleClass('card_history_black', !isRed);
                });

                this.addToHistory(card);
            },
            recalculateMultipliers() {
                let calculateMultiplier = (type) => {
                    if(type === 'higher') return parseFloat(this.applyHouseEdge(12.350 / (13 - (deck[this.startingCardIndex].slot - 1))));
                    if(type === 'lower') return parseFloat(this.applyHouseEdge(12.350 / (deck[this.startingCardIndex].slot)));
                    if(type === 'same') return parseFloat(this.applyHouseEdge(16.83));
                };

                let sameProbability = 5.88,
                    lowerProbability = (deck[this.startingCardIndex].slot / 13) * 100 - (sameProbability),
                    higherProbability = 100 - lowerProbability - (sameProbability);

                const noHigher = (this.startingCardIndex % 13) + 1 === 1, noLower = (this.startingCardIndex % 13) + 1 === 2;

                if(noHigher) lowerProbability = 100 - sameProbability;
                if(noLower) higherProbability = 100 - sameProbability;

                $('.hilo-higher').removeClass('disabled').fadeOut('fast', function () {
                    if(noHigher) $(this).addClass('disabled');
                    $('.hilo-higher .multiplier').html((noHigher ? 0 : calculateMultiplier('higher')).toFixed(2)+'x');
                    $('.hilo-higher .chance').html((noHigher ? 0 : higherProbability).toFixed(2)+'%');
                    $('.hilo-higher').fadeIn('fast');
                });

                $('.hilo-same').removeClass('disabled').fadeOut('fast', function() {
                    $('.hilo-same .multiplier').html(calculateMultiplier('same').toFixed(2)+'x');
                    $('.hilo-same .chance').html(sameProbability.toFixed(2)+'%');
                    $('.hilo-same').fadeIn('fast');
                });

                $('.hilo-lower').removeClass('disabled').fadeOut('fast', function () {
                    if(noLower) $(this).addClass('disabled');
                    $('.hilo-lower .multiplier').html((noLower ? 0 : calculateMultiplier('lower')).toFixed(2)+'x');
                    $('.hilo-lower .chance').html((noLower ? 0 : lowerProbability).toFixed(2)+'%');
                    $('.hilo-lower').fadeIn('fast');
                });
            },
            addToHistory(card) {
                Bus.$emit('hilo:history:addEntry', { html: `<div class="card_history ${card.type === 'hearts' || card.type === 'diamonds' ? 'card_history_red' : 'card_history_black'}">
                        <div>${card.value}</div>
                        <i class="${deck.toIcon(card)}"></i>
                    </div>` });

                this.recalculateMultipliers();
            },
            clearHistory() {
                $.each($('.card_history'), (i, e) => $(e).fadeOut('fast', () => $(e).remove()));
            },
            replace() {
                if(this.isExtendedGameStarted()) return;
                this.clearHistory();

                const next = () => {
                    const number = this.random(1, Object.keys(deck).length);
                    const card = deck[number];
                    if(card === undefined || card.slot === 1 || card.slot === 13) {
                        next();
                        return;
                    }

                    this.startingCardIndex = number;
                    this.setCard(card);
                    this.recalculateMultipliers();
                };

                next();

                this.playSound('/sounds/card_slide.mp3');
            },
            callback(response) {
                if(this.isExtendedGameStarted()) {
                    this.clearHistory();
                    $('.hilo-replace').fadeOut('fast');
                    $('.hilo-turn, .hilo-skip').fadeIn('fast');
                    this.turnId = 0;
                    $('.hilo-skip .game-sidebar-buttons-container-button').removeClass('disabled');

                    this.recalculateMultipliers();
                    $('.hilo-buttons').show();
                } else {
                    $('.hilo-buttons').fadeOut('fast');
                    $('.hilo-replace').fadeIn('fast');
                    $('.hilo-turn, .hilo-skip').fadeOut('fast');
                    if(response) this.resultPopup(response.game);
                }
            },
            restore(game) {
                $('.hilo-replace').hide();
                $('.hilo-turn, .hilo-skip').show();

                if(game.history.length >= 3) $('.hilo-skip .game-sidebar-buttons-container-button').addClass('disabled');

                this.setCard(deck[game.history[game.history.length - 1]]);
                this.clearHistory();

                _.forEach(game.history, (index) => this.addToHistory(deck[index]));

                $('.hilo-buttons').show();
                this.recalculateMultipliers();
            },
            getClientData() {
                return {
                    starting: this.startingCardIndex
                };
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'buttons', data: { buttons: [
                        { label: `<div class="label">${this.$i18n.t('general.hilo-higher')}</div><div class="multiplier">0.00x</div><div class="chance">0.00%</div>`, isAction: true, preventMark: true, class: 'hilo-higher', callback: () => this.turn('higher') }
                    ], classList: 'hilo-buttons' } },
                    { name: 'buttons', data: { buttons: [
                        { label: `<div class="label">${this.$i18n.t('general.hilo-same')}</div><div class="multiplier">0.00x</div><div class="chance">0.00%</div>`, isAction: true, preventMark: true, class: 'hilo-same', callback: () => this.turn('same') }
                    ], classList: 'hilo-buttons' } },
                    { name: 'buttons', data: { buttons: [
                        { label: `<div class="label">${this.$i18n.t('general.hilo-lower')}</div><div class="multiplier">0.00x</div><div class="chance">0.00%</div>`, isAction: true, preventMark: true, class: 'hilo-lower', callback: () => this.turn('lower') }
                    ], classList: 'hilo-buttons' } },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } },
                    { name: 'history', data: { scrollable: false } }
                ]
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-sidebar .game-sidebar-buttons-container {
        @include themed() {
            &.hilo-buttons {
                display: none;

                div {
                    color: t('text') !important;
                }
            }
        }
    }

    .game-hilo {
        .game-content-hilo {
            display: flex;
            align-items: center;
            justify-content: center;

            .game-history {
                height: 140px;
            }
        }

        .hilo-lower, .hilo-same, .hilo-higher {
            margin-top: 5px;
            color: white;
            font-weight: 600;
            position: relative;
            display: flex;
            flex-direction: column;
            text-align: left !important;
            padding-left: 10px;
            align-items: unset !important;
            justify-content: unset !important;

            .label {
                font-size: 1.0em;
            }

            .multiplier {
                font-size: 0.85em;
                font-weight: 300 !important;
            }

            .chance {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                text-align: center;
                .chanceLabel {
                    font-weight: 300 !important;
                }
            }
        }

        .hilo-lower {
            background: #d33333 !important;
        }

        .hilo-same {
            background: #555963 !important;
        }

        .hilo-higher {
            background: #3bc248 !important;
        }

        .hilo_card {
            background: white;
            width: 200px;
            height: 330px;
            border-radius: 5px;
            padding: 25px;
            user-select: none;
            cursor: default;
            transition: color 0.3s ease;
            position: relative;
            box-shadow: 0 0 1px 1px rgba(black, 0.4);
            margin-bottom: 140px;

            #card_icon {
                font-size: 2.5em;
                position: relative;
                bottom: 20px;
            }
        }

        .hilo-card-value {
            font-size: 4em;
            position: relative;
            bottom: 20px;
        }

        .hilo-replace {
            position: absolute;
            top: -19px;
            right: -17px;
            @include themed() {
                background: t('draggableWindowHeader');
                color: t('textInverted');
            }
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
        }

        .card_history {
            display: inline-block;
            width: 80px;
            height: 110px;
            padding: 15px;
            position: relative;
            bottom: 0;
            user-select: none;
            margin-right: 5px;
            background: white;

            border-top-left-radius: 3px;
            border-top-right-radius: 3px;
            box-shadow: 0 0 1px 1px rgba(black, 0.4);

            div {
                font-size: 1.4em;
                position: relative;
                bottom: 8px;
            }

            svg {
                position: relative;
                bottom: 10px;
            }
        }

        .overview-render-target {
            .card_history {
                width: 40px;
                height: 60px;
                padding: 6px;
                margin-top: 5px;

                div {
                    font-size: 0.8em;
                    bottom: 3px;
                }

                svg {
                    font-size: 0.6em;
                    bottom: 12px;
                }
            }
        }

        .card_history_black {
            color: #2b2f3b;
        }

        .card_history_red {
            color: #e86376;
        }

        @include media-breakpoint-down(sm) {
            .game-content-hilo {
                .game-history {
                    height: 90px;

                    .history-hilo {
                        margin-right: 10px;
                    }
                }
            }

            .hilo_card {
                width: 120px;
                height: 180px;
                padding: 15px;
                margin-bottom: 90px;

                #card_icon {
                    font-size: 1.4em;
                    position: relative;
                    bottom: 13px;
                }

            }

            .hilo-card-value {
                font-size: 2.3em;
                bottom: 10px;
            }

            .hilo-replace {
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.6em;
                padding: 10px;
            }

            .card_history {
                width: 40px;
                height: 60px;
                padding: 6px;

                div {
                    font-size: 0.8em;
                    bottom: 3px;
                }

                svg {
                    bottom: 12px;
                    font-size: 0.6em;
                }
            }

        }
    }
</style>
