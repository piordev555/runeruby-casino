<template>
    <div class="diamonds-grid">
        <div data-diamonds-slot="1"><div class="shadow" data-shadow-id="1"></div></div>
        <div data-diamonds-slot="2"><div class="shadow" data-shadow-id="2"></div></div>
        <div data-diamonds-slot="3"><div class="shadow" data-shadow-id="3"></div></div>
        <div data-diamonds-slot="4"><div class="shadow" data-shadow-id="4"></div></div>
        <div data-diamonds-slot="5"><div class="shadow" data-shadow-id="5"></div></div>
    </div>
</template>

<script>
    import Bus from '../../../bus';
    import gsap from 'gsap';

    export default {
        methods: {
            callback(response) {
                this.clear();
                this.chain(5, 200, (i) => {
                    this.setDiamond(i, response.game.data.diamonds[i - 1], $.grep(response.game.data.diamonds, function(e) {
                        return e === response.game.data.diamonds[i - 1];
                    }).length >= 2);
                    if(i === 5) {
                        $(`[data-m="${response.game.multiplier.toFixed(2)}"]`).addClass('highlight');
                        this.updateGameInstance((i) => i.playTimeout = false);
                        this.resultPopup(response.game);
                    }
                })
            },
            getClientData() {
                return {};
            },
            setDiamond(slot, color, highlight) {
                $('.game-container').find(`[data-diamonds-slot="${slot}"]`).addClass('dropShadow').addClass(highlight ? color : '')
                    .append(`<img src="/img/diamonds/${color}.svg" alt>`).hide().fadeIn(300);

                const down = () => {
                    gsap.to(`[data-diamonds-slot="${slot}"] img`, {
                        duration: 0.45,
                        y: '+=4px',
                        rotate: 1.0 + (this.random(1, 2) / 10),
                        ease: 'sine.out',
                        onComplete: up
                    });
                    gsap.to(`[data-shadow-id="${slot}"]`, {
                        scale: 0.95,
                        duration: 0.45,
                        ease: 'sine.out'
                    });
                };

                const up = () => {
                    gsap.to(`[data-diamonds-slot="${slot}"] img`, {
                        duration: 0.4,
                        y: '-=4px',
                        rotate: 0 - (this.random(1, 2) / 10),
                        ease: 'sine.out',
                        onComplete: down
                    });
                    gsap.to(`[data-shadow-id="${slot}"]`, {
                        scale: 0.9,
                        duration: 0.4,
                        ease: 'sine.out'
                    });
                };

                up();

                this.playSound(`/sounds/open${this.random(1, 2)}.mp3`);
            },
            clear() {
                gsap.killTweensOf(['[data-diamonds-slot="1"] img', '[data-diamonds-slot="2"] img', '[data-diamonds-slot="3"] img', '[data-diamonds-slot="4"] img', '[data-diamonds-slot="5"] img',
                    '[data-shadow-id="1"]', '[data-shadow-id="2"]', '[data-shadow-id="3"]', '[data-shadow-id="4"]', '[data-shadow-id="5"]']);
                $('.game-container').find(`[data-diamonds-slot]`).attr('class', '').find('img').fadeOut(300, function() {
                    $(this).remove();
                });
            },
            gameDataRetrieved() {
                const full = 1, double = 0.5, empty = 0.2;

                Bus.$emit('diamonds:history:addEntry', { html: `<div data-m="${this.applyHouseEdge(50.00)}">${this.applyHouseEdge(50.00)}x (0.04%)<div><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i></div></div>`, type: 'append' });
                Bus.$emit('diamonds:history:addEntry', { html: `<div data-m="${this.applyHouseEdge(5.00)}">${this.applyHouseEdge(5.00)}x (1.25%)<div><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i></div></div>`, type: 'append' });
                Bus.$emit('diamonds:history:addEntry', { html: `<div data-m="${this.applyHouseEdge(4.00)}">${this.applyHouseEdge(4.00)}x (2.50%)<div><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${double}"></i><i class="fad fa-gem" style="opacity: ${double}"></i></div></div>`, type: 'append' });
                Bus.$emit('diamonds:history:addEntry', { html: `<div data-m="${this.applyHouseEdge(3.00)}">${this.applyHouseEdge(3.00)}x (12.49%)<div><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i></div></div>`, type: 'append' });
                Bus.$emit('diamonds:history:addEntry', { html: `<div data-m="${this.applyHouseEdge(2.00)}">${this.applyHouseEdge(2.00)}x (18.74%)<div><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${double}"></i><i class="fad fa-gem" style="opacity: ${double}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i></div></div>`, type: 'append' });
                Bus.$emit('diamonds:history:addEntry', { html: `<div data-m="${this.applyHouseEdge(0.10)}">${this.applyHouseEdge(0.10)}x (49.98%)<div><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${full}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i></div></div>`, type: 'append' });
                Bus.$emit('diamonds:history:addEntry', { html: `<div data-m="0.00">0.00x (14.99%)<div><i class="fad fa-gem" style="opacity: ${empty}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i><i class="fad fa-gem" style="opacity: ${empty}"></i></div></div>`, type: 'append' });
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } },
                    { name: 'history', data: { scrollable: true } }
                ];
            }
        }
    }
