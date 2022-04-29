<template>
    <div class="row h-100">
        <div class="col-12 dice-column">
            <div class="diceCanvas"></div>
            <div class="dice-wrapper">
                <div class="dice-slider"></div>
            </div>
        </div>
        <div class="col-12 dice-footer-column">
            <div class="dice-footer">
                <div class="row">
                    <div class="col-6 col-md-4">
                        <div class="dice-header">{{ $t('general.payout') }}</div>
                        <input class="dice-payout" type="text" readonly>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="dice-header dice-target">{{ $t('dice.lower') }}</div>
                        <div class="position-relative">
                            <input class="dice-number" type="number" step="1" @input="handleNumInput($event.target.value)">
                            <button class="dice-append">
                                <i class="far fa-exchange-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="d-none d-md-block col-md-4">
                        <div class="dice-header">{{ $t('dice.chance') }}</div>
                        <input class="dice-chance" readonly type="text">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    require('jquery-ui/ui/widgets/slider');
    require('jquery-ui-touch-punch');
    import Dice from '../../../utils/dice3d';
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                currentTarget: 'lower',
                hideResultTimer: null,
                dice: null
            }
        },
        computed: {
            ...mapGetters(['quick'])
        },
        mounted() {
            this.dice = new Dice(this, $('.diceCanvas')[0]);

            $('.game-dice').find('.dice-slider').slider({
                range: 'min',
                min: 10,
                max: 110,
                value: 60,
                slide: (event, ui) => {
                    if(ui.value < 11 || ui.value > 109) return false;
                    if(this.currentTarget === 'lower' && ui.value > 108) return false;
                    if(this.currentTarget === 'higher' && ui.value < 12) return false;

                    $('#tooltip-value').html(ui.value);
                    setTimeout(this.update, 100);

                    this.playSound('/sounds/bet.mp3', 150);
                }
            });

            const tooltip = $('<div class="d_slider-tooltip_container"><div class="d_slider-tooltip"><span id="tooltip-value">60</span></div></div>').hide();
            const stop = function(left, css) {
                return $('<div>'+left+'</div>').css({
                    position: 'absolute',
                    top: -55,
                    color: '#565656',
                    'text-align': 'center',
                    'font-size': '13px',
                    left: `calc(${css})`,
                    transform: 'translateX(-50%)'
                });
            };

            $('.game-dice').find('.dice-slider').append($('<div id="circle" class="d_slider-circle" style="display: none" />'))
                .append($('<div id="result" class="d_slider-result" style="opacity: 0">0</div>'))
                .append(stop(110, '100%'))
                .append(stop(85, '75%'))
                .append(stop(60, '50%'))
                .append(stop(35, '25%'))
                .append(stop(10, '0'))
                .find('.ui-slider-handle').append(tooltip)
                .hover(function() {
                    tooltip.stop(true).fadeIn('fast');
                }, function() {
                    tooltip.stop(true).fadeOut('fast');
                });

            $('.game-dice').find('.dice-append').on('click', () => {
                this.currentTarget = this.currentTarget === 'lower' ? 'higher' : 'lower';

                $('.game-dice').find('.dice-slider').slider('option', {
                    range: this.currentTarget === 'lower' ? 'min' : 'max'
                });

                let v = 110 - $('.game-dice').find('.dice-slider').slider('value');
                if(this.currentTarget === 'higher' && v < 12) v = 12;
                if(this.currentTarget === 'lower' && v > 108) v = 108;

                if(v < 11) v = 11;
                if(v > 109) v = 109;

                $('.game-dice').find('.dice-slider').slider('value', v);
                $('#tooltip-value').html(v);

                this.update();
            });

            this.update();
            Bus.$on('sidebar:update', this.update);
        },
        watch: {
            currentTarget() {
                this.update();
            }
        },
        methods: {
            handleNumInput(val) {
                const value = parseInt(val);
                if(isNaN(value) || (this.currentTarget === 'lower' && value > 108) || (this.currentTarget === 'higher' && value < 12) || value < 11 || value > 109) {
                    $('.dice-number').toggleClass('error', true);
                    return;
                }

                $('.dice-number').toggleClass('error', false);

                $('.game-dice').find('.dice-slider').slider('value', value);
                this.update();
            },
            update() {
                const value = $('.game-dice .dice-slider').slider('value');
                Bus.$emit('profit:update', this.getProfit(this.currentTarget === 'lower' ? 0 : value - 10, this.currentTarget === 'higher' ? 100 : value - 10));
                $('.dice-chance').val((this.currentTarget === 'higher' ? 110 - value : value - 10) + '%');
                $('.dice-number').val(value);
                $('.dice-header.dice-target').html(this.$i18n.t(`dice.${this.currentTarget}`));
            },
            getClientData() {
                return {
                    target: this.currentTarget,
                    value: $('.game-dice .dice-slider').slider('value')
                }
            },
            callback(response) {
                if(!this.quick) {
                    this.updateGameInstance((i) => i.playTimeout = true);
                    setTimeout(() => this.updateGameInstance((i) => i.playTimeout = false), 150);
                } else this.updateGameInstance((i) => i.playTimeout = false);

                const win = response.game.win, result = response.server_seed.result[0];

                $('#circle, #result').css({ transition: this.quick ? 'none' : '' });

                $('#circle').fadeIn('fast');
                $('#circle').css({
                    left: 'calc(' + (result - 10).toFixed(2) + '% - 30px)',
                    color: win ? 'green' : 'red'
                });

                //if(!this.quick) this.dice.throw(Math.floor(result));

                $('#result').toggleClass('lose', !win);
                $('#result').toggleClass('win', win);

                $('#result').text(result);
                $('#result').css({ opacity: 1 });
                $('#result').css({ left: 'calc(' + (result - 10) + '% - 30px)' });

                setTimeout(() => this.playSound(`/sounds/${win ? 'guessed' : 'lose'}.mp3`), 150);

                if(this.hideResultTimer != null) clearTimeout(this.hideResultTimer);
                this.hideResultTimer = setTimeout(function() {
                    $('#result').css({ opacity: 0 });
                    $('#circle').fadeOut('fast');
                }, 7000);

                Bus.$emit('dice:history:addEntry', { html: `<div class="text-${win ? 'success' : 'danger'}">${response.server_seed.result[0]}</div>` });
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'label', data: { label: this.$i18n.t('general.profit') } },
                    { name: 'profit' },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'quick', 'sound', 'stats'] } },
                    { name: 'history', data: { scrollable: true } }
                ]
            },
            getProfit(min, max) {
                let payout, range;
                if(min === max) payout = 99.0;
                else {
                    range = max - min;
                    payout = 99.0 / range;
                }

                $('.dice-payout').val(`x${payout.toFixed(2)}`);
                return payout * this.gameInstance.bet - this.gameInstance.bet;
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";
    @import "~jquery-ui/themes/base/slider.css";

    .game-dice {
        .diceCanvas {
            margin-top: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: -15px;
            width: calc(100% + 30px);
        }

        .dice-header {
            margin-bottom: 15px;
        }

        .dice-wrapper {
            margin: 50px 30px 70px;
            width: calc(100% - 60px);

            @include themed() {
                padding: 20px;
                background: lighten(t('body'), 2.5%);
                border-radius: 50px;
                border: 10px solid t('input');
            }
        }

        .d_slider-tooltip_container {
            position: absolute;
            top: -63px;
            text-align: center;
            z-index: 50;
            left: -20px;
            @include themed() {
                color: t('text');
                background: t('header');
            }
        }

        .d_slider-tooltip {
            display: inline-block;
            position: relative;
            @include themed() {
                border: 1px solid t('text');
            }
            text-decoration: none;
            padding: 15px 20px;
            min-width: 61px;
        }

        .d_slider-tooltip:after {
            content: '';
            display: block;
            position: absolute;
            width: 0;
            height: 0;
            border: 9px solid transparent;
            @include themed() {
                border-top-color: t('text');
            }
            bottom: calc(100% - 71px);
        }

        .d_slider-result {
            width: 55px;
            height: 35px;
            @include themed() {
                background: t('header');
                color: t('text');
            }

            border: 1px solid gray;
            position: absolute;
            top: -45px;
            z-index: 2;
            transition: border-color 0.3s ease, color 0.3s ease, left 1s ease, opacity 0.2s ease;

            display: flex !important;
            justify-content: center;
            align-items: center;

            left: -13px;
            padding: 8px;

            &:after {
                content: '';
                display: block;
                position: absolute;
                width: 0;
                height: 0;
                border: 9px solid transparent;
                border-top-color: gray;
                bottom: calc(100% - 51px);
                left: 50%;
                transform: translateX(-50%);
            }
        }

        .win {
            border-color: #27ae60;
            color: #62ca5b;
        }

        .lose {
            border-color: #e74c3c;
            color: #e74c3c;
        }

        .lose.d_slider-result:after {
            border-top-color: #e74c3c;
            color: #e74c3c;
        }

        .win.d_slider-result:after {
            border-top-color: #27ae60;
            color: #27ae60;
        }

        .d_slider-border {
            padding: 35px;
        }

        .d_slider-circle {
            width: 7px;
            height: 7px;
            background: white;
            border-radius: 12px;
            position: absolute;
            z-index: 4;
            left: 3px;
            top: 2px;
            transform: translateX(-50%);
            margin-left: calc(55px / 2);
            transition: left 1s ease;
        }

        .dice-append {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            right: 10px;
            border: none;
            @include themed() {
                background: lighten(t('input'), 2%);
                color: t('link');
                transition: color 0.3s ease, background 0.3s ease;
                &:hover {
                    background: lighten(t('input'), 3%);
                    color: t('link-hover');
                }
            }
            border-radius: 3px;
        }

        #slider-range, .ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default, .ui-button, html .ui-button.ui-state-disabled:hover, html .ui-button.ui-state-disabled:active {
            border: none;
        }

        .ui-corner-all {
            background: #e74c3c;
        }

        div.ui-slider-range.ui-widget-header {
            background: #27ae60;
        }

        .ui-slider-horizontal .ui-slider-handle {
            top: -0.4em;
            margin-left: -0.8em;
            width: 1.5em;
            height: 1.5em;
        }

        .ui-slider-handle {
            user-select: none;
            border-radius: 50%;
            border: none;
            outline: 0;
            background-color: white;

            transition: background-color 0.3s ease, color 0.3s ease;
            box-shadow: 0 0 5px rgba(black, 0.5);
            z-index: 10;

            i {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                font-size: 0.8em;
                color: black !important;
            }

            &:hover {
                background-color: darken(white, 1%);
                color: white;
                cursor: grab;
            }

            &:active {
                background-color: darken(white, 2.5%);
                cursor: grabbing;
            }
        }

        .ui-widget-content {
            border: none !important;
        }

        .dice-column {
            height: 100%;
        }

        .dice-footer-column {
            position: absolute;

            input {
                box-shadow: 0 0 1px 1px transparent;
                transition: box-shadow 0.3s ease;
            }

            input.error {
                box-shadow: 0 0 1px 1px #db4437;
            }
        }

        .game-history {
            height: 65px;
        }

        .history-dice {
            @include themed() {
                border: 1px solid rgba(t('text'), 0.25) !important;
            }
            border-radius: 3px;
            padding: 5px 10px;
            font-weight: 600;
        }

        .dice-wrapper-overview {
            margin: 110px 0 0 !important;
            position: unset !important;
            width: unset !important;
        }
    }

    @include media-breakpoint-down(md) {
        .dice-column {
            height: auto;
            order: 1;
        }

        .dice-footer-column {
            position: unset;
            height: 0;
        }

        .dice-wrapper {
            margin: 40px 0 60px !important;
            position: unset !important;
            width: unset !important;
        }
    }

    @media(max-width: 1500px) {
        .diceCanvas {
            canvas {
                width: 40% !important;
                height: unset !important;
            }
        }
    }
</style>
