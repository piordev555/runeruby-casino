<template>
    <div class="mines_grid">
        <div v-for="i in 5 * 5" :key="i - 1" :data-mine-id="i - 1" class="mine disabled" @click="cellClick(i - 1)">
            <img alt src="/img/game/mines-mine.svg">
            <img alt src="/img/game/mines-gem.svg">
        </div>
    </div>
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';
    const MINE_TYPE_LOSE = 1, MINE_TYPE_SAFE = 0;

    export default {
        data() {
            return {
                mines: 3
            }
        },
        computed: {
            ...mapGetters(['gameInstance'])
        },
        watch: {
            mines() {
                this.updateHistory();
            }
        },
        methods: {
            displayGrid(grid) {
                for(let i = 0; i < 5 * 5; i++) this.setMine(i, grid[i] === 1 ? MINE_TYPE_LOSE : MINE_TYPE_SAFE, false);
            },
            gameDataRetrieved() {
                this.updateHistory();
            },
            updateHistory() {
                document.querySelectorAll('.history-mines').forEach(e => e.parentNode.removeChild(e));
                let multipliers = this.gameInstance.game.data[this.mines];
                _.forEach(multipliers, (key, value) => {
                    Bus.$emit('mines:history:addEntry', { html: `<div>${value}</div><div>x${this.abbreviate(key)}</div>`, type: 'append' });
                });
            },
            restore(game) {
                $('.game-mines').find('.mine').removeClass('disabled');
                _.forEach(game.history, (e) => {
                    $('.game-mines').find(`[data-mine-id="${e}"]`).addClass('selected');
                    this.setMine(parseInt(e), MINE_TYPE_SAFE, false);
                });
            },
            extendedAutoBetHandle(take) {
                if($('.game-mines').find('.autoBetPick').length > 25 - this.mines) {
                    this.$toast.error(this.$i18n.t('general.error.autobet_mines_error'));
                    this.gameInstance.game.autoBetSettings.stop();
                    this.finishExtended(true);
                    return;
                }

                if($('.game-mines').find('.autoBetPick').length === 0) {
                    this.$toast.error(this.$i18n.t('general.error.autobet_pick_something'));
                    this.gameInstance.game.autoBetSettings.stop();
                    this.finishExtended(true);
                    return;
                }

                let picked = [];
                _.forEach($('.game-mines').find('.autoBetPick'), (e) => picked.push(parseInt($(e).attr('data-mine-id'))));

                this.turn(picked, take);
            },
            cellClick(i) {
                const e = $(`[data-mine-id="${i}"]`);

                if(!this.isExtendedGameStarted() && this.gameInstance.bettingType === 'auto') {
                    e.toggleClass('autoBetPick');
                    return;
                }

                if(!this.isExtendedGameStarted() || e.hasClass('disabled')) return;
                this.turn(i);
            },
            setMine(id, type, sound) {
                if(sound) this.playSound(`/sounds/${type === MINE_TYPE_SAFE ? 'open' : 'lose'}.mp3`, 50);
                const e = $('.game-mines').find(`[data-mine-id='${id}']`);
                e.attr('class', `disabled mine mines-${type} ${e.hasClass('selected') ? 'selected' : ''} ${e.hasClass('autoBetPick') ? 'autoBetPick' : ''}`).attr('data-type', type === MINE_TYPE_SAFE ? 'open' : 'lose');
            },
            turn(mineId, callback = null) {
                this.sendTurn({ id: mineId }, (response) => {
                    if(response.type === 'fail') return;
                    if(response.type === 'lose') {
                        if(!Array.isArray(mineId)) this.setMine(mineId, MINE_TYPE_LOSE, true);
                        this.finishExtended(false);
                        this.updateGameInstance((i) => i.playTimeout = true);

                        if(Array.isArray(mineId)) $('.game-mines').find('.mine').attr('data-type', null).removeClass('mines-0').removeClass('mines-1');
                        setTimeout(() => {
                            this.updateGameInstance((i) => i.playTimeout = false);
                            this.displayGrid(response.data.grid);

                            this.resultPopup(response.game);
                        }, Array.isArray(mineId) ? 200 : 800);
                    } else {
                        if(!Array.isArray(mineId)) {
                            this.setMine(mineId, MINE_TYPE_SAFE, true);
                            $('.history-mines').removeClass('highlight');
                            $(`.history-mines:nth-child(${response.turn})`).addClass('highlight');
                        }

                        if(response.type === 'finish') {
                            this.resultPopup(response.game);
                            this.finishExtended(false);
                        }
                    }

                    if(Array.isArray(mineId)) _.forEach(mineId, (id) => $(`[data-mine-id="${id}"]`).addClass('selected'));
                    else $(`[data-mine-id="${mineId}"]`).addClass('selected');
                    if(callback != null) callback(response);
                }, () => {
                    if(callback != null) callback({ game: { status: 'lose' }});
                });
            },
            callback(response) {
                $('.history-mines').removeClass('highlight');
                if(this.isExtendedGameStarted()) $('.game-mines .mine.disabled').attr('data-type', '').removeClass('disabled').removeClass('mine-1').removeClass('remove-0').removeClass('selected');
                else {
                    $('.game-mines .mine').toggleClass('disabled', true);
                    if(response && response.game.data.game_data.grid !== undefined) {
                        this.displayGrid(response.game.data.game_data.grid);
                        this.resultPopup(response.game);
                    }
                }
            },
            getClientData() {
                return {
                    mines: this.mines
                }
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'label', data: { label: this.$i18n.t('general.mines') } },
                    { name: 'buttons', data: { buttons: [
                        { label: 3, callback: () => this.mines = 3 },
                        { label: 5, callback: () => this.mines = 5 },
                        { label: 10, callback: () => this.mines = 10 },
                        { label: 24, callback: () => this.mines = 24 },
                        { label: this.$i18n.t('general.edit'), callback: (v) => this.mines = v, type: 'input', input: { min: 2, max: 24 } } ] } },
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

    .game-mines {
        .game-history {
            display: flex;
            height: 84px;
        }

        .history-mines {
            display: inline-flex;
            flex-direction: column;
            text-align: center;
            align-items: center;
            justify-content: center;
            width: 65px;
            padding: 9px;
            border-radius: 3px;
            line-height: 17px;

            div:first-child {
                font-size: 13px;
            }

            div:last-child {
                font-size: 11px;
            }

            &:last-of-type {
                margin-right: 15px !important;
            }

            @include themed() {
                $color: rgba(t('text'), 0.25);
                color: $color !important;
                border: 1px solid $color !important;
            }
        }

        .theme--light .history-mines {
            color: black !important;
        }

        .game-content-mines {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mines_grid {
            display: grid;
            grid-gap: 10px;
            grid-template-columns: repeat(5, 65px);
            grid-template-rows: repeat(5, 65px);
            grid-auto-flow: column;
            width: 100%;
            justify-content: center;
            margin-top: -72px;

            @include themed() {
                div.disabled {
                    background: t('secondary');
                    cursor: default !important;
                }

                div {
                    background: lighten(t('secondary'), 2%);
                    text-align: center;
                    border-radius: 2px;
                    transition: background-color 0.3s ease;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    justify-content: center;

                    img {
                        width: 65%;
                        user-select: none;
                        -webkit-user-drag: none;
                        display: none;
                    }

                    &:hover {
                        background: lighten(t('secondary'), 3%);
                    }
                }
            }

            [data-type='open'] {
                img:last-child {
                    display: block;
                }
            }

            [data-type='lose'] {
                img:first-child {
                    display: block;
                }
            }

            div.mines-0.selected {
                background: #2ecc71 !important;
            }

            div.mines-1.selected {
                background: #db4437 !important;
            }

            div.autoBetPick {
                background: white !important;
            }
        }

        @include media-breakpoint-down(lg) {
            .mines_grid {
                grid-template-columns: repeat(5, 40px);
                grid-template-rows: repeat(5, 40px);
                grid-gap: 5px;

                font-size: 13px;
            }
        }

        @include media-breakpoint-down(md) {
            .mines_grid {
                grid-template-columns: repeat(5, 35px);
                grid-template-rows: repeat(5, 35px);
                grid-gap: 5px;
                padding: 15px 0 25px;
                font-size: 11px;
            }

            .game-content {
                margin-bottom: 120px;
            }
        }
    }

    .overview-render-target .mines_grid {
        grid-template-columns: repeat(5, 35px);
        grid-template-rows: repeat(5, 35px);
        grid-gap: 7px;
        transform: unset !important;
        margin-bottom: 20px;
        margin-top: 10px;

        span {
            font-size: 11px;
        }
    }
</style>
