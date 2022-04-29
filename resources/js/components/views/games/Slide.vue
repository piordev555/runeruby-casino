<template>
    <div>
        <custom-history></custom-history>

        <div class="slide">
        <div class="slide_container_line"></div>
        <div class="slide_container">
            <div class="slide_container_row"></div>
        </div>
        </div>
    </div>
</template>

<script>
    import Bus from '../../../bus';

    const hex = {
        0: ['#ffc000', '#997300'],
        1: ['#ffa808', '#a16800'],
        2: ['#ffa808', '#a95b00'],
        3: ['#ff9010', '#a95b00'],
        4: ['#ff7818', '#914209'],
        5: ['#ff6020', '#b93500'],
        6: ['#ff4827', '#c01d00'],
        7: ['#ff302f', '#c80100'],
        8: ['#ff1837', '#91071c'],
        9: ['#ff003f', '#990026']
    };

    export default {
        data() {
            return {
                target_payout: 2
            }
        },
        methods: {
            setRoundTimer(seconds) {
                seconds *= 1000;

                $('.history-slide').hide().stop().css({'width': '100%'}).fadeIn('fast')
                    .animate({'width': '0%'}, {duration: seconds, easing: 'linear'});
            },
            spin(id, size) {
                $(".slide_container_row").css({
                    position: 'relative',
                    left: 0
                });

                const amount = size * 2,
                    gw = $('.slide_card').outerWidth(true),
                    center = gw / 2,
                    containerCenter = $('.slide_container').outerWidth(true) / 2;

                $('.slide_container_row').stop().animate({ left: `-=${amount * gw + (id * gw) - containerCenter + center}` }, 6000);
            },
            getClientData() {
                return {
                    target: this.target_payout
                }
            },
            gameDataRetrieved(data) {
                Bus.$emit('slide:history:addEntry', { html: '' });

                _.forEach(data.history, (m) => {
                    let color = hex[0];
                    if(m.multiplier > 250) color = hex[9];
                    else if(m.multiplier > 100) color = hex[8];
                    else if(m.multiplier > 10) color = hex[7];
                    else if(m.multiplier > 7) color = hex[6];
                    else if(m.multiplier > 5) color = hex[5];
                    else if(m.multiplier > 4) color = hex[4];
                    else if(m.multiplier > 3) color = hex[3];
                    else if(m.multiplier > 2) color = hex[2];
                    else if(m.multiplier > 1) color = hex[1];

                    Bus.$emit('game:customHistory:add', {
                        text: `${parseFloat(m.multiplier).toFixed(2)}x`,
                        style: `background: ${color[0]}; border-bottom: 1px solid ${color[1]}`,
                        seed: {
                            serverSeed: m.server_seed,
                            clientSeed: m.client_seed,
                            nonce: m.nonce,
                            placement: 'right'
                        }
                    });
                });

                _.forEach(data.data.slides, (slide) => $('.slide_container_row').append(this.card(slide)));
                this.clone();

                if(data.timestamp === -1) this.spin(data.data.index, data.data.slides.length);
                else {
                    const now = +new Date() / 1000;
                    const left = parseInt(now - data.timestamp);
                    if(left >= 0 && left <= 6) this.setRoundTimer(left);
                }
            },
            card(mul) {
                return `<div class="slide_card" data-slide="${mul}">
                    <div class="hexagon">x${mul.toFixed(2)}</div>
                    <div class="slide_card_footer"></div>
                </div>`;
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'label', data: { label: this.$i18n.t('general.target_payout') } },
                    { name: 'input', data: { value: '2.00', callback: (v) => {
                        v = parseFloat(v);
                        if(!isNaN(v) && v >= 1.01) {
                            this.target_payout = v;
                            return true;
                        }
                        return false;
                    } } },
                    { name: 'auto-bets' },
                    { name: 'multiplayer-table' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } },
                    { name: 'history' }
                ];
            },
            callback(response) {
                if(this.gameInstance.bettingType === 'manual') $('.play-button').addClass('disabled');
            },
            clone() {
                for(let i = 0; i < 2; i++) $('.slide_container_row').append($('.slide_container_row').children().clone(true, true));
            },
            multiplayerEvent(event, data) {
                switch (event) {
                    case 'MultiplayerBettingStateChange':
                        if(this.gameInstance.bettingType === 'manual') $('.play-button').toggleClass('disabled', !data.state);
                        break;
                    case 'MultiplayerGameBet':
                        Bus.$emit('sidebar:multiplayer:add', { user: data.user, game: data.game });
                        break;
                    case 'MultiplayerGameFinished':
                        $('.slide_container_row').html('');
                        _.forEach(data.data.slides, (slide) => $('.slide_container_row').append(this.card(slide)));
                        this.clone();

                        this.spin(data.data.index, data.data.slides.length);
                        setTimeout(() => {
                            $(`[data-slide="${data.data._result}"]`).addClass('selected');

                            let color = hex[0];
                            if(parseFloat(data.data._result) > 1) color = hex[1];
                            if(parseFloat(data.data._result) > 2) color = hex[2];
                            if(parseFloat(data.data._result) > 3) color = hex[3];
                            if(parseFloat(data.data._result) > 4) color = hex[4];
                            if(parseFloat(data.data._result) > 5) color = hex[5];
                            if(parseFloat(data.data._result) > 7) color = hex[6];
                            if(parseFloat(data.data._result) > 10) color = hex[7];
                            if(parseFloat(data.data._result) > 100) color = hex[8];
                            if(parseFloat(data.data._result) > 250) color = hex[9];

                            Bus.$emit('game:customHistory:add', {
                                text: `${parseFloat(data.data._result).toFixed(2)}x`,
                                style: `background: ${color[0]}; border-bottom: 1px solid ${color[1]}`,
                                seed: {
                                    serverSeed: data.server_seed,
                                    clientSeed: data.client_seed,
                                    nonce: data.nonce,
                                    placement: 'right'
                                }
                            });
                        }, 6000);
                        break;
                    case 'MultiplayerTimerStart':
                        Bus.$emit('sidebar:multiplayer:clear');
                        this.setRoundTimer(6);
                        break;
                }
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-slide {
        .slide {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .customHistory {
            left: 20px;
            top: 25px;
            transform: unset;
            z-index: 60;
        }

        .game-content-slide {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;

            .slide_container_line {
                position: absolute;
                z-index: 4;
                background: url('/img/misc/slide.svg') no-repeat center;
                background-size: cover;
                width: 20px;
                height: 185px;
                margin-top: 155px;
            }

            .game-history {
                z-index: 55;
                position: absolute !important;
            }

            .slide_container {
                display: flex;
                overflow: hidden;
                width: calc(100% + 30px);
                max-width: 50vw;

                &:before, &:after {
                    content: '';
                    position: absolute;
                    top: 0;
                    width: 20%;
                    height: 100%;
                    z-index: 3;
                }

                @include themed() {
                    &:before {
                        left: 0;
                        background: linear-gradient(to left, transparent, t('sidebar'));
                    }

                    &:after {
                        right: 0;
                        background: linear-gradient(to right, transparent, t('sidebar'));
                    }
                }

                .slide_container_row {
                    display: flex;
                    white-space: nowrap;
                    flex-wrap: nowrap;

                    @include themed() {
                        .slide_card {
                            width: 110px;
                            height: 230px;
                            display: flex;
                            margin-right: 10px;
                            border-radius: 3px;
                            user-select: none;
                            position: relative;
                            background: #1e1d23;
                            border: 3px solid transparent;
                            transition: border-color 0.3s ease;

                            .slide_card_footer {
                                position: absolute;
                                left: 0;
                                bottom: 0;
                                width: 100%;
                                height: 20%;
                                background: #18171c;
                                transition: background 0.3s ease;
                            }

                            .hexagon {
                                position: absolute;
                                left: 50%;
                                top: 14px;
                                width: 70%;
                                height: 85px;
                                transform: translateX(-50%);
                                background: url(/img/misc/hexagon.svg) no-repeat center;
                                background-size: cover;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: white;
                            }

                            &:last-child {
                                margin-right: 0;
                            }
                        }

                        .slide_card.selected {
                            border-color: darken(#18171c, 3%) !important;

                            .slide_card_footer {
                                background: darken(#18171c, 3%) !important;
                            }
                        }
                    }
                }
            }

            .slideCustomHistory {
                position: absolute;
                left: 30px;
                top: 30px;
            }

            .game-history {
                height: 10px;
                padding: 0 !important;
                position: relative;
                top: 0 !important;
                bottom: unset !important;

                .history-slide {
                    position: absolute;
                    left: 0;
                    top: 0;
                    height: 100%;
                    width: 0;
                    will-change: width;

                    span {
                        position: absolute;
                        right: 5px;
                        top: 50%;
                        transform: translateY(-50%);
                        color: white;
                        font-size: 0.65em;
                    }

                    @include themed() {
                        background: t('secondary');
                    }
                }
            }
        }

        @include media-breakpoint-down(md) {
            .game-content-slide {
                .slideCustomHistory {
                    left: 10px;
                    top: 10px;
                    transform: unset !important;
                    height: 180px;
                }

                .slide_container {
                    max-width: 80vw;
                }

                .slide_container_line {
                    margin-top: 105px;
                }
            }
        }
    }
</style>
