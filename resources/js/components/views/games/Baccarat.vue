<template>
    <div>
        <div class="baccarat-game-field">
            <i class="fas fa-baccarat-ribbon"></i>
            <div class="deck">
                <div><div></div></div>
                <div><div></div></div>
                <div><div></div></div>
                <div><div></div></div>
            </div>
            <div class="baccaratCardsPlayer">
                <div class="score" style="display: none"></div>
            </div>
            <div class="baccaratCardsBanker">
                <div class="score" style="display: none"></div>
            </div>
            <div class="timer"></div>
        </div>
        <div class="baccarat-field">
            <div class="header">
                {{ $t('general.wager') }}: <span id="bet">{{ rawBitcoin(currency, 0) }}</span> <icon :icon="currencies[currency].icon" :style="{ color: currencies[currency].style }"></icon>
                <div class="right">
                    <button class="btn btn-primary" @click="clear">{{ $t('general.clear') }} <i class="fal fa-times"></i></button>
                </div>
            </div>
            <div class="bets">
                <div class="content">
                    <div class="cell" data-chip="player">
                        <div class="title">{{ $t('general.baccarat.player') }}</div>
                        <div class="payout">x2.00</div>
                    </div>
                </div>
                <div class="content rows">
                    <div class="content">
                        <div class="cell" data-chip="draw">
                            <div class="title">{{ $t('general.baccarat.tie') }}</div>
                            <div class="payout">x8.00</div>
                        </div>
                    </div>
                    <div class="content" style="margin-top: 1px">
                        <div class="cell" data-chip="pair_player">
                            <div class="title">{{ $t('general.baccarat.pair_player') }}</div>
                            <div class="payout">x11.00</div>
                        </div>
                        <div class="cell" data-chip="pair_banker">
                            <div class="title">{{ $t('general.baccarat.pair_banker') }}</div>
                            <div class="payout">x11.00</div>
                        </div>
                    </div>
                </div>
                <div class="content">
                    <div class="cell" style="margin-right: 0" data-chip="banker">
                        <div class="title">{{ $t('general.baccarat.banker') }}</div>
                        <div class="payout">x1.95</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="baccarat-players">
            <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }" class="baccarat-players-scrollable os-host-flexbox">
                <div class="baccarat-players-container player">
                    <div class="header">{{ $t('general.baccarat.pair') }}</div>
                    <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }" class="users" data-container="pair_player">
                        <div class="empty">{{ $t('general.baccarat.empty') }}</div>
                    </overlay-scrollbars>
                </div>
                <div class="baccarat-players-container player">
                    <div class="header">{{ $t('general.baccarat.player') }}</div>
                    <overlay-scrollbars class="users" data-container="player" :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                        <div class="empty">{{ $t('general.baccarat.empty') }}</div>
                    </overlay-scrollbars>
                </div>
                <div class="baccarat-players-container tie">
                    <div class="header">{{ $t('general.baccarat.tie') }}</div>
                    <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }" class="users" data-container="draw">
                        <div class="empty">{{ $t('general.baccarat.empty') }}</div>
                    </overlay-scrollbars>
                </div>
                <div class="baccarat-players-container banker">
                    <div class="header">{{ $t('general.baccarat.banker') }}</div>
                    <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }" class="users" data-container="pair_banker">
                        <div class="empty">{{ $t('general.baccarat.empty') }}</div>
                    </overlay-scrollbars>
                </div>
                <div class="baccarat-players-container banker">
                    <div class="header">{{ $t('general.baccarat.pair') }}</div>
                    <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }" class="users" data-container="banker">
                        <div class="empty">{{ $t('general.baccarat.empty') }}</div>
                    </overlay-scrollbars>
                </div>
            </overlay-scrollbars>
        </div>
    </div>
</template>

