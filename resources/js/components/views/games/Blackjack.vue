<template>
    <div>
        <i class="fas fa-blackjack-ribbon ignoresUpdates"></i>
        <div class="deck">
            <div><div></div></div>
            <div><div></div></div>
            <div><div></div></div>
            <div><div></div></div>
        </div>
        <div class="blackjack_score dealer" style="display: none"></div>
        <div class="blackjack_score player" style="display: none"></div>
        <div class="blackjack_score playerSplit" style="display: none"></div>
        <div class="arrowContainer arrowPlayer" style="display: none">
            <div class="arrow"><div></div></div>
            <div class="arrow"><div></div></div>
        </div>
        <div class="arrowContainer arrowSplit" style="display: none">
            <div class="arrow"><div></div></div>
            <div class="arrow"><div></div></div>
        </div>
        <div class="blackjack_container">
            <div id="dealer">
                <div id="dhand"></div>
            </div>
            <div id="split">
                <div id="shand"></div>
            </div>
            <div id="player">
                <div id="phand"></div>
            </div>
        </div>
    </div>
</template>

<script>
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

            return card === undefined ? icons['spades'] : icons[card.type];
        },
        toString: function(card) {
            return card.value + `<i class="${deck.toIcon(card)}"></i>`;
        }
    };

    class Player {

        constructor(vm, ele, score) {
            this.vm = vm;
            this.hand = [];
            this.ele = ele;
            this.score = score;
        }

        getHand() {
            return this.hand;
        }

        setHand(card) {
            this.hand.push(card);
        }

        resetHand() {
            this.hand = [];
        }

        flipCards(dealerSecretOptions) {
            $('.down').each((i, e) => {
                $(e).removeClass('down').addClass('up');
                this.vm.renderCard(false, false, $(this), false, dealerSecretOptions);
            });
        }

        hit(dbl) {
            this.vm.updateGameInstance((i) => i.playTimeout = true);
            this.vm.sendTurn({ type: 'hit' }, (response) => {
                $('.splitB').toggleClass('disabled', true);

                this.vm.deal.dealCard(this.vm.splitted ? this.vm.split : this.vm.player, {
                    'index': response.data.player.index + 1,
                    'rank': response.data.player.value,
                    'suit': response.data.player.type,
                    'value': response.data.player.blackjack_value,
                    'type': 'up'
                });

                (this.vm.splitted ? this.vm.split : this.vm.player).getScore((response) => {
                    if (dbl || response >= 21) setTimeout(() => (this.vm.splitted ? this.vm.split : this.vm.player).stand(), 500);
                    else this.vm.updateGameInstance((i) => i.playTimeout = false);

                    (this.vm.splitted ? this.vm.split : this.vm.player).updateBoard();
                });
            });
        }

        stand() {
            this.vm.sendTurn({ type: 'stand' }, (response) => {
                if(response.type === 'continue') {
                    this.vm.splitted = true;
                    $('.arrowPlayer').fadeOut('fast');
                    $('.arrowSplit').fadeIn('fast');

                    this.vm.updateGameInstance((i) => i.playTimeout = false);
                    return;
                }

                this.vm.dealer.flipCards({
                    'rank': response.data.dealerReveal['value'],
                    'suit': `<i class="${deck.toIcon(deck[response.data.dealerReveal['index']])}"></i>`,
                    'value': response.data.dealerReveal['blackjack_value']
                });

                if(response.data.dealerDraw.length === 0) {
                    this.vm.playSound('/sounds/'+(response.game.multiplier === 0 ? 'lose' : 'win')+'.mp3');
                    this.vm.resultPopup(response.game);
                }

                this.vm.chain(response.data.dealerDraw.length, 250, (i) => {
                    this.vm.deal.dealCard(this.vm.dealer, {
                        'index': response.data.dealerDraw[i - 1].index + 1,
                        'rank': response.data.dealerDraw[i - 1].value,
                        'suit': response.data.dealerDraw[i - 1].type,
                        'value': response.data.dealerDraw[i - 1].blackjack_value,
                        'type': 'up'
                    });

                    this.vm.dealer.getScore((response) => $('.dealer').html(response));

                    if(i === response.data.dealerDraw.length) {
                        this.vm.playSound('/sounds/'+(response.game.multiplier === 0 ? 'lose' : 'win')+'.mp3');
                        this.vm.resultPopup(response.game);
                    }
                });

                this.vm.dealer.getScore((response) => $('.dealer').html(response));
                this.vm.dealer.updateBoard();
                this.vm.finishExtended(false);
            });
        }

        split() {
            $('.splitB, .double').addClass('disabled');
            this.vm.sendTurn({ type: 'split' }, (response) => {
                if(response.data.error) {
                    this.vm.$toast.error(this.vm.$i18n.t('general.error.invalid_wager'));
                    return;
                }

                this.vm.player.resetHand();
                this.vm.split.resetHand();

                setTimeout(() => {
                    this.vm.deal.dealCard(this.vm.player, {
                        'index': response.data.player[0].index + 1,
                        'rank': response.data.player[0].value,
                        'suit': response.data.player[0].type,
                        'value': response.data.player[0].blackjack_value,
                        'type': 'up'
                    });
                    setTimeout(() => {
                        this.vm.deal.dealCard(this.vm.split, {
                            'index': response.data.split[0].index + 1,
                            'rank': response.data.split[0].value,
                            'suit': response.data.split[0].type,
                            'value': response.data.split[0].blackjack_value,
                            'type': 'up'
                        });
                        setTimeout(() => {
                            this.vm.deal.dealCard(this.vm.player, {
                                'index': response.data.player[1].index + 1,
                                'rank': response.data.player[1].value,
                                'suit': response.data.player[1].type,
                                'value': response.data.player[1].blackjack_value,
                                'type': 'up'
                            });
                            setTimeout(() => {
                                this.vm.deal.dealCard(this.vm.split, {
                                    'index': response.data.split[1].index + 1,
                                    'rank': response.data.split[1].value,
                                    'suit': response.data.split[1].type,
                                    'value': response.data.split[1].blackjack_value,
                                    'type': 'up'
                                });

                                $('.arrowPlayer').fadeIn('fast');
                            }, 250);
                        }, 250);
                    }, 250);
                }, 250);
            });
        }

        double() {
            this.vm.sendTurn({ type: 'double' }, (response) => {
                if(response.data.error) {
                    this.vm.$toast.error(this.vm.$i18n.t('general.error.invalid_error'));
                    return;
                }
                this.vm.player.hit(true);
            });
        }

        getScore(callback) {
            let hand = this.getHand(), score = 0, aces = 0, i;

            for(i = 0; i < hand.length; i++) {
                if(hand[i].rank.length === 0) continue;

                score += hand[i].value;

                if(hand[i].value === 11) aces += 1;

                if(score > 21 && aces > 0) {
                    score -= 10;
                    aces--;
                }
            }

            $(this.score).fadeIn('fast');
            callback(score);
        }

        updateBoard() {
            this.getScore((response) => $(this.score).html(response));
        }

        getElements() {
            return {
                'ele': this.ele,
                'score': this.score
            }
        }

    }

    class Card {

        constructor(card) {
            this.card = card;
        }

        getIndex() {
            return this.card.index;
        }

        getType() {
            return this.card.type;
        }

        getRank() {
            return this.card.rank;
        }

        getSuit() {
            return this.card.suit;
        }

        getValue() {
            switch (this.getRank()) {
                case 'A': return 11;
                case 'K': case 'Q': case 'J': return 10;
                default: return parseInt(this.getRank(), 0);
            }
        }

    }

    class Deal {

        constructor(vm) {
            this.vm = vm;
        }

        setCard(sender, card) {
            sender.setHand(card);
        }

        dealCard(sender, card, isHiddenByServer) {
            let elements = sender.getElements(), dealerHand = this.vm.dealer.getHand();
            this.vm.deal.setCard(sender, card);

            this.vm.playSound('/sounds/card_slide.mp3', 100);
            this.vm.renderCard(elements.ele, sender, false, isHiddenByServer, undefined);
            sender.getScore((response) => $(elements.score).html(response));

            if(this.vm.player.getHand().length < 3) {
                if(dealerHand.length > 0 && dealerHand[0].rank === 'A') {
                    if($('.insurance').length === 0 && this.vm.isExtendedGameStarted()) {
                        $('.game-container').prepend(`
                            <div class="insurance">
                                <div class="window">
                                    <div class="insurance-desc">${this.vm.$i18n.t('general.insurance')}</div>
                                    <div class="mt-2">
                                        <button class="btn btn-primary mr-2" id="i-a">${this.vm.$i18n.t('general.accept')}</button>
                                        <button class="btn btn-secondary" id="i-d">${this.vm.$i18n.t('general.decline')}</button>
                                    </div>
                                </div>
                            </div>
                        `);

                        $('#i-d').on('click', () => $('.insurance').remove());

                        $('#i-a').on('click', () => {
                            $('.insurance').remove();
                            this.vm.sendTurn({type: 'insurance'}, (response) => {
                                if(response.data.error) this.vm.$toast.error(this.vm.$i18n.t('general.error.invalid_wager'));
                                this.vm.$toast.success(this.vm.$i18n.t('general.insurance_success'));
                            });
                        });
                    }
                }

                this.vm.player.getScore((response) => {
                    if(response === 21) {
                        if(this.vm.blackjack) return;
                        setTimeout(() => this.vm.player.stand(), 500);
                        this.vm.blackjack = true;
                    }
                });
            }
        }

    }

    export default {
        data() {
            return {
                player: null,
                split: null,
                dealer: null,
                deal: null,
                blackjack: false,
                splitted: false
            }
        },
        mounted() {
            this.deal = new Deal(this);
            this.player = new Player(this, '#phand', '.player');
            this.split = new Player(this, '#shand', '.playerSplit');
            this.dealer = new Player(this, '#dhand', '.dealer');
            this.resetBoard();
        },
        methods: {
            newGame(callback) {
                this.resetBoard();
                this.updateGameInstance((i) => i.playTimeout = true);

                this.sendTurn({type: 'info'}, (response) => {
                    setTimeout(() => {
                        this.deal.dealCard(this.player, {
                            'index': response.data.player[0].index + 1,
                            'rank': response.data.player[0].value,
                            'suit': response.data.player[0].type,
                            'value': response.data.player[0].blackjack_value,
                            'type': 'up'
                        });
                        setTimeout(() => {
                            this.deal.dealCard(this.dealer, {
                                'index': response.data.dealer.index + 1,
                                'rank': response.data.dealer.value,
                                'suit': response.data.dealer.type,
                                'value': response.data.dealer.blackjack_value,
                                'type': 'up'
                            });
                            setTimeout(() => {
                                this.deal.dealCard(this.player, {
                                    'index': response.data.player[1].index + 1,
                                    'rank': response.data.player[1].value,
                                    'suit': response.data.player[1].type,
                                    'value': response.data.player[1].blackjack_value,
                                    'type': 'up',
                                });
                                setTimeout(() => {
                                    this.deal.dealCard(this.dealer, {
                                        'index': 1,
                                        'rank': '',
                                        'suit': '',
                                        'value': 0,
                                        'type': 'down'
                                    }, true);

                                    $('.splitB').toggleClass('disabled', response.data.player[0].blackjack_value !== response.data.player[1].blackjack_value);
                                    this.updateGameInstance((i) => i.playTimeout = false);
                                    callback();
                                }, 500);
                            }, 500);
                        }, 500);
                    }, 500);
                });
            },
            resetBoard() {
                this.blackjack = false;
                this.splitted = false;
                $('#dhand').html('');
                $('#phand').html('');
                $('#shand').html('');
                $('#phand, #dhand, #shand').css('left', 0);
                $('.dealer, .player, .playerSplit').fadeOut('fast');
                $('.insurance, .arrowContainer').fadeOut('fast');
                $('.double').removeClass('disabled');

                this.player.resetHand();
                this.dealer.resetHand();
                this.split.resetHand();
            },
            renderCard(ele, sender, item, isHiddenByServer, secretRevealOptions) {
                let hand, i, card;

                if (!item) {
                    hand = sender.getHand();
                    i = hand.length - 1;
                    card = new Card(hand[i]);
                } else {
                    hand = this.dealer.getHand();
                    card = new Card(hand[1]);
                }

                if (secretRevealOptions !== undefined) {
                    card.rank = secretRevealOptions.rank;
                    card.suit = secretRevealOptions.suit;
                    card.value = secretRevealOptions.value;
                    card.type = 'up';

                    this.dealer.getHand()[1] = card;
                }

                let rank = card.getRank(), suit = card.getSuit(), posx = 0, posy = 20, speed = 200, cards = ele + ' .card-' + i, type = card.getType();
                if (i > 0) posx -= 50 * i;

                if (!item) {
                    $(ele).append(
                        `<div class="${(isHiddenByServer !== undefined && isHiddenByServer === true ? 'dealerSecret ' : '')} blackjack_card card-${i} ${type}">
                            <span class="pos-0 ${suit === 'diamonds' || suit === 'hearts' ? 'text-danger' : '' }">
                                <span class="rank">&nbsp;</span>
                                <span class="suit">&nbsp;</span>
                            </span>
                        </div>`
                    );

                    if (ele === '#phand') {
                        posy = 340;
                        speed = 500;

                        $(`${ele} .card-${i}`).attr('id', 'pcard-' + i);
                        if (hand.length < 2) setTimeout(() => this.player.getScore((response) => $('.player').html(response).fadeIn('fast')), 500);
                    } else if(ele === '#shand') {
                        posy = 120;
                        speed = 500;

                        $(`${ele} .card-${i}`).attr('id', 'scard-' + i);
                        if (hand.length < 2) setTimeout(() => this.player.getScore((response) => $('.split').html(response).fadeIn('fast')), 500);
                    }
                    else if(ele === '#dhand') {
                        $(`${ele} .card-${i}`).attr('id', 'dcard-' + i);
                        if (hand.length < 2) setTimeout(() => this.dealer.getScore((response) => $('.dealer').html(response).fadeIn('fast')), 100);
                    }

                    $(`${ele} .card-${i}`).css({'z-index': i, top: 0, right: 0 }).animate({
                        'top': posy,
                        'right': posx
                    }, speed);
                } else cards = item;

                if (type === 'up' || item) {
                    if (secretRevealOptions === undefined) $(cards).find('span.rank').html(rank);
                    else $('.dealerSecret span.rank').html(secretRevealOptions.rank);
                    $(cards).find('span.suit').html('<i class="' + deck.toIcon(deck[card.getIndex()]) + '"></i>');
                }
            },
            hit() {
                $('.double').addClass('disabled');
                this.player.hit();
            },
            double() {
                if($('.double').hasClass('disabled')) return;
                this.player.double();
            },
            prfSplit() {
                if($('.splitB').hasClass('disabled')) return;
                this.player.split();
            },
            stand() {
                this.player.stand();
            },
            getClientData() {
                return {};
            },
            callback() {
                if(this.isExtendedGameStarted()) {
                    this.newGame(() => {
                        $('.play-button').fadeOut('fast');
                        $('.double, .hit, .stand, .splitB').fadeIn('fast');
                    });
                } else {
                    $('.play-button').fadeIn('fast');
                    $('.double, .hit, .stand, .splitB').fadeOut('fast');
                }
            },
            restore(game) {
                setTimeout(() => {
                    this.deal.dealCard(this.player, {
                        'index': game.user_data.player[0].index + 1,
                        'rank': game.user_data.player[0].value,
                        'suit': game.user_data.player[0].type,
                        'value': game.user_data.player[0].blackjack_value,
                        'type': 'up'
                    });
                    setTimeout(() => {
                        this.deal.dealCard(this.dealer, {
                            'index': game.user_data.dealer[0].index + 1,
                            'rank': game.user_data.dealer[0].value,
                            'suit': game.user_data.dealer[0].type,
                            'value': game.user_data.dealer[0].blackjack_value,
                            'type': 'up'
                        });
                        setTimeout(() => {
                            this.deal.dealCard(this.player, {
                                'index': game.user_data.player[1].index + 1,
                                'rank': game.user_data.player[1].value,
                                'suit': game.user_data.player[1].type,
                                'value': game.user_data.player[1].blackjack_value,
                                'type': 'up'
                            });
                            setTimeout(() => {
                                this.deal.dealCard(this.dealer, {
                                    'index': 1,
                                    'rank': '',
                                    'suit': '',
                                    'value': 0,
                                    'type': 'down'
                                }, true);

                                if(game.user_data.split.length > 0) {
                                    for(let i = 0; i < game.user_data.split.length; i++)
                                        this.deal.dealCard(this.split, {
                                            'index': game.user_data.split[i].index + 1,
                                            'rank': game.user_data.split[i].value,
                                            'suit': game.user_data.split[i].type,
                                            'value': game.user_data.split[i].blackjack_value,
                                            'type': 'up'
                                        });
                                    this.splitted = game.user_data.currentHand === 1;
                                    if(this.splitted) $('.arrowSplit').fadeIn('fast');
                                    else $('.arrowPlayer').fadeIn('fast');
                                }

                                if(game.user_data.player.length > 2) {
                                    for(let i = 2; i < game.user_data.player.length; i++)
                                        this.deal.dealCard(this.player, {
                                            'index': game.user_data.player[i].index + 1,
                                            'rank': game.user_data.player[i].value,
                                            'suit': game.user_data.player[i].type,
                                            'value': game.user_data.player[i].blackjack_value,
                                            'type': 'up'
                                        });
                                }

                                this.updateGameInstance((i) => i.playTimeout = false);

                                $('.splitB').toggleClass('disabled', game.user_data.player[0].blackjack_value !== game.user_data.player[1].blackjack_value || game.user_data.player.length !== 2);

                                $('.play-button').fadeOut('fast');
                                $('.double, .hit, .stand, .splitB').fadeIn('fast');
                            }, 500);
                        }, 500);
                    }, 500);
                }, 500);
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'buttons', data: { buttons: [
                        { label: this.$i18n.t('general.stand'), class: 'stand', preventMark: true, isAction: true, callback: () => this.stand() },
                        { label: this.$i18n.t('general.hit'), class: 'hit', preventMark: true, isAction: true, callback: () => this.hit() }
                    ] } },
                    { name: 'buttons', data: { buttons: [
                        { label: this.$i18n.t('general.double'), class: 'double', preventMark: true, isAction: true, callback: () => this.double() },
                        { label: this.$i18n.t('general.split'), class: 'splitB', preventMark: true, isAction: true, callback: () => this.prfSplit() }
                    ] } },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } }
                ]
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-sidebar .game-sidebar-buttons-container .game-sidebar-buttons-container-button {
        @include themed() {
            &.splitB, &.hit, &.stand, &.double {
                display: none;
                margin-top: 5px;
            }
        }
    }

    .game-blackjack {
        .game-content-blackjack {
            .fa-blackjack-ribbon {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                font-size: 18em;
            }

            .blackjack_card i {
                font-size: 4.5em;
                transform: translate(-50%, -50%);
            }

            min-height: 600px !important;
        }

        .arrowPlayer {
            top: 485px;
        }

        .arrowSplit {
            top: 320px;
        }

        .arrowContainer {
            position: absolute;
            left: 25px;
            transform: rotate(-90deg);

            .arrow {
                animation-name: bounce;
                animation-duration: 1.25s;
                animation-iteration-count: infinite;
            }

            .arrow {
                animation-name: opacity;
                animation-duration: 1.25s;
                animation-iteration-count: infinite;
                transition: opacity 0.3s ease;
            }

            .arrow:last-child {
                animation-direction: reverse;
                margin-top: -6px;
            }

            .arrow > div {
                width: 16px;
                height: 16px;
                border-right: 4px solid #bebebe;
                border-bottom: 4px solid #bebebe;
                transform: rotate(45deg) translateZ(1px);
            }
        }

        @keyframes opacity {
            0% {
                opacity: 0;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        @keyframes bounce {
            0% {
                transform: translateY(0);
            }

            10% {
                transform: translateY(3px);
            }

            20% {
                transform: translateY(6px);
            }

            30% {
                transform: translateY(9px);
            }

            40% {
                transform: translateY(12px);
            }

            50% {
                transform: translateY(15px);
            }

            60% {
                transform: translateY(18px);
            }

            70% {
                transform: translateY(21px);
            }

            80% {
                transform: translateY(24px);
            }

            90% {
                transform: translateY(27px);
            }

            100% {
                transform: translateY(30px);
            }
        }

        .deck {
            position: absolute;
            right: 100px;
            top: 0;

            div {
                width: 100px;
                height: 115px;
                background: white;
                border-bottom-left-radius: 8px;
                padding: 9px;
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
                    height: calc(100% + 9px);
                    background-size: cover;
                    position: relative;
                    background-position: center;
                    top: -9px;
                    border-bottom-left-radius: 6px;
                    border-bottom-right-radius: 6px;
                    right: unset;
                }
            }
        }

        .down {
            background: url('/img/misc/cardback.svg') !important;
            background-size: cover !important;
        }

        №dscore {
            position: absolute;
            bottom: 30px;
        }

        #pscore {
            position: absolute;
            bottom: 15px;
        }

        #phand, #dhand, #shand {
            font-weight: 700;
            position: absolute;
            top: -250px;
            white-space: nowrap;
        }

        #shand {
            top: -190px;
        }

        .blackjack_card {
            background: white;
            color: black;
            border: 10px solid white;
            border-radius: 5px;
            box-shadow: 0 0 1px black;
            display: inline-block;
            height: 135px;
            margin: 0 5px;
            position: absolute;
            right: 0;
            width: 95px;
        }

        .rank {
            font-size: 18px;
        }

        .suit {
            position: absolute;
            top: 35px;
            left: 7px;
            font-size: 3px;
        }

        .blackjack_container {
            position: absolute;
            user-select: none;
            left: 170px;
            top: 50%;
        }

        .blackjack_score {
            position: absolute;
            left: 118px;
            color: white;
            transform: translateX(-50%);
            padding: 5px 30px;
            background: #080808;
            border-radius: 15px;
            z-index: 9;
            user-select: none;
            cursor: default;
            margin-bottom: 80px;
        }

        .dealer {
            top: 160px;
        }

        .player {
            bottom: 7px;
        }

        .playerSplit {
            bottom: 170px;
        }

        .insurance {
            @include themed() {
                position: absolute;
                z-index: 999;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 3px;
                @include blur(t('sidebar'), 0.65, 0.95, 5px);

                .window {
                    display: flex;
                    flex-direction: column;
                    align-content: center;
                    justify-content: center;
                    .insurance-desc {
                        text-align: center;
                    }
                    .mt-2 {
                        display: flex;
                        flex-direction: row;
                        align-content: center;
                        justify-content: center;
                    }
                }
            }
        }

        @include media-breakpoint-down(md) {
            .playerSplit {
                bottom: 195px;
            }

            .player {
                bottom: 35px;
            }

            .dealer {
                top: 189px;
            }
        }

        @media(max-width: 750px) {
            .deck {
                display: none;
            }
        }
    }
</style>
