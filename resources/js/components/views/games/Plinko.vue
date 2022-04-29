<template>
    <div>
        <div class="plinkoContainer">
            <div class="plinko"></div>
        </div>
        <custom-history></custom-history>
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
    }, colors = {
        8: [hex[9], hex[7], hex[4], hex[2], hex[0], hex[2], hex[4], hex[7], hex[9]],
        9: [hex[9], hex[7], hex[6], hex[5], hex[2], hex[2], hex[5], hex[6], hex[7], hex[9]],
        10: [hex[9], hex[8], hex[7], hex[5], hex[4], hex[1], hex[4], hex[5], hex[7], hex[8], hex[9]],
        11: [hex[9], hex[8], hex[7], hex[5], hex[4], hex[2], hex[2], hex[4], hex[5], hex[7], hex[8], hex[9]],
        12: [hex[9], hex[8], hex[7], hex[6], hex[5], hex[4], hex[1], hex[4], hex[5], hex[6], hex[7], hex[8], hex[9]],
        13: [hex[9], hex[8], hex[7], hex[6], hex[5], hex[4], hex[2], hex[2], hex[4], hex[5], hex[6], hex[7], hex[8], hex[9]],
        14: [hex[9], hex[8], hex[7], hex[6], hex[5], hex[4], hex[3], hex[2], hex[3], hex[4], hex[5], hex[6], hex[7], hex[8], hex[9]],
        15: [hex[9], hex[8], hex[7], hex[6], hex[5], hex[4], hex[3], hex[2], hex[2], hex[3], hex[4], hex[5], hex[6], hex[7], hex[8], hex[9]],
        16: [hex[9], hex[8], hex[7], hex[6], hex[5], hex[4], hex[3], hex[2], hex[1], hex[2], hex[3], hex[4], hex[5], hex[6], hex[7], hex[8], hex[9]]
    };

    export default {
        data() {
            return {
                speed: 300,
                difficulty: 'low',
                pins: 8
            }
        },
        watch: {
            difficulty() {
                this.reset();
            },
            pins() {
                this.reset();
            }
        },
        methods: {
            getDataFromObj(obj) {
                let step = parseInt(obj.attr('step'));
                let delta = parseInt(obj.attr('delta'));
                let target = $('.plinkoContainer .plinko').find('[row=' + step + '][pos=' + delta + ']');
                return {
                    top: target.css('top'),
                    left: target.css('left')
                }
            },
            drop(bucket) {
                let s = 1 / 3 / (this.pins + 2);
                let css = {
                    position: 'absolute',
                    top: (-100 * s) + '%',
                    left: '50%',
                    width: (100 * s) + '%',
                    height: (100 * s) + '%',
                    background: `hsl(${this.random(0, 360)}, 90%, 60%)`,
                    borderRadius: '50%',
                    animationDuration: (this.speed / 1000) + 's',
                    transform: 'translate(-50%, -125%)'
                };
                let attr = {
                    step: 0,
                    delta: 0,
                    target: bucket
                };
                let ball = $('<div>').css(css).attr(attr);
                $('.plinkoContainer .plinko').append(ball);

                const instance = this;

                const animationCallback = function() {
                    instance.animationCallback($(this), instance);
                };
                ball.animate(this.getDataFromObj(ball), this.speed, animationCallback);
            },
            animationCallback(obj) {
                obj.attr('step', parseInt(obj.attr('step')) + 1);
                let step = parseInt(obj.attr('step'));

                if (step !== this.pins + 1) {
                    let heading = (Math.random() < 0.5 ? 0 : 1);
                    let target = parseInt(obj.attr('target'));
                    let delta = parseInt(obj.attr('delta'));
                    if (delta === target) heading = 0;
                    else if (this.pins - step + 1 === target - delta) heading = 1;

                    let pin = $('.plinkoContainer .plinko').find(`[row=${step - 1}][pos=${(parseInt(obj.attr('delta')))}]`);
                    pin.addClass('pulsate');
                    setTimeout(function () {
                        pin.removeClass('pulsate');
                    }, 700);

                    obj.attr('delta', parseInt(obj.attr('delta')) + heading);
                    obj.removeAttr('heading').delay(this.speed / 10).queue(function () {
                        $(this).attr('heading', heading).dequeue();
                    });

                    const plinkoInstance = this;
                    const animationCallback = function () {
                        plinkoInstance.animationCallback($(this), plinkoInstance);
                    };
                    obj.animate(this.getDataFromObj(obj), this.speed, animationCallback);
                } else {
                    const plinkoInstance = this;
                    obj.removeAttr('heading').delay(this.speed / 10).queue(function () {
                        $(this).attr('heading', 2).dequeue();
                        plinkoInstance.playSound(`/sounds/plinko1.mp3`, 100);
                    }).delay(this.speed).queue(function () {
                        $(this).remove().dequeue();
                    });
                }
            },
            reset() {
                $('.plinkoContainer .plinko').empty();
                for (let i = 0; i <= this.pins; i++) {
                    for (let j = 0; j <= i; j++) {
                        let x = 0.5 + (j - (i / 2)) / (this.pins + 2);
                        let y = (i + 1) / (this.pins + 2);
                        let s = 1 / (i === this.pins ? 3 : 5) / (this.pins + 2);
                        let isBucket = i === this.pins;
                        let width = (isBucket ? (100 * s) * 2.2 : 100 * s);
                        let css = {
                            position: 'absolute',
                            top: (100 * y) + '%',
                            left: (100 * x) + '%',
                            width: width + '%',
                            height: (isBucket ? (100 * s) * 1.4 : (100 * s)) + '%',
                            background: (isBucket ? colors[this.pins][j][0] : '#66abf5'),
                            'border-bottom': (isBucket ? `${width / 2}px solid ${colors[this.pins][j][1]}` : 'none'),
                            borderRadius: (isBucket ? '3px' : '50%'),
                            transform: 'translate(-50%, -50%)'
                        };
                        let attr = {
                            row: i,
                            pos: j
                        };

                        const e = $('<div>').css(css).attr(attr).addClass(isBucket ? 'bucket' : 'pin');
                        if(isBucket) e.html('x'+this.gameInstance.game.data[this.difficulty][this.pins][j]);
                        $('.plinkoContainer .plinko').append(e);
                    }
                }
            },
            callback(response) {
                this.updateGameInstance((i) => i.playTimeout = false);
                this.drop(response.game.data.bucket);

                setTimeout(() => {
                    Bus.$emit('game:customHistory:add', {
                        text: `${response.game.multiplier}x`,
                        style: `background: ${colors[this.pins][response.game.data.bucket][0]}; border-bottom: 1px solid ${colors[this.pins][response.game.data.bucket][1]}`,
                        seed: {
                            serverSeed: response.server_seed.server_seed,
                            clientSeed: response.server_seed.client_seed,
                            nonce: response.server_seed.nonce,
                            placement: 'left'
                        }
                    })
                }, response.game.data.delay);

                if(this.gameInstance.bettingType === 'auto') {
                    this.updateGameInstance((i) => i.playTimeout = true);
                    setTimeout(() => this.updateGameInstance((i) => i.playTimeout = false), 100);
                }
            },
            getClientData() {
                return {
                    difficulty: this.difficulty,
                    pins: this.pins
                };
            },
            gameDataRetrieved() {
                this.reset();
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'label', data: { label: this.$i18n.t('general.difficulty.title') } },
                    { name: 'buttons', data: { buttons: [
                        { label: this.$i18n.t('general.difficulty.low'), callback: () => this.difficulty = 'low' },
                        { label: this.$i18n.t('general.difficulty.medium'), callback: () => this.difficulty = 'medium' },
                        { label: this.$i18n.t('general.difficulty.high'), callback: () => this.difficulty = 'high' },
                    ] } },
                    { name: 'label', data: { label: this.$i18n.t('general.pins') } },
                    { name: 'buttons', data: { buttons: [
                        { label: 8, callback: () => this.pins = 8 },
                        { label: 10, callback: () => this.pins = 10 },
                        { label: 12, callback: () => this.pins = 12 },
                        { label: 16, callback: () => this.pins = 16 },
                        { label: this.$i18n.t('general.edit'), type: 'input', input: { min: 8, max: 16 }, callback: (v) => this.pins = v }
                    ] } },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } }
                ];
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-plinko {
        .game-content-plinko {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .overview-render-target {
            .plinkoContainer {
                width: 365px;
                padding-bottom: 365px;
            }
        }

        .plinkoContainer {
            position: relative;
            width: 600px;
            display: block;
            padding-bottom: 600px;
            height: 0;
            overflow: hidden;

            @include themed() {
                .pin {
                    background: t('secondary') !important;
                }

                .pin.pulsate {
                    position: relative;

                    &:before {
                        content: '';
                        position: relative;
                        display: block;
                        width: 300%;
                        height: 300%;
                        box-sizing: border-box;
                        margin-left: -100%;
                        margin-top: -100%;
                        border-radius: 45px;
                        background-color: t('secondary') !important;
                        animation: pulse-plinko 1.25s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
                    }
                }
            }

            .plinko {
                position: absolute;
                width: 100%;
                height: 100%;

                .bucket {
                    color: black;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    cursor: default;
                    font-size: 0.9em;
                }

                [heading]:not([heading=""]) {
                    animation-timing-function: ease-in-out;
                    animation-iteration-count: 1;
                }

                [heading="0"] {
                    animation-name: bounceLeft;
                }

                [heading="1"] {
                    animation-name: bounceRight;
                }

                [heading="2"] {
                    animation-name: fallAway;
                }
            }
        }

        @keyframes pulse-plinko {
            0% {
                transform: scale(.33);
            }
            80%, 100% {
                opacity: 0;
            }
        }

        @keyframes bounceLeft {
            0%  { transform: translate(-50%, -125%) }
            50%  { transform: translate(-100%, -250%) }
            100%  { transform: translate(-50%, -125%) }
        }

        @keyframes bounceRight {
            0%  { transform: translate(-50%, -125%) }
            50%  { transform: translate(0%, -250%) }
            100%  { transform: translate(-50%, -125%) }
        }

        @keyframes fallAway {
            0%  { transform: translate(-50%, -125%) scale(1) }
            100%  { transform: translate(-50%, -50%) scale(0) }
        }

        @media(max-width: 1400px) {
            .plinkoContainer {
                width: 335px;
                padding-bottom: 335px;
            }

            .plinko {
                .bucket {
                    font-size: 50% !important;
                }
            }
        }

        @include media-breakpoint-down(md) {
            .plinkoContainer {
                width: 85vmin;
                padding-bottom: 85vmin;
            }

            .game-content-plinko {
                .plinkoCustomHistory {
                    right: 10px;
                    top: 10px;
                    transform: unset !important;
                }
            }
        }
    }
</style>