</script>

<style lang="scss">
    @import 'resources/sass/variables';

    $blue: #0e3d8c;
    $blue_border: #1e6eef;
    $green: #006b01;
    $green_border: #17d118;
    $light_blue: #02858b;
    $light_blue_border: #03bfc7;
    $pink: #ab186f;
    $pink_border: #ff4fb6;
    $purple: #430bb0;
    $purple_border: #7633fa;
    $red: #991029;
    $red_border: #ff1c44;
    $yellow: #81670e;
    $yellow_border: #fec916;

    .game-diamonds {
        .history-diamonds {
            cursor: default;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 5px 10px;
            border-radius: 3px;
            text-align: center;

            div {
                transition: color 0.3s ease;
                @include themed() {
                    color: rgba(t('text'), 0.4) !important;
                }
            }

            &:last-child {
                margin-right: 15px !important;
            }
        }

        .history-diamonds div.highlight {
            @include themed() {
                color: t('text') !important;
            }
        }

        .game-content-diamonds {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .diamonds-grid {
            display: flex;

            [data-diamonds-slot] {
                display: inline-flex;
                width: 160px;
                height: 85px;
                margin-right: 30px;
                border-radius: 3px;
                position: relative;
                @include themed() {
                    background: t('body');
                    border-bottom: 15px solid darken(t('body'), 5%);
                    .shadow {
                        content: '';
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                        transition: opacity 0.3s ease;
                        width: 60%;
                        height: 40%;
                        background: rgba(black, 0.2);
                        border-radius: 50%;
                        opacity: 0;
                    }
                }
                transition: background 0.3s ease, border-color 0.3s ease;

                &:last-child {
                    margin-right: 0;
                }

                img {
                    z-index: 1;
                    width: 100%;
                    height: 250%;
                    position: absolute;
                    top: -160%;
                }
            }

            [data-diamonds-slot].blue {
                background: $blue;
                border-color: $blue_border;
            }

            [data-diamonds-slot].green {
                background: $green;
                border-color: $green_border;
            }

            [data-diamonds-slot].light_blue {
                background: $light_blue;
                border-color: $light_blue_border;
            }

            [data-diamonds-slot].pink {
                background: $pink;
                border-color: $pink_border;
            }

            [data-diamonds-slot].purple {
                background: $purple;
                border-color: $purple_border;
            }

            [data-diamonds-slot].red {
                background: $red;
                border-color: $red_border;
            }

            [data-diamonds-slot].yellow {
                background: $yellow;
                border-color: $yellow_border;
            }

            [data-diamonds-slot].dropShadow {
                .shadow {
                    opacity: 1;
                }
            }
        }
    }

    .overview-render-target {
        .diamonds-grid {
            width: 100%;

            [data-diamonds-slot] {
                width: 100%;
                height: 65px;
                margin-right: 8px;

                img {
                    width: 60%;
                    height: 400%;
                    left: 20%;
                    position: absolute;
                    top: -145%;
                }

                .shadow {
                    opacity: 0 !important;
                }
            }
        }
    }

    @media(max-width: 1600px) {
        .diamonds-grid {
            [data-diamonds-slot] {
                width: 100px;
                height: 65px;
                margin-right: 20px;
            }
        }
    }

    @media(max-width: 1200px) {
        .diamonds-grid {
            [data-diamonds-slot] {
                width: 71px;
                height: 55px;
                margin-right: 5px;
            }
        }
    }

    @include media-breakpoint-down(md) {
        .game-content-diamonds {
            padding: 90px 0 !important;
            padding-bottom: 120px !important;

            .diamonds-grid {
                [data-diamonds-slot] {
                    width: 15vw;
                    height: 55px;
                    margin-right: 3px;
                }
            }
        }
    }
</style>
