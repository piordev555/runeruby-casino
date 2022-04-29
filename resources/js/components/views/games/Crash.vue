<template>
    <div>
        <custom-history></custom-history>
        <canvas width="800" height="600"></canvas>
    </div>
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

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

    class Ruler {

        constructor(vm, x1, y1, x2, y2, isDebug) {
            this.vm = vm;
            this.vm.ctx.beginPath();
            this.vm.ctx.lineWidth = 5;
            this.vm.ctx.strokeStyle = isDebug ? 'yellow' : (this.vm.theme === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)');
            this.vm.ctx.moveTo(x1, y1);
            this.vm.ctx.lineTo(x2, y2);
            this.vm.ctx.stroke();
        }

        addText(label, x, y, align = 'center') {
            const textSize = 15;
            this.vm.ctx.fillStyle = this.vm.theme === 'dark' ? 'rgba(255, 255, 255, 0.25)' : 'rgba(0, 0, 0, 0.5)';
            this.vm.ctx.textAlign = align;
            this.vm.ctx.font = `${textSize}px Open Sans`;
            this.vm.ctx.fillText(label, x, y + textSize);
        }

    }

    export default {
        data() {
            return {
                debug: false,

                width: 800,
                height: 600,

                in_progress_color: '#ffcc00',
                crash_color: '#ff1f44',
                cashoutTexts: [],
                crashed: false,
                placedBetThisRound: false,

                betValue: null,

                startTimestamp: 0,
                currentMultiplier: 1,
                autoCashout: 2,

                linePosX: 0,
                linePosY: 0,

                panZoomX: 50,
                panZoomY: 50,
                panZoomScale: 1,

                ctx: null,
                autoBetTake: null,

                betNextRoundMode: false,
                nextRoundBetData: null
            }
        },
        computed: {
            ...mapGetters(['theme', 'currency'])
        },
        methods: {
            lerp(v0, v1, t) {
                return (1.0 - t) * v0 + t * v1;
            },
            extendedAutoBetHandle(take) {
                this.autoBetTake = take;
            },
            drawBezierSplit(x0, y0, x1, y1, x2, y2, t0, t1) {
                this.ctx.beginPath();

                if(0.0 === t0 && t1 === 1.0) {
                    this.ctx.strokeStyle = this.crashed ? this.crash_color : this.in_progress_color;
                    this.ctx.moveTo(x0, y0);
                    this.ctx.quadraticCurveTo(x1, y1, x2, y2);
                } else if(t0 !== t1) {
                    let t00 = t0 * t0,
                        t01 = 1.0 - t0,
                        t02 = t01 * t01,
                        t03 = 2.0 * t0 * t01;

                    let nx0 = t02 * x0 + t03 * x1 + t00 * x2,
                        ny0 = t02 * y0 + t03 * y1 + t00 * y2;

                    t00 = t1 * t1;
                    t01 = 1.0 - t1;
                    t02 = t01 * t01;
                    t03 = 2.0 * t1 * t01;

                    let nx2 = t02 * x0 + t03 * x1 + t00 * x2,
                        ny2 = t02 * y0 + t03 * y1 + t00 * y2;

                    let nx1 = this.lerp(this.lerp(x0, x1, t0), this.lerp(x1, x2, t0), t1),
                        ny1 = this.lerp(this.lerp(y0, y1, t0), this.lerp(y1, y2, t0), t1);

                    if(this.debug) {
                        this.ctx.beginPath();
                        this.ctx.strokeStyle = 'black';
                        this.ctx.rect(nx1, ny1, 10, 10);
                        this.ctx.stroke();
                    }

                    this.ctx.strokeStyle = this.crashed ? this.crash_color : this.in_progress_color;
                    this.drawLineCircle(nx2, ny2);

                    this.ctx.moveTo(nx0, ny0);
                    this.ctx.lineWidth = 6;
                    this.ctx.quadraticCurveTo(nx1, ny1, nx2, ny2);
                }

                this.ctx.stroke();
                this.ctx.closePath();
            },
            drawLineCircle(x, y) {
                this.ctx.beginPath();
                this.ctx.arc(x, y, 10, 0, 2 * Math.PI, false);
                this.ctx.lineWidth = 3;
                this.ctx.strokeWidth = 3;
                this.ctx.fillStyle = this.crashed ? this.crash_color : this.in_progress_color;
                this.ctx.fill();

                this.linePosX = x;
                this.linePosY = y;
            },
            animatePathDrawing(x0, y0, x1, y1, x2, y2, duration) {
                let start = null;
                let step = (timestamp) => {
                    if (start === null)
                        start = timestamp;

                    let delta = timestamp - start,
                        progress = Math.min(delta / duration, 1);

                    // Clear canvas
                    this.ctx.clearRect(0, 0, this.width, this.height);

                    if(this.debug) {
                        const drawDebug = (x, y) => {
                            this.ctx.beginPath();
                            this.ctx.strokeStyle = 'red';
                            this.ctx.rect(x, y, 10, 10);
                            this.ctx.stroke();
                        };

                        drawDebug(x0, y0);
                        drawDebug(x1, y1);
                        drawDebug(x2, y2);
                    }

                    this.redrawCanvas();

                    this.drawBezierSplit(x0, y0, x1, y1, x2, y2, 0, progress);
                    if (!(progress < 1)) {
                        this.drawLineCircle(x2, y2);
                        this.panZoomX -= 1;
                        this.panZoomY += 1;
                    }

                    if(!this.crashed) window.requestAnimationFrame(step);
                };
                window.requestAnimationFrame(step);
            },
            drawGrid() {
                const scale = 1 / this.panZoomScale;
                let gridScale = 2 ** (Math.log2(128 * scale) | 0);
                let size = Math.max(this.width, this.height) * scale + gridScale * 2;
                let x = ((-this.panZoomX * scale - gridScale) / gridScale | 0) * gridScale;
                let y = ((-this.panZoomY * scale - gridScale) / gridScale | 0) * gridScale;
                this.applyPanZoom();

                this.ctx.lineWidth = 1;
                this.ctx.strokeStyle = this.theme === 'dark' ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.1)';
                this.ctx.beginPath();

                for (let i = 0; i < size; i += gridScale) {
                    this.ctx.moveTo(x + i, y);
                    this.ctx.lineTo(x + i, y + size);
                    this.ctx.moveTo(x, y + i);
                    this.ctx.lineTo(x + size, y + i);
                }

                this.ctx.setTransform(1, 0, 0, 1, 0, 0);
                this.ctx.stroke();

                this.ctx.globalAlpha = 1;

                this.ctx.clearRect(0, 0, 53, this.height);
                this.ctx.clearRect(0, 0, this.width, 53);
                this.ctx.clearRect(0, this.height - 53, this.width, this.height);
                this.ctx.clearRect(this.width - 53, 0, this.width, this.height);
            },
            redrawCanvas() {
                this.drawGrid();

                const textSize = 80;
                this.ctx.fillStyle = this.theme === 'dark' ? 'white' : 'black';
                this.ctx.font = `${textSize}px Open Sans`;
                this.ctx.textAlign = "center";
                this.ctx.textBaseline = "middle";
                this.ctx.fillText(`${parseFloat(this.currentMultiplier).toFixed(2)}x`, this.width / 2, this.height / 2);

                const rulerY = new Ruler(this, 50, 50, 50, this.height - 50);
                const rulerX = new Ruler(this, 48, this.height - 50, this.width - 50, this.height - 50);

                const secondsRunning = this.startTimestamp === 0 ? 0 : parseInt((+new Date() / 1000) - this.startTimestamp - 4);
                const timeOffset = secondsRunning < 10 ? 0 : secondsRunning - 9;
                const offset = 30;

                const multiplierOffset = (num, i) => {
                    return secondsRunning < 10 ? num : parseFloat(this.currentMultiplier) / i;
                };

                rulerY.addText(`x${(multiplierOffset(2.0, 1)).toFixed(1)}`, 50 / 2, 50);
                if(secondsRunning >= 10) {
                    rulerY.addText(`x${(multiplierOffset(1.8, 2)).toFixed(1)}`, 50 / 2, 50 + (((this.height - 50 + (offset / 2)) / 6)));
                    rulerY.addText(`x${(multiplierOffset(1.6, 3)).toFixed(1)}`, 50 / 2, 50 + (((this.height - 50 + (offset / 2)) / 6) * 2));
                    rulerY.addText(`x${(multiplierOffset(1.4, 4)).toFixed(1)}`, 50 / 2, 50 + (((this.height - 50 + (offset / 2)) / 6) * 3));
                    rulerY.addText(`x${(multiplierOffset(1.2, 5)).toFixed(1)}`, 50 / 2, 50 + (((this.height - 50 + (offset / 2)) / 6) * 4));
                    rulerY.addText(`x${(multiplierOffset(1.0, 6)).toFixed(1)}`, 50 / 2, 50 + offset + (((this.height - 50 - (offset / 2)) / 6) * 5));
                }

                rulerX.addText(`${2 + timeOffset}s`, (50 / 2) + (((this.width - 50 - (offset / 2)) / 5)), this.height - (50 - 10), 'right');
                rulerX.addText(`${4 + timeOffset}s`, (50 / 2) + (((this.width - 50 - (offset / 2)) / 5) * 2), this.height - (50 - 10), 'right');
                rulerX.addText(`${6 + timeOffset}s`, (50 / 2) + (((this.width - 50 - (offset / 2)) / 5) * 3), this.height - (50 - 10), 'right');
                rulerX.addText(`${8 + timeOffset}s`, (50 / 2) + (((this.width - 50 - (offset / 2)) / 5) * 4), this.height - (50 - 10), 'right');
                rulerX.addText(`${10 + timeOffset}s`, (50 / 2) + (((this.width - 50 - (offset / 2)) / 5) * 5), this.height - (50 - 10), 'right');

                if(this.debug) {
                    new Ruler(this, 50, 50, this.width - 50, 50, true);
                    new Ruler(this, this.width - 50, 50, this.width - 50, this.height - 50, true);
                }

                _.forEach(this.cashoutTexts, (e) => {
                    if(e.alpha <= 0) return;

                    this.ctx.fillStyle = `rgba(${this.theme === 'dark' ? '255, 255, 255' : '0, 0, 0'}, ${e.alpha})`;
                    this.ctx.font = `15px Open Sans`;
                    this.ctx.textAlign = "center";
                    this.ctx.textBaseline = "middle";
                    this.ctx.fillText(`${e.name} x${e.multiplier}`, e.x, e.y + (25 - (e.alpha * 10)));

                    e.alpha -= 0.01;
                });
            },
            multiplayerEvent(event, data) {
                switch (event) {
                    case 'MultiplayerBettingStateChange':
                        if(!this.placedBetThisRound && this.gameInstance.bettingType === 'manual' && !this.nextRoundBetData) this.setNextRoundBetMode(data.state);
                        break;
                    case 'MultiplayerGameBet':
                        Bus.$emit('sidebar:multiplayer:add', { user: data.user, game: data.game });
                        break;
                    case 'MultiplayerBetCancellation':
                        this.cashoutTexts.push({
                            name: data.user.name,
                            alpha: 1,
                            multiplier: parseFloat(this.currentMultiplier).toFixed(2),
                            x: this.linePosX,
                            y: this.linePosY
                        });
                        break;
                    case 'MultiplayerGameFinished':
                        this.finishExtended(false);
                        this.crashed = true;

                        let color = hex[0];
                        if(parseFloat(this.currentMultiplier) > 1) color = hex[1];
                        if(parseFloat(this.currentMultiplier) > 2) color = hex[2];
                        if(parseFloat(this.currentMultiplier) > 3) color = hex[3];
                        if(parseFloat(this.currentMultiplier) > 4) color = hex[4];
                        if(parseFloat(this.currentMultiplier) > 5) color = hex[5];
                        if(parseFloat(this.currentMultiplier) > 7) color = hex[6];
                        if(parseFloat(this.currentMultiplier) > 10) color = hex[7];
                        if(parseFloat(this.currentMultiplier) > 100) color = hex[8];
                        if(parseFloat(this.currentMultiplier) > 250) color = hex[9];

                        Bus.$emit('game:customHistory:add', {
                            text: `<div class="color" style="background: ${color[0]};"></div> ${parseFloat(this.currentMultiplier).toFixed(2)}x`,
                            style: '',
                            seed: {
                                serverSeed: data.server_seed,
                                clientSeed: data.client_seed,
                                nonce: data.nonce,
                                placement: 'bottom'
                            }
                        });

                        if(this.gameInstance.bettingType === 'auto' && this.gameInstance.game.autoBetSettings.state) this.gameInstance.game.autoBetSettings.next();
                        setTimeout(() => this.reset(), 5000);
                        break;
                    case 'MultiplayerTimerStart':
                        Bus.$emit('sidebar:multiplayer:clear');

                        this.placedBetThisRound = false;
                        this.setRoundTimer(6, () => {
                            this.startTimestamp = +new Date() / 1000;
                            this.startGame();
                        });

                        if(this.nextRoundBetData) {
                            this.updateGameInstance((i) => {
                                i.bet = this.nextRoundBetData.value;
                                this.$store.dispatch('setCurrency', this.nextRoundBetData.currency);
                                this.nextRoundBetData = null;
                            });
                            $('.play-button').click();
                        }

                        this.setNextRoundBetMode(false);
                        break;
                }
            },
            updateMultiplier() {
                if(this.gameInstance.bettingType === 'manual' && this.placedBetThisRound && !this.crashed)
                    $('.play-button').html(this.$i18n.t('general.take', { value: (this.betValue * parseFloat(this.currentMultiplier)).toFixed(this.currency.startsWith('local_') ? 2 : 8) })).removeClass('disabled');
            },
            startGame() {
                this.animatePathDrawing(50, this.height - 50, 350, this.height - 50, this.width - 50, 50, 10000);

                if(this.placedBetThisRound) {
                    $('.play-button').removeClass('disabled');
                    this.updateMultiplier();
                } else setTimeout(() => { if(!this.crashed) this.setNextRoundBetMode(true) }, 100);

                this.nextRoundBetData = null;

                const nextMultiplier = () => {
                    let timeInMilliseconds = 0, simulation = 1, suS = 0, diffS = (+new Date() / 1000) - this.startTimestamp;

                    while(timeInMilliseconds / 1000 < diffS) {
                        simulation += 0.05 / 15 + suS;
                        timeInMilliseconds += 2000 / 15 / 3;
                        if(simulation >= 5.5) {
                            suS += 0.05 / 15;
                            timeInMilliseconds += 4000 / 15 / 3;
                        }
                    }

                    //console.log(`sim ${simulation}`, `tMS ${timeInMilliseconds}`, `diffS ${diffS}`, `suS ${suS}`);
                    this.currentMultiplier = simulation.toFixed(2);
                    if(this.currentMultiplier > 1000) {
                        this.startTimestamp = +new Date();
                        this.currentMultiplier = 1;
                    }

                    if(this.placedBetThisRound) {
                        this.updateMultiplier();
                        if(parseFloat(this.currentMultiplier) >= this.autoCashout && parseFloat(this.currentMultiplier) >= 1.1 && !this.crashed) {
                            if(this.gameInstance.bettingType === 'manual') $('.play-button').click();
                            else this.autoBetTake();
                        }
                    }
                }

                let interval = setInterval(function() {
                    if(this.crashed) {
                        clearInterval(interval);
                        return;
                    }

                    nextMultiplier();
                }, 66);
            },
            setRoundTimer(seconds, callback) {
                seconds *= 1000;

                $('.history-crash').hide()
                    .css({ 'width': '100%' })
                    .fadeIn('fast')
                    .animate({ 'width': '0%' }, { duration: seconds, easing: 'linear' });

                setTimeout(() => {
                    this.crashed = false;
                    this.currentMultiplier = 1.0;
                    this.resetPanZoom();
                    callback();
                }, seconds);

                this.crashed = true;
            },
            clear() {
                this.ctx.clearRect(0, 0, this.width, this.height);
            },
            reset() {
                this.clear();
                this.redrawCanvas();
                this.drawLineCircle(50, this.height - 50);
            },
            resetPanZoom() {
                this.panZoomX = 50;
                this.panZoomY = 50;
                this.panZoomScale = 1;
            },
            applyPanZoom() {
                this.ctx.setTransform(this.panZoomScale, 0, 0, this.panZoomScale, this.panZoomX, this.panZoomY);
            },
            gameDataRetrieved(data) {
                Bus.$emit('crash:history:addEntry', { html: '' });

                $('.play-button').on('click', this.onPlayButtonClick);

                this.ctx = $('.game-crash').find('canvas')[0].getContext('2d');
                this.reset();

                this.startTimestamp = data.timestamp;
                this.startGame();

                _.forEach(data.players, (player) => Bus.$emit('sidebar:multiplayer:add', { user: player.user, game: player.game }));

                setTimeout(() => this.setNextRoundBetMode(true), 100);

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
                        text: `<div class="color" style="background: ${color[0]};"></div> ${parseFloat(m.multiplier).toFixed(2)}x`,
                        style: '',
                        seed: {
                            serverSeed: m.server_seed,
                            clientSeed: m.client_seed,
                            nonce: m.nonce,
                            placement: 'bottom'
                        }
                    });
                });
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'label', data: { label: this.$i18n.t('general.autoStop') } },
                    { name: 'input', data: { value: '2.00', callback: (v) => {
                        v = parseFloat(v);
                        if(!isNaN(v) && v >= 1.1 && v <= 1000) {
                            this.autoCashout = v;
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
            getClientData() {
                return {};
            },
            callback(response) {
                if(!response || this.placedBetThisRound) {
                    this.placedBetThisRound = false;
                    if(this.gameInstance.bettingType === 'manual') this.setNextRoundBetMode(true);
                    return;
                }

                this.placedBetThisRound = true;
                this.betValue = response.wager;
                if(this.gameInstance.bettingType === 'manual') $('.play-button').addClass('disabled');
            },
            setNextRoundBetMode(type) {
                if(this.gameInstance.bettingType !== 'manual') return;

                this.betNextRoundMode = type;

                if(type) $('.play-button').html(this.$i18n.t('general.bet_next_round'));
                else $('.play-button').html(this.$i18n.t('general.play'));
            },
            onPlayButtonClick() {
                if(this.placedBetThisRound || $('.play-button').hasClass('disabled')) return;
                if(this.betNextRoundMode) {
                    if(this.nextRoundBetData) {
                        this.setNextRoundBetMode(true);
                        this.nextRoundBetData = null;
                        return;
                    }

                    this.nextRoundBetData = {
                        currency: this.currency,
                        value: this.gameInstance.bet
                    };

                    $('.play-button').html(this.$i18n.t('general.cancel'));
                }
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-crash {
        .customHistory {
            top: 20px;
            transform: unset;
            flex-direction: unset;
            height: 45px;
            width: 70%;
            left: 15%;
            align-items: center;
            background: transparent;

            .element {
                padding: 0.955em 10px;

                @include themed() {
                    color: t('text');
                }

                .color {
                    width: 16px;
                    height: 16px;
                    border-radius: 50%;
                    margin-right: 6px;
                    margin-top: 3px;
                }
            }
        }

        canvas {
            margin-top: 35px;
        }

        .game-content-crash {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;

            @include themed() {
                .crashMultiplayerTable {
                    width: 100%;
                    border-top: 5px solid t('secondary');
                    margin-top: 5px;

                    display: flex;
                    flex-direction: column;

                    .user {
                        display: flex;
                        flex-direction: row;
                        align-items: center;
                        padding: 15px;
                        background: t('sidebar');
                        transition: background 0.3s ease;
                        cursor: pointer;

                        &:hover {
                            background: darken(t('sidebar'), 2%);
                        }

                        &:first-child {
                            margin-top: 5px;
                        }

                        &:nth-child(even) {
                            background: t('input') !important;

                            &:hover {
                                background: darken(t('input'), 2%) !important;
                            }
                        }

                        .avatar {
                            img {
                                border-radius: 50%;
                                width: 32px;
                                height: 32px;
                            }
                        }

                        .name {
                            margin-left: 5px;
                        }

                        .bet {
                            margin-left: auto;
                        }

                        .crash {
                            margin-left: 5px;
                        }
                    }
                }
            }

            .game-history {
                height: 10px;
                padding: 0 !important;
                position: relative;
                top: 0 !important;
                bottom: unset !important;

                .history-crash {
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

        @media(max-width: 1400px) {
            .game-content-crash {
                canvas {
                    width: 100%;
                }
            }
        }
    }
</style>
