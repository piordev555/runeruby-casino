<template>
    <div>
        <canvas class="roulette-wheel" width="600" height="600"></canvas>
        <div class="roulette-field">
            <div class="header">
                {{ $t('general.wager') }}: <span id="bet">{{ totalBet.toFixed(currency.startsWith('local_') ? 2 : 8) }}</span>
                <div class="right">
                    <button class="btn btn-primary" @click="clear">{{ $t('general.clear') }}</button>
                </div>
            </div>
            <div class="content">
                <div class="side">
                    <div class="chip green" data-chip="0">
                        0
                    </div>
                </div>
                <div class="numeric">
                    <div class="r">
                        <div class="chip red" data-chip="3">
                            3
                        </div>
                        <div class="chip black" data-chip="6">
                            6
                        </div>
                        <div class="chip red" data-chip="9">
                            9
                        </div>
                        <div class="chip red" data-chip="12">
                            12
                        </div>
                        <div class="chip black" data-chip="15">
                            15
                        </div>
                        <div class="chip red" data-chip="18">
                            18
                        </div>
                        <div class="chip red" data-chip="21">
                            21
                        </div>
                        <div class="chip black" data-chip="24">
                            24
                        </div>
                        <div class="chip red" data-chip="27">
                            27
                        </div>
                        <div class="chip red" data-chip="30">
                            30
                        </div>
                        <div class="chip black" data-chip="33">
                            33
                        </div>
                        <div class="chip red" data-chip="36">
                            36
                        </div>
                    </div>
                    <div class="r">
                        <div class="chip black" data-chip="2">
                            2
                        </div>
                        <div class="chip red" data-chip="5">
                            5
                        </div>
                        <div class="chip black" data-chip="8">
                            8
                        </div>
                        <div class="chip black" data-chip="11">
                            11
                        </div>
                        <div class="chip red" data-chip="14">
                            14
                        </div>
                        <div class="chip black" data-chip="17">
                            17
                        </div>
                        <div class="chip black" data-chip="20">
                            20
                        </div>
                        <div class="chip red" data-chip="23">
                            23
                        </div>
                        <div class="chip black" data-chip="26">
                            26
                        </div>
                        <div class="chip black" data-chip="29">
                            29
                        </div>
                        <div class="chip red" data-chip="32">
                            32
                        </div>
                        <div class="chip black" data-chip="35">
                            35
                        </div>
                    </div>
                    <div class="r">
                        <div class="chip red" data-chip="1">
                            1
                        </div>
                        <div class="chip black" data-chip="4">
                            4
                        </div>
                        <div class="chip red" data-chip="7">
                            7
                        </div>
                        <div class="chip black" data-chip="10">
                            10
                        </div>
                        <div class="chip black" data-chip="13">
                            13
                        </div>
                        <div class="chip red" data-chip="16">
                            16
                        </div>
                        <div class="chip red" data-chip="19">
                            19
                        </div>
                        <div class="chip black" data-chip="22">
                            22
                        </div>
                        <div class="chip red" data-chip="25">
                            25
                        </div>
                        <div class="chip black" data-chip="28">
                            28
                        </div>
                        <div class="chip black" data-chip="31">
                            31
                        </div>
                        <div class="chip red" data-chip="34">
                            34
                        </div>
                    </div>
                </div>
                <div class="side">
                    <div class="chip bordered" data-chip="row1">
                        2:1
                    </div>
                    <div class="chip bordered" data-chip="row2">
                        2:1
                    </div>
                    <div class="chip bordered" data-chip="row3">
                        2:1
                    </div>
                </div>
            </div>
            <div class="content">
                <div class="side"></div>
                <div class="numeric">
                    <div class="r">
                        <div class="chip bordered" data-chip="1-12">
                            {{ $t('general.to', { 1: 1, 2: 12 }) }}
                        </div>
                        <div class="chip bordered" data-chip="13-24">
                            {{ $t('general.to', { 1: 13, 2: 24 }) }}
                        </div>
                        <div class="chip bordered" data-chip="25-36">
                            {{ $t('general.to', { 1: 25, 2: 36 }) }}
                        </div>
                    </div>
                    <div class="r">
                        <div class="chip bordered" data-chip="1-18">
                            {{ $t('general.to', { 1: 1, 2: 18 }) }}
                        </div>
                        <div class="chip bordered" data-chip="even">
                            {{ $t('general.even') }}
                        </div>
                        <div class="chip red" data-chip="red"></div>
                        <div class="chip black" data-chip="black"></div>
                        <div class="chip bordered" data-chip="odd">
                            {{ $t('general.odd') }}
                        </div>
                        <div class="chip bordered" data-chip="19-36">
                            {{ $t('general.to', { 1: 19, 2: 36 }) }}
                        </div>
                    </div>
                </div>
                <div class="side"></div>
            </div>
        </div>
        <custom-history></custom-history>
    </div>
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

    const FULL_CIRCLE = 2 * Math.PI, _slotTotal = 37;
    const GREEN = '#7FBD35', DARK_GREEN = '#71A82F', RED = '#E7586A', DARK_RED = '#CC4F5E', BLACK = '#272933', DARK_BLACK = '#24252E';

    const _outerColors = [
        GREEN,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
        RED, BLACK, RED, BLACK,
    ], _innerColors = [
        DARK_GREEN,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK,
        DARK_RED, DARK_BLACK, DARK_RED, DARK_BLACK
    ], _arcAngle = FULL_CIRCLE / _slotTotal;

    const _numbers = [
        '0', '32', '15', '19',
        '4', '21', '2', '25',
        '17', '34', '6', '27',
        '13', '36', '11', '30',
        '8', '23', '10', '5',
        '24', '16', '33', '1',
        '20', '14', '31', '9',
        '22', '18', '29', '7',
        '28', '12', '35', '3', '26',
    ];

    export default {
        mounted() {
            this.canvas = $('.game-roulette').find('.roulette-wheel')[0];
            this.ctx = this.canvas.getContext('2d');

            this.init();

            Bus.$on('sidebar:chipSelect', (chip) => {
                this.chip = chip.value;
                this.chipDisplayValue = chip.displayValue;
            });

            if(this.currency.startsWith('local_')) {
                this.chipDisplayValue = 1000;
                this.chip = 1000;
            }
        },
        computed: {
            ...mapGetters(['quick', 'currency'])
        },
        methods: {
            clear() {
                this.bet = {};
                this.totalBet = 0;
                $('.bet-stack').fadeOut('fast', function() {
                    $(this).remove();
                });
            },
            drawFrame() {
                if (this._ballSettingsShowBall) {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.redrawBackground();

                    this.ctx.translate(this._centerX, this._centerY);

                    this._ballSettingsSpinTimeElapsed = Date.now() - this._ballSettingsSpinStartTime;

                    let f = this._ballSettingsSpinTimeElapsed / this._ballSettingsSpinTotalTime;

                    f = f > 1 ? 1 : f;

                    this.ctx.rotate((this._worldAngle + this._ballSettingsFinalPosition) * f);

                    this.ctx.beginPath();
                    this.ctx.fillStyle = '#ffffff';

                    let vertDeceleration, g = this._ballSettingsSpinTimeElapsed / (this._ballSettingsSpinTotalTime);

                    g = g > 1 ? 1 : g;
                    if (g < 0.1) vertDeceleration = 1;
                    else vertDeceleration = (1 - g) * Math.abs(Math.sin(5 * g * g * this._worldAngle));

                    let x = this._ballVertOffset + this._ballVertRange * vertDeceleration;

                    this.ctx.arc(x, 0, this._ballRadius, 0, FULL_CIRCLE);
                    this.ctx.fill();

                    this.ctx.setTransform(1, 0, 0, 1, 0, 0);
                }

                requestAnimationFrame(this.drawFrame);
            },
            findIndexOfSlot(num) {
                let slotNum = _numbers.indexOf(`${num}`);
                if(slotNum < 0) return false;
                return {
                    index: slotNum,
                    position: _arcAngle * (slotNum + 0.5)
                }
            },
            initBallSpin(num, time = 5000) {
                this._ballSettingsSpinTotalTime = time;
                this._ballSettingsSpinStartTime = Date.now();
                this._ballSettingsSpinFinalTime = this._ballSettingsSpinStartTime + this._ballSettingsSpinTotalTime;

                setTimeout(() => {
                    this.updateGameInstance((i) => i.playTimeout = false);
                    this._ballSettingsShowBall = false;
                }, time);
            },
            putBallAtSlot(num, time) {
                let slot = this.findIndexOfSlot(num);
                if(slot === false) {
                    this._ballSettingsShowBall = false;
                    return;
                }

                this.initBallSpin(num, time);

                this._ballSettingsFinalPosition = (5 * 2 * Math.PI + slot.position - 0.005);
                this._ballSettingsStartPosition = 0;
                this._ballSettingsArcIncrement  = (this._ballSettingsFinalPosition -
                    this._ballSettingsStartPosition) /
                    this._ballSettingsSpinTotalTime;

                this._ballSettingsShowBall = true;
            },
            init() {
                this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);

                this._worldAngle = 0;

                this._centerX = this.canvas.width / 2;
                this._centerY = this.canvas.height / 2;

                this._outerEdge = this.canvas.width / 2;

                this._outerRingOuterRadius = this.canvas.width * 0.9 / 2;
                this._outerRingInnerRadius = this.canvas.width * 0.75 / 2;

                this._innerRingOuterRadius = this._outerRingInnerRadius;
                this._innerRingInnerRadius = this._innerRingOuterRadius * 0.8;

                this._slotTextRadius = this._outerRingInnerRadius + (this._outerRingOuterRadius - this._outerRingInnerRadius) * 0.35;
                this._slotTextSize   = (this._outerRingOuterRadius - this._outerRingInnerRadius) * 0.5;

                this._slotTextFont = `normal ${this._slotTextSize}px sans-serif`;

                this._ornamentImg     = new Image();
                this._ornamentImg.src = this.CENTER_ORNAMENT;

                this._ornamentX     = this._ornamentY = -this._innerRingInnerRadius / 2;
                this._ornamentWidth = this._ornamentHeight = this._innerRingInnerRadius;

                this._ballRadius     = ((this.canvas.width / 2 - this._outerRingOuterRadius) / 2) * 0.9;
                this._ballVertOffset = this._innerRingInnerRadius + this._ballRadius;
                this._ballVertRange  = (this._outerRingOuterRadius - this._innerRingInnerRadius);

                this._startOfTime = Date.now();

                this.redrawBackground();
                requestAnimationFrame(this.drawFrame);

                let rows = {
                    first: ['3', '6', '9', '12', '15', '18', '21', '24', '27', '30', '33', '36'],
                    second: ['2', '5', '8', '11', '14', '17', '20', '23', '26', '29', '32', '35'],
                    third: ['1', '4', '7', '10', '13', '16', '19', '22', '25', '28', '31', '34'],
                    red: ['3', '9', '12', '18', '21', '27', '30', '36', '5', '14', '23', '32', '1', '7', '16', '19', '25', '34'],
                    black: ['6', '15', '24', '33', '2', '8', '11', '17', '20', '26', '29', '35', '4', '10', '13', '22', '28', '31'],
                    numeric: {
                        first: ['3', '6', '9', '12', '2', '5', '8', '11', '1', '4', '7', '10'],
                        second: ['15', '18', '21', '24', '14', '17', '20', '23', '13', '16', '19', '22'],
                        third: ['27', '30', '33', '36', '26', '29', '32', '35', '25', '28', '31', '34']
                    },
                    half: {
                        first: ['3', '6', '9', '12', '15', '18', '2', '5', '8', '11', '14', '17', '1', '4', '7', '10', '13', '16'],
                        second: ['21', '24', '27', '30', '33', '36', '20', '23', '26', '29', '32', '35', '19', '22', '25', '28', '31', '34']
                    },
                    e: {
                        even: ['6', '12', '18', '24', '30', '36', '2', '8', '14', '20', '26', '32', '4', '10', '16', '22', '28', '34'],
                        opposite: ['3', '9', '15', '21', '27', '33', '5', '11', '17', '23', '29', '35', '1', '7', '13', '19', '25', '31']
                    }
                };

                const disableChipsFor = (elementId, chips) => {
                    elementId = `[data-chip="${elementId}"]`;
                    $(elementId).on('mouseover', function () {
                        $.each($('.chip'), function (i, e) {
                            if (chips.includes($(this).attr('data-chip'))) $(this).addClass('chip-disabled');
                        });
                    });
                    $(elementId).on('mouseleave', function () {
                        $('.chip').removeClass('chip-disabled');
                    });
                };

                disableChipsFor('row1', rows.second.concat(rows.third));
                disableChipsFor('row2', rows.first.concat(rows.third));
                disableChipsFor('row3', rows.first.concat(rows.second));
                disableChipsFor('red', rows.black);
                disableChipsFor('black', rows.red);
                disableChipsFor('1-12', rows.numeric.second.concat(rows.numeric.third));
                disableChipsFor('13-24', rows.numeric.first.concat(rows.numeric.third));
                disableChipsFor('25-36', rows.numeric.first.concat(rows.numeric.second));
                disableChipsFor('1-18', rows.half.second);
                disableChipsFor('19-36', rows.half.first);
                disableChipsFor('odd', rows.e.opposite);
                disableChipsFor('even', rows.e.even);

                const instance = this;
                $('[data-chip]').on('click', function() {
                    if(instance.chip == null) return;

                    let stack = $(this).find('.bet-stack');
                    if(stack.length === 0) {
                        stack = $('<div class="bet-stack"></div>');
                        stack.hide().fadeIn('fast');
                        $(this).append(stack);
                    }

                    const e = $(`<div class="user-chip" data-display-value="${instance.chipDisplayValue}" data-token-value="${instance.chip}" style="margin-top: -${stack.children().length * 2}px">${instance.abbreviate(instance.chipDisplayValue)}</div>`);
                    stack.append(e);

                    let b = $(this).attr('data-chip');
                    instance.setBet(b, instance.getBet(b) + instance.chip);
                    instance.playSound('/sounds/click.mp3');
                });
            },
            getClientData() {
                return {
                    bet: this.bet
                }
            },
            getBet(chip) {
                if(this.bet[chip] == null) return 0;
                return this.bet[chip];
            },
            setBet(chip = null, value = null) {
                if(chip !== null && value !== null) this.bet[chip] = value;
                let total = 0;
                for(let i = 0; i < Object.keys(this.bet).length; i++) total += this.bet[Object.keys(this.bet)[i]];
                this.totalBet = total;
            },
            redrawBackground() {
                for (let i = 0; i < _slotTotal; i++) {
                    let angle = i * _arcAngle, _endAngle = angle + _arcAngle;

                    this.ctx.fillStyle = DARK_BLACK;
                    this.ctx.beginPath();
                    this.ctx.arc(this._centerX, this._centerY, this._outerEdge, 0, FULL_CIRCLE, true);
                    this.ctx.arc(this._centerX, this._centerY, this._outerRingOuterRadius, 0, FULL_CIRCLE, false);
                    this.ctx.fill();

                    this.ctx.fillStyle = _outerColors[i];
                    this.ctx.beginPath();
                    this.ctx.arc(this._centerX, this._centerY, this._outerRingOuterRadius, angle, _endAngle, false);
                    this.ctx.arc(this._centerX, this._centerY, this._outerRingInnerRadius, _endAngle, angle, true);
                    this.ctx.fill();

                    this.ctx.fillStyle = _innerColors[i];
                    this.ctx.beginPath();
                    this.ctx.arc(this._centerX, this._centerY, this._innerRingOuterRadius, angle, _endAngle, false);
                    this.ctx.arc(this._centerX, this._centerY, this._innerRingInnerRadius, _endAngle, angle, true);
                    this.ctx.fill();

                    this.ctx.fillStyle = DARK_BLACK;
                    this.ctx.beginPath();
                    this.ctx.arc(this._centerX, this._centerY, this._innerRingInnerRadius, 0, FULL_CIRCLE, true);
                    this.ctx.arc(this._centerX, this._centerY, 0, 0, FULL_CIRCLE, false);
                    this.ctx.fill();

                    this.ctx.save();
                    this.ctx.font      = this._slotTextFont;
                    this.ctx.lineWidth = 2;
                    this.ctx.fillStyle = '#ffffff';
                    this.ctx.translate(
                        this._centerX + Math.cos(angle + _arcAngle / 2) * this._slotTextRadius,
                        this._centerY + Math.sin(angle + _arcAngle / 2) * this._slotTextRadius,
                    );

                    this.ctx.rotate(angle + _arcAngle / 2 + Math.PI / 2);

                    this.ctx.fillText(_numbers[i], -this.ctx.measureText(_numbers[i]).width / 2, 0);
                    this.ctx.restore();
                }

                const f = () => {
                    this.ctx.translate(this._centerX, this._centerY)
                    this.ctx.drawImage(this._ornamentImg, this._ornamentX, this._ornamentY, this._ornamentWidth, this._ornamentHeight);
                    this.ctx.setTransform(1, 0, 0, 1, 0, 0);
                };

                if(this._ballSettingsShowBall) f();
                else setTimeout(f, 100);

                this.ctx.save();
            },
            callback(response) {
                this.updateGameInstance((i) => i.playTimeout = true);
                this.putBallAtSlot(response.server_seed.result[0], this.quick ? 1500 : 5000);

                let color = $(`[data-chip="${response.game.data.slot}"]`);
                color = color.hasClass('green') ? [GREEN, DARK_GREEN, 'black'] : (color.hasClass('red') ? [RED, DARK_RED, 'white'] : [BLACK, DARK_BLACK, 'white']);

                this.playSound('/sounds/spin.mp3');
                setTimeout(() => this.playSound('/sounds/drop.mp3'), 300);

                setTimeout(() => {
                    Bus.$emit('game:customHistory:add', {
                        text: response.game.data.slot,
                        style: `background: ${color[0]}; border-bottom: 1px solid ${color[1]}; color: ${color[2]}`,
                        seed: {
                            serverSeed: response.server_seed.server_seed,
                            clientSeed: response.server_seed.client_seed,
                            nonce: response.server_seed.nonce,
                            placement: 'left'
                        }
                    });

                    if(response.game.win) this.playSound('/sounds/win.mp3');
                    else this.playSound('/sounds/lose.mp3');
                }, response.game.delay);
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-chips' },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'quick', 'sound', 'stats'] } }
                ];
            }
        },
        data() {
            return {
                canvas: null,
                ctx: null,
                bet: {},
                totalBet: 0,

                chipDisplayValue: 1000,
                chip: 1000,

                CENTER_ORNAMENT: 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+Cjxzdmcgd2lkdGg9IjcycHgiIGhlaWdodD0iNzJweCIgdmlld0JveD0iMCAwIDcyIDcyIiB2ZXJzaW9uPSIxLjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiPgogICAgPCEtLSBHZW5lcmF0b3I6IFNrZXRjaCA0MiAoMzY3ODEpIC0gaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoIC0tPgogICAgPHRpdGxlPldoZWVsIGhhbmRsZTwvdGl0bGU+CiAgICA8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4KICAgIDxkZWZzPgogICAgICAgIDxwYXRoIGQ9Ik0zOC4xMzgxMjA1LDcuMzgxMjA1MzkgTDM5Ljg1NjQxMzgsMjQuNTY0MTM4MiBDNDMuMjUyMjg3OSwyNS43MDMwNjM2IDQ2LjA0NzM1NTcsMjguNDgyNDUxOCA0Ny4yOTYzMDMzLDMyLjEyOTYyOTYgTDY0LjYxODc5NDYsMzMuODYxODc5NSBDNjUuMzI3ODk2MiwzMi43NDI4NTggNjYuNTc3MTk2MiwzMiA2OCwzMiBDNzAuMjA5MTM5LDMyIDcyLDMzLjc5MDg2MSA3MiwzNiBDNzIsMzguMjA5MTM5IDcwLjIwOTEzOSw0MCA2OCw0MCBDNjYuNTc3MTk2Miw0MCA2NS4zMjc4OTYyLDM5LjI1NzE0MiA2NC42MTg3OTQ2LDM4LjEzODEyMDUgTDQ3LjQ0MTg1NywzOS44NTU4MTQzIEM0Ni4yNzUxMzgyLDQzLjM5MDk4NjEgNDMuMzkwOTg2MSw0Ni4yNzUxMzgyIDM5Ljg1NTgxNDMsNDcuNDQxODU3IEwzOC4xMzgxMjA1LDY0LjYxODc5NDYgQzM5LjI1NzE0Miw2NS4zMjc4OTYyIDQwLDY2LjU3NzE5NjIgNDAsNjggQzQwLDcwLjIwOTEzOSAzOC4yMDkxMzksNzIgMzYsNzIgQzMzLjc5MDg2MSw3MiAzMiw3MC4yMDkxMzkgMzIsNjggQzMyLDY2LjU3NzE5NjIgMzIuNzQyODU4LDY1LjMyNzg5NjIgMzMuODYxODc5NSw2NC42MTg3OTQ2IEwzMi4xMjk2MzA3LDQ3LjI5NjMwNzEgQzI4LjQ4MjQ1MTgsNDYuMDQ3MzU1NyAyNS43MDMwNjM2LDQzLjI1MjI4NzkgMjQuNTY0MTM4MiwzOS44NTY0MTM4IEw3LjM4MTIwNTM5LDM4LjEzODEyMDUgQzYuNjcyMTAzNzksMzkuMjU3MTQyIDUuNDIyODAzNzksNDAgNCw0MCBDMS43OTA4NjEsNDAgOS40MzU1MzIwMmUtMTUsMzguMjA5MTM5IDkuNTcwODAyNzdlLTE1LDM2IEM5LjcwNjA3MzUyZS0xNSwzMy43OTA4NjEgMS43OTA4NjEsMzIgNCwzMiBDNS40MjI4MDM3OSwzMiA2LjY3MjEwMzc5LDMyLjc0Mjg1OCA3LjM4MTIwNTM5LDMzLjg2MTg3OTUgTDI0LjcxMjI4ODMsMzIuMTI4NzcxMiBDMjUuOTMyMTAwMiwyOC42MzEzOTQgMjguNjMxMzk0LDI1LjkzMjEwMDIgMzIuMTI4NzcxMiwyNC43MTIyODgzIEwzMy44NjE4Nzk1LDcuMzgxMjA1MzkgQzMyLjc0Mjg1OCw2LjY3MjEwMzc5IDMyLDUuNDIyODAzNzkgMzIsNCBDMzIsMS43OTA4NjEgMzMuNzkwODYxLC00LjI2MzI1NjQxZS0xNCAzNiwtNC4yNjMyNTY0MWUtMTQgQzM4LjIwOTEzOSwtNC4yNjMyNTY0MWUtMTQgNDAsMS43OTA4NjEgNDAsNCBDNDAsNS40MjI4MDM3OSAzOS4yNTcxNDIsNi42NzIxMDM3OSAzOC4xMzgxMjA1LDcuMzgxMjA1MzkgWiIgaWQ9InBhdGgtMSI+PC9wYXRoPgogICAgPC9kZWZzPgogICAgPGcgaWQ9IlJvdWxldHRlIiBzdHJva2U9Im5vbmUiIHN0cm9rZS13aWR0aD0iMSIgZmlsbD0ibm9uZSIgZmlsbC1ydWxlPSJldmVub2RkIj4KICAgICAgICA8ZyBpZD0iR2FtZS0tLVJvdWxldHRlIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtNzQyLjAwMDAwMCwgLTIxMC4wMDAwMDApIj4KICAgICAgICAgICAgPGcgaWQ9IlJvdWxldHRlIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMjAuMDAwMDAwLCA5Ni4wMDAwMDApIj4KICAgICAgICAgICAgICAgIDxnIGlkPSJXaGVlbCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNTIyLjAwMDAwMCwgMTQuMDAwMDAwKSI+CiAgICAgICAgICAgICAgICAgICAgPGcgaWQ9IlRvcCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNTYuMDAwMDAwLCA1Ni4wMDAwMDApIj4KICAgICAgICAgICAgICAgICAgICAgICAgPGcgaWQ9IldoZWVsLWhhbmRsZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNDQuMDAwMDAwLCA0NC4wMDAwMDApIj4KICAgICAgICAgICAgICAgICAgICAgICAgICAgIDxnIGlkPSJDb21iaW5lZC1TaGFwZSIgZmlsbD0iI0ZGRDEwMCI+CiAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgPHVzZSB4bGluazpocmVmPSIjcGF0aC0xIj48L3VzZT4KICAgICAgICAgICAgICAgICAgICAgICAgICAgICAgICA8dXNlIHhsaW5rOmhyZWY9IiNwYXRoLTEiPjwvdXNlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPC9nPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPGNpcmNsZSBpZD0iT3ZhbCIgZmlsbD0iI0Q2QTk0OSIgY3g9IjM2IiBjeT0iMzYiIHI9IjciPjwvY2lyY2xlPgogICAgICAgICAgICAgICAgICAgICAgICAgICAgPGNpcmNsZSBpZD0iT3ZhbCIgZmlsbD0iI0ZCRDYzRSIgY3g9IjM2IiBjeT0iMzYiIHI9IjIiPjwvY2lyY2xlPgogICAgICAgICAgICAgICAgICAgICAgICA8L2c+CiAgICAgICAgICAgICAgICAgICAgPC9nPgogICAgICAgICAgICAgICAgPC9nPgogICAgICAgICAgICA8L2c+CiAgICAgICAgPC9nPgogICAgPC9nPgo8L3N2Zz4=',
                _outerEdge: 0,
                _outerRingOuterRadius: 0,
                _outerRingInnerRadius: 0,
                _innerRingInnerRadius: 0,
                _innerRingOuterRadius: 0,
                _ornamentX: 0,
                _ornamentY: 0,
                _ornamentImg: null,
                _ornamentHeight: 0,
                _ornamentWidth: 0,
                _slotTextRadius: 0,
                _slotTextSize: 0,
                _slotTextFont: 'normal 12px sans-serif',
                _centerX: null,
                _centerY: null,
                _startOfTime: 0,
                _worldAngle: 0,

                _ballRadius: 0,
                _ballX: 0,
                _ballY: 0,
                _ballVertOffset: 0,
                _ballVertRange: 0,
                _ballSettingsArcIncrement: 0,
                _ballSettingsShowBall: false,
                _ballSettingsSpinFinalTime: 0,
                _ballSettingsSpinStartTime: 0,
                _ballSettingsSpinTimeElapsed: 0,
                _ballSettingsSpinTotalTime: 0,
                _ballSettingsStartPosition: 0,
                _ballSettingsFinalPosition: 0
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-roulette {
        .customHistory {
            top: 25px;
            transform: unset;
            right: 25px;
        }

        .game-content-roulette {
            display: flex;
            justify-content: center;

            .roulette-wheel {
                width: 300px;
                height: 300px;
                animation: rotating 15s linear infinite;
            }

            .roulette-field {
                position: absolute;
                bottom: 0;
                left: 0;
                width: 100%;
                padding: 10px;

                .header {
                    position: relative;
                    margin-bottom: 15px;
                    z-index: 500;
                    @include themed() {
                        text-shadow: 0 0 2px t('sidebar');
                    }

                    .right {
                        position: absolute;
                        right: 0;
                        bottom: 0;
                    }
                }

                .content {
                    display: flex;

                    .side {
                        display: inline-flex;
                        width: 7%;
                        flex-direction: column;

                        &:first-child .chip {
                            height: 100%;
                            width: 95%;
                        }
                    }

                    .numeric {
                        display: inline-flex;
                        flex-direction: column;
                        width: 100%;
                        .r {
                            display: flex;
                            width: 100%;
                            height: 100%;
                        }
                    }
                }
            }

            .chip {
                display: inline-flex;
                width: 100%;
                text-align: center;
                align-items: center;
                justify-content: center;
                border-radius: 3px;
                margin: 1px;
                padding: 8px;
                cursor: pointer;
                opacity: 1;
                position: relative;
                transition: opacity 0.3s ease;
                user-select: none;

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

            .chip-disabled {
                opacity: 0.5 !important;
            }

            .chip.bordered {
                @include themed() {
                    border: 1px solid rgba(t('text'), 0.3);
                }
            }

            .chip.red {
                background: #fe3955;
                color: white;
            }

            .chip.black {
                background: #485163;
                color: white;
            }

            .chip.green {
                background: #62ca5b;
                color: white;
            }
        }

        [data-display-value="1"] {
            background-color: rgb(41, 182, 246) !important;
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

        [data-display-value="100000000000"] {
            background-color: #AA88FF !important;
        }

        .overview-render-target {
            .roulette-wheel {
                width: 200px;
                margin-left: 50%;
                transform: translate(-50%);
                margin-top: 10px;
            }
        }

        @keyframes rotating {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        @include media-breakpoint-down(lg) {
            .game-content-roulette {
                .chip {
                    font-size: 0.9em;
                }
            }
        }

        @include media-breakpoint-down(md) {
            .game-content-roulette {
                padding-bottom: 250px !important;

                .roulette-wheel {
                    width: 200px;
                    height: 200px;
                }

                .chip {
                    font-size: 0.85em;
                    padding: 3px !important;
                }

                .customHistory {
                    right: 10px;
                    top: 10px;
                    transform: scale(0.7) translate(20%, -20%);
                }
            }
        }
    }
</style>
