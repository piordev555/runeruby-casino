<template>
    <div class="coin front" data-coin><div class="front"></div><div class="back"></div></div>
</template>

<script>
    import Bus from '../../../bus';
    import gsap from 'gsap';

    export default {
        methods: {
            getClientData() {
                return {};
            },
            callback(response) {
                if(this.isExtendedGameStarted()) {
                    $('.history-coinflip').remove();
                    $('.coinflip-color').removeClass('disabled');
                } else {
                    $('.coinflip-color').addClass('disabled');
                    if(response) this.resultPopup(response.game);
                }
            },
            flip(side, callback) {
                $('[data-coin]').attr('class', `coin ${side}`);

                this.playSound('/sounds/flip.mp3');

                const i = gsap.timeline({
                    onComplete: callback
                });

                i.set('.game-content-coinflip', { perspective: 400, transformStyle: 'preserve3d' });
                i.fromTo('[data-coin]', .4, { rotationY: -720 }, { rotationY: -190, z: 120, ease: 'easeOut' });
                i.to('[data-coin]', { duration: .1, rotationY: -170, ease: 'easeOut' });
                i.to('[data-coin]', { duration: .4, rotationY: 0, z: -15, ease: 'easeIn' });
                i.to('[data-coin]', { duration: .1, z: 0, ease: 'easeIn' });
            },
            restore(game) {
                $('.coinflip-color').removeClass('disabled');
                _.forEach(game.history, (color) => Bus.$emit('coinflip:history:addEntry', { html: `<div class="coin ${color === 'yellow' ? 'front' : 'back'}"><div class="front"></div><div class="back"></div></div>` }));
            },
            turn(color, callback = null) {
                $('.coinflip-color').toggleClass('disabled', true);

                this.sendTurn({ color: color }, (response) => {
                    this.flip(response.data.color === 'yellow' ? 'front' : 'back', () => {
                        if(callback != null) callback(response);

                        if(response.type === 'lose') {
                            $('.coinflip-color').toggleClass('disabled', true);
                            this.finishExtended(false);
                            this.playSound('/sounds/lose.mp3');
                            this.resultPopup(response.game);
                        } else {
                            $('.coinflip-color').removeClass('disabled');
                            this.playSound('/sounds/guessed.mp3');
                            Bus.$emit('coinflip:history:addEntry', { html: `<div class="coin ${response.data.color === 'yellow' ? 'front' : 'back'}"><div class="front"></div><div class="back"></div></div>` });
                        }
                    });
                }, () => {
                    if(callback != null) callback({ game: { status: 'lose' } });
                });
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'label', data: { label: this.$i18n.t('general.color') } },
                    { name: 'buttons', data: { buttons: [
                        { class: 'coinflip-color disabled', label: this.$i18n.t('general.yellow'), isAction: true, preventMark: true, callback: () => this.turn('yellow') },
                        { class: 'coinflip-color disabled', label: this.$i18n.t('general.blue'), isAction: true, preventMark: true, callback: () => this.turn('blue') },
                    ] } },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } },
                    { name: 'history', data: { scrollable: true } }
                ];
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-coinflip {
        .history-coinflip, .overview-render-target {
            .coin {
                width: 32px;
                height: 32px;

                .front, .back {
                    &:after {
                        @include themed() {
                            border: 3px solid t('header');
                        }
                    }
                }
            }
        }

        .overview-render-target {
            .coin {
                display: inline-flex;
                margin-right: 10px;
                flex-wrap: nowrap;
                white-space: nowrap;
            }
        }

        .game-content-coinflip {
            display: flex;
            align-items: center;
            justify-content: center;

            .coin {
                margin-bottom: 65px;
            }

            .game-history {
                height: 65px;
            }
        }

        .coin {
            width: 200px;
            height: 200px;
            transform-style: preserve-3d;
            backface-visibility: hidden;
            perspective: 400px;
            position: relative;

            .front {
                background: #ffd000;
            }

            .back {
                background: #4986f5;
            }

            .front, .back {
                position: absolute;
                transform-style: preserve-3d;
                backface-visibility: hidden;
                width: 100%;
                height: 100%;
                border-radius: 50%;

                &:after {
                    content: "";
                    background: transparent;
                    width: 90%;
                    height: 90%;
                    position: absolute;
                    border-radius: 50%;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    @include themed() {
                        border: 8px solid t('header');
                    }
                }
            }
        }

        .coin.front {
            .front {
                transform: translateZ(1px);
            }

            .back {
                transform: translateZ(-1px) rotateY(180deg);
            }
        }

        .coin.back {
            .front {
                transform: translateZ(-1px) rotateY(180deg);
            }

            .back {
                transform: translateZ(1px);
            }
        }
    }
</style>