<script>
    import gsap from 'gsap';
    import { mapGetters } from 'vuex';
    import Bus from "../../../bus";

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
            return `<i class="${deck.toIcon(card)}" style="color: ${card.type === 'diamonds' || card.type === 'hearts' ? '#e86376' : '#2b2f3b'}"></i>`;
        }
    };

    export default {
        computed: {
            ...mapGetters(['currency', 'currencies'])
        },
        data() {
            return {
                _id: null,

                chipDisplayValue: 1000,
                chip: 1000,
                bet: {}
            }
        },
        mounted() {
            Bus.$on('sidebar:chipSelect', (chip) => {
                this.chip = chip.value;
                this.chipDisplayValue = chip.displayValue;
            });
        },
        methods: {
            gameDataRetrieved() {
                if(this.gameInstance.game.data.timestamp > 0) {
                    const now = +new Date() / 1000;
                    const left = parseInt(now - this.gameInstance.game.data.timestamp);

                    if (left > 0 && left <= 15) this.countdown($('.game-baccarat').find('.timer'), {
                        timePassed: left
                    });

                    _.forEach(this.gameInstance.game.data.players, (data) => this.addPlayer(data));
                }

                const vm = this;
                $('.cell').on('click', function() {
                    let stack = $(this).find('.bet-stack'), b = $(this).attr('data-chip');
                    if(stack.length === 0) {
                        stack = $('<div class="bet-stack"></div>');
                        stack.hide().fadeIn('fast');
                        $(this).append(stack);
                        const e = $(`<div class="user-chip" data-display-value="${vm.chipDisplayValue}" data-token-value="${vm.chip}">${vm.abbreviate(vm.chipDisplayValue)}</div>`);
                        stack.append(e);
                    } else {
                        const colors = {
                            1: '#828f9a',
                            10: 'rgb(0, 188, 212)',
                            100: 'rgb(76, 175, 80)',
                            1000: 'rgb(139, 195, 74)',
                            10000: 'rgb(205, 220, 57)',
                            100000: 'rgb(192, 202, 51)',
                            1000000: 'rgb(255, 235, 59)',
                            10000000: 'rgb(251, 192, 45)',
                            100000000: 'rgb(255, 179, 0)',
                            1000000000: 'rgb(251, 140, 0)',
                            10000000000: 'rgb(244, 81, 30)',
                            100000000000: '#AA88FF'
                        };

                        const e = stack.find('.user-chip');
                        e.attr('data-display-value', (vm.getBet(b) + vm.chip) * 100000000)
                            .attr('data-token-value', vm.getBet(b) + vm.chip)
                            .html(vm.abbreviate((vm.getBet(b) + vm.chip) * 100000000));

                        _.forEach(Object.keys(colors), (c) => {
                            if((vm.getBet(b) + vm.chip) * 100000000 >= c) e.css({ 'background-color': colors[c] });
                        });
                    }

                    vm.setBet(b, vm.getBet(b) + vm.chip);
                    vm.playSound('/sounds/click.mp3');
                });
            },
            customWagerCalculation() {
                return parseFloat(this.rawBitcoin(this.currency, parseFloat($('#bet').html())));
            },
            clear() {
                this.bet = {};
                $('.bet-stack').fadeOut('fast', () => $('.bet-stack').remove());
                $('#bet').html(this.rawBitcoin(this.currency, 0));
            },
            customWagerIncrease(category) {
                if(category === 'initialBet') return bet;
                if(this.gameInstance.game.autoBetSettings[category].action === 'reset') this.bet = this.gameInstance.game.autoBetSettings.initialBet;
                else if(this.gameInstance.game.autoBetSettings[category].value > 0)
                    _.forEach(this.bet, (value, key) => this.bet[key] = this.bet[key] + ((( this.gameInstance.game.autoBetSettings[category].value / 100) * this.bet[key])));
                setTimeout(this.setBet, 5000);
            },
            extendedAutoBetHandle(take) {},
            getBet(chip) {
                if(!this.bet[chip]) return 0;
                return this.bet[chip];
            },
            setBet(chip = null, value = null) {
                if(chip !== null && value !== null) this.bet[chip] = value;
                let total = 0;
                for(let i = 0; i < Object.keys(this.bet).length; i++) total += this.bet[Object.keys(this.bet)[i]];
                $('#bet').html(this.rawBitcoin(this.currency, total));
            },
            getClientData() {
                return {
                    bet: this.bet
                }
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-chips' },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } }
                ];
            },
            callback(response) {
                if(this.gameInstance.bettingType === 'manual') $('.play-button').addClass('disabled');
            },
            show(data) {
                this._id = Math.random();
                let id = this._id;

                const v = () => id === this._id;

                $('.baccaratCardsPlayer .score, .baccaratCardsBanker .score').stop().removeClass('win').removeClass('draw').fadeOut(300);
                $('.baccaratCardsPlayer .card, .baccaratCardsBanker .card').stop().fadeOut(300, function() {
                    $(this).remove();
                });

                setTimeout(() => {
                    if(!v()) return;

                    this.chain(data.player.length, 500, (i) => {
                        if(!v()) return;

                        this.sendCard(this.createCard('Player', data.player[i - 1]), i - 1);
                        if(i === data.player.length) {
                            setTimeout(() => {
                                if(!v()) return;
                                $('.baccaratCardsPlayer .score').html(data.score.player).fadeIn('fast');

                                this.chain(data.dealer.length, 500, (i) => {
                                    if(!v()) return;
                                    this.sendCard(this.createCard('Banker', data.dealer[i - 1]), i - 1);

                                    if (i === data.dealer.length) {
                                        $(`[data-container] .user`).slideUp('fast', () => $('[data-container] .user').remove());
                                        $(`[data-container] .empty`).fadeIn('fast');

                                        setTimeout(() => {
                                            if(!v()) return;
                                            $('.baccaratCardsBanker .score').html(data.score.dealer).stop().fadeIn('fast');

                                            $('.baccaratCardsPlayer .card, .baccaratCardsPlayer .score').toggleClass('win', data.status === 'player');
                                            $('.baccaratCardsBanker .card, .baccaratCardsBanker .score').toggleClass('win', data.status === 'banker');
                                            $('.baccaratCardsPlayer .card, .baccaratCardsPlayer .score').toggleClass('draw', data.status === 'draw');
                                            $('.baccaratCardsBanker .card, .baccaratCardsBanker .score').toggleClass('draw', data.status === 'draw');

                                            $('.play-button').removeClass('disabled');
                                        }, 500);
                                    }
                                });
                            }, 500);
                        }
                    });
                }, 300);
            },
            createCard(hand, instance) {
                const e = $(`<div class="card">
                    <div class="value" style="color: ${instance.type === 'diamonds' || instance.type === 'hearts' ? '#e86376' : '#2b2f3b'}">${instance.value}</div>
                    <div class="icon">${deck.toString(instance)}</div>
                </div>`)[0];
                gsap.set(e, { x: $('.deck').offset().left, y: $('.deck').offset().top, overwrite: false });
                return {
                    element: e,
                    hand: hand,
                    transform: {
                        x: () => gsap.getProperty(e, "x"),
                        y: () => gsap.getProperty(e, "y")
                    },
                    first: { x: 0, y: 0 },
                    last: { x: 0, y: 0 }
                };
            },
            sendCard(e, handIndex) {
                const stagger = 0.05;
                const duration = 0.5;

                const tl = gsap.timeline();

                e.element.style.transform = "none";
                let rect = e.element.getBoundingClientRect();
                e.first.x = rect.left;
                e.first.y = rect.top;

                $(e.element).hide();
                $(`.baccaratCards${e.hand}`).append(e.element);
                $(e.element).fadeIn('fast');

                rect = e.element.getBoundingClientRect();
                e.last.x = rect.left;
                e.last.y = rect.top;

                tl.set(e.element, {
                    x: e.transform.x() + e.first.x - e.last.x,
                    y: e.transform.y() + e.first.y - e.last.y
                });

                tl.to(e.element, duration, {
                    x: 20 * handIndex,
                    y: -180 * handIndex
                }, stagger);

                this.playSound('/sounds/card_slide.mp3', 100);
            },
            addPlayer(data) {
                _.forEach(data.data.bet, (value, key) => {
                    $(`[data-container="${key}"] .empty`).hide();
                    $(`[data-container="${key}"] .os-content`).append(`<div class="user" onclick="window.open('/profile/${data.user._id}', '_blank')">
                        <div class="avatar"><img src="${data.user.avatar}" alt></div>
                        <div class="name">${data.user.name}</div>
                        <div class="bet">
                            ${this.rawBitcoin(data.game.currency, value)} ${this.currencies[data.game.currency].name.toUpperCase()}
                        </div>
                    </div>`);
                });
            },
            multiplayerEvent(event, data) {
                switch (event) {
                    case 'MultiplayerGameBet':
                        this.addPlayer(data);
                        break;
                    case 'MultiplayerGameFinished':
                        this.show(data.data);
                        break;
                    case 'MultiplayerTimerStart':
                        this.countdown($('.game-baccarat').find('.timer'), {
                            timePassed: 0
                        });

                        //$.setFooterMultiplayerSeed(data.client_seed, data.nonce);
                        break;
                }
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-baccarat {
        .game-content-baccarat {
            .fa-baccarat-ribbon {
                position: relative;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                font-size: 18em;
            }

            display: flex;
            flex-direction: column;
            height: 900px !important;
            min-height: 900px !important;
            padding: 0;

            @include themed() {
                .baccarat-players {
                    display: flex;
                    flex-direction: row;

                    .os-host {
                        width: 0 !important;
                        flex: 1;
                    }

                    .baccarat-players-container .os-host {
                        width: 100% !important;
                        flex: unset !important;
                    }

                    .baccarat-players-scrollable {
                        width: 100%;

                        .os-content {
                            display: flex;
                            flex-direction: row !important;
                        }
                    }

                    .users .os-content {
                        display: flex;
                        align-items: baseline;
                        flex-direction: column !important;
                        position: relative;

                        .empty {
                            color: t('link');
                            text-align: center;
                            width: 80%;
                            min-width: 85px;
                            position: absolute;
                            left: 50%;
                            top: 50%;
                            transform: translate(-50%, -50%);
                            cursor: default;
                            user-select: none;
                        }
                    }

                    .tie {
                        .header {
                            border-bottom-color: #ec487f !important;
                        }
                    }

                    .player {
                        .header {
                            border-bottom-color: #1652f0 !important;
                        }
                    }

                    .banker {
                        .header {
                            border-bottom-color: #3bc248 !important;
                        }
                    }

                    .baccarat-players-container {
                        display: flex;
                        flex-direction: column;
                        width: 100%;
                        min-width: 247px;
                        user-select: none;

                        .header {
                            text-transform: uppercase;
                            font-weight: 600;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            text-align: center;
                            padding: 15px;
                            border-bottom: 3px solid;
                        }

                        .users {
                            height: 185px;

                            .os-host {
                                height: 100%;
                            }

                            .user {
                                display: flex;
                                flex-direction: row;
                                align-items: center;
                                background: t('sidebar');
                                transition: background 0.3s ease;
                                cursor: pointer;
                                padding: 15px;
                                width: 100%;

                                &:hover {
                                    background: darken(t('sidebar'), 2%);
                                }

                                &:nth-child(even) {
                                    background: t('input') !important;

                                    &:hover {
                                        background: darken(t('input'), 2%) !important;
                                    }
                                }

                                .avatar {
                                    margin-right: 5px;

                                    img {
                                        border-radius: 50%;
                                        width: 32px;
                                        height: 32px;
                                    }
                                }

                                .name {
                                    white-space: nowrap;
                                    text-overflow: ellipsis;
                                    width: 80px;
                                    overflow: hidden;
                                }

                                .bet {
                                    margin-left: auto;
                                }
                            }
                        }
                    }
                }
            }

            .baccaratCardsPlayer, .baccaratCardsBanker {
                position: absolute;
                margin-top: 90px;

                .score {
                    padding: 3px 20px;
                    border-radius: 40px;
                    position: absolute;
                    right: -40px;
                    top: -15px;
                    z-index: 15;
                    @include themed() {
                        color: t('textInverted');
                        background: t('link');
                    }
                    transition: background 0.3s ease;
                }

                .score.win {
                    background: #62ca5b;
                    color: white;
                }

                .score.draw {
                    background: #ffc645;
                    color: white;
                }
            }

            .baccaratCardsPlayer {
                left: 100px;
            }

            .baccaratCardsBanker {
                right: 150px;
            }

            .card {
                position: relative;
                top: 0;
                left: 0;
                width: 125px;
                height: 200px;
                display: flex;
                flex-direction: column;
                box-shadow: -15px 10px 20px 0 rgba(0, 0, 0, .1);
                border: 3px solid transparent;
                border-radius: 10px;
                padding: 10px;
                transition: border-color 0.3s ease;
                font-size: 1.5em;
                background: white;
                cursor: default;
                user-select: none;

                .value {
                    font-size: 1.6em;
                    margin-bottom: -5px;
                }
            }

            .card.win {
                border-color: #62ca5b;
            }

            .card.draw {
                border-color: #ffc645;
            }

            @include themed() {
                .baccarat-game-field {
                    height: 100%;
                    position: relative;

                    .timer {
                        position: absolute;
                        left: 50%;
                        top: 50%;
                        transform: translate(-50%, -50%);
                    }
                }

                .baccarat-field {
                    z-index: 30;
                    user-select: none;

                    .header {
                        position: relative;
                        z-index: 500;
                        background: darken(t('sidebar'), 5%);
                        text-shadow: 0 0 2px t('sidebar');
                        padding: 10px;

                        .right {
                            position: absolute;
                            right: 0;
                            bottom: 0;

                            button {
                                font-size: 10px;
                                margin-top: -23px;
                                margin-right: 6px;
                                text-transform: uppercase;
                            }
                        }
                    }

                    .bets {
                        display: flex;
                        flex-direction: row;
                        width: 100%;

                        .rows {
                            flex-direction: column !important;
                        }

                        .content {
                            display: flex;
                            flex-direction: row;
                            width: 100%;

                            .cell {
                                padding: 15px;
                                margin: 0 1px 0 0;
                                position: relative;
                                display: flex;
                                flex-direction: column;
                                align-items: center;
                                justify-content: center;
                                text-align: center;
                                width: 100%;
                                height: 100%;
                                cursor: pointer;
                                transition: background 0.3s ease;
                                background: darken(t('sidebar'), 2%);

                                &:hover {
                                    background: darken(t('sidebar'), 2.5%);
                                }

                                .title {
                                    color: t('link');
                                }

                                .bet-stack {
                                    position: absolute;
                                    left: 0;
                                    top: 0;
                                    width: 100%;
                                    height: 100%;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    background: rgba(black, 0.65);

                                    .user-chip {
                                        position: absolute;
                                        z-index: 2;
                                        width: 22px;
                                        height: 22px;
                                        background: url(/img/misc/chip.svg) 0 0/cover no-repeat #828f9a;
                                        border-radius: 50%;
                                        text-align: center;
                                        display: flex;
                                        justify-content: center;
                                        align-items: center;
                                        font-size: 0.7em;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        .deck {
            position: absolute;
            right: 100px;
            top: 0;

            div {
                width: 100px;
                height: 55px;
                background: white;
                border-bottom-left-radius: 8px;
                padding: 5px;
                border-bottom-right-radius: 8px;
                box-shadow: 0 1px 0 rgba(0,0,0,.2);
                position: absolute;
                right: -45px;
            }

            div:first-child {
                z-index: 5;
            }

            div:nth-child(2) {
                margin-top: 2px;
                z-index: 4;
            }

            div:nth-child(3) {
                margin-top: 4px;
                z-index: 3;
            }

            div:nth-child(4) {
                margin-top: 6px;
                z-index: 2;
            }

            div {
                div {
                    background: url('/img/misc/cardback.svg');
                    width: 100%;
                    height: calc(100% + 5px);
                    position: relative;
                    background-size: cover;
                    background-position: bottom;
                    top: -5px;
                    border-bottom-left-radius: 6px;
                    border-bottom-right-radius: 6px;
                    right: unset;
                }
            }
        }


        [data-display-value="1"] {
            background-color: #828f9a !important;
        }

        [data-display-value="10"] {
            background-color: rgb(0, 188, 212) !important;
        }

        [data-display-value="100"] {
            background-color: rgb(76, 175, 80) !important;
        }

        [data-display-value="1000"] {
            background-color: rgb(139, 195, 74) !important;
        }

        [data-display-value="10000"] {
            background-color: rgb(205, 220, 57) !important;
        }

        [data-display-value="100000"] {
            background-color: rgb(192, 202, 51) !important;
        }

        [data-display-value="1000000"] {
            background-color: rgb(255, 235, 59) !important;
        }

        [data-display-value="10000000"] {
            background-color: rgb(251, 192, 45) !important;
        }

        [data-display-value="100000000"] {
            background-color: rgb(255, 179, 0) !important;
        }

        [data-display-value="1000000000"] {
            background-color: rgb(251, 140, 0) !important;
        }

        [data-display-value="10000000000"] {
            background-color: rgb(244, 81, 30) !important;
        }

        [data-display-value="1000000000000"] {
            background-color: #AA88FF !important;
        }
    }

    @media(max-width: 1320px) {
        .baccaratCardsPlayer {
            left: 30px !important;
        }

        .baccaratCardsBanker {
            right: 70px !important;
        }
    }

    @media(max-width: 480px) {
        .card {
            width: 105px !important;
            height: 190px !important;
        }

        .baccaratCardsPlayer {
            left: 10px !important;
        }

        .baccaratCardsBanker {
            right: 50px !important;
        }
    }
</style>
