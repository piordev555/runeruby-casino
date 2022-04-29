<template>
    <div>
        <div class="towerColumns">
            <div class="towerMultipliers">
                <div v-if="gameInstance.game.data[mines]" v-for="m in gameInstance.game.data[mines]" class="multiplier">{{ abbreviate(m) }}</div>
            </div>
            <div class="towerField">
                <div v-for="row in 10" class="towerRow" :data-row-id="row - 1">
                    <div v-for="column in 5" class="cell" :data-cell-id="column - 1" @click="cellClick(row - 1, column - 1)"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            mines: 1
        }
    },
    computed: {
        ...mapGetters(['gameInstance'])
    },
    methods: {
        getClientData() {
            return {
                mines: this.mines
            }
        },
        callback(response) {
            if(this.isExtendedGameStarted()) {
                $('.cell').removeClass('active').removeClass('selected').removeClass('red').removeClass('green').find('img').remove();
                this.setRow(0, true);
            } else {
                $('.cell').removeClass('active');
                if(response) this.resultPopup(response.game);
            }
        },
        cellClick(row, column) {
            const e = $(`[data-row-id="${row}"] [data-cell-id="${column}"]`);

            if(!e.hasClass('active')) return;

            this.setRow(row, false);
            e.addClass('selected');
            this.sendTurn({ cell: column }, (response) => {
                if (response.type === 'fail') {
                    this.setRow(row, true);
                    e.removeClass('selected');
                    return;
                }

                for (let i = 0; i < 5; i++) {
                    const safe = !response.data.death.includes(i);
                    $(`[data-row-id="${row}"] [data-cell-id="${i}"]`).toggleClass(safe ? 'green' : 'red').append(`<img src="/img/game/mines-${safe ? 'gem' : 'mine'}.svg" alt>`);
                }

                if (response.type === 'lose') this.playSound('/sounds/lose.mp3');
                if (response.type === 'finish') this.playSound('/sounds/win.mp3');
                if (response.type === 'finish' || response.type === 'lose') {
                    for (let i = 0; i < response.data.grid.length; i++) {
                        let row = response.data.grid[i];
                        for (let j = 0; j < 5; j++) {
                            const e = $('.game-tower').find(`[data-row-id="${i}"] [data-cell-id="${j}"]`);
                            if (e.hasClass('red') || e.hasClass('green')) continue;
                            e.addClass(row.includes(j) ? 'red' : 'green').append(`<img src="/img/game/mines-${row.includes(j) ? 'mine' : 'gem'}.svg" alt>`);
                        }
                    }

                    this.finishExtended(false);
                    this.resultPopup(response.game);
                }
                if (response.type === 'continue') {
                    this.playSound('/sounds/guessed.mp3');
                    this.setRow(row + 1, true);
                }
            });
        },
        gameDataRetrieved() {},
        restore(game) {
            for(let i = 0; i < game.history.length; i++) $(`[data-row-id="${i}"] [data-cell-id="${game.history[i]}"]`).addClass('selected');
            this.setRow(game.history.length);
        },
        setRow(id, active = true) {
            $(`[data-row-id="${id}"] .cell`).toggleClass('active', active);
        },
        getSidebarComponents() {
            return [
                { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                { name: 'wager-classic' },
                { name: 'label', data: { label: this.$i18n.t('general.mines') } },
                { name: 'buttons', data: { buttons: [
                    { label: 1, callback: () => this.mines = 1 },
                    { label: 2, callback: () => this.mines = 2 },
                    { label: 3, callback: () => this.mines = 3 },
                    { label: 4, callback: () => this.mines = 4 }
                ] } },
                { name: 'play' },
                { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } }
            ];
        }
    }
}
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .game-tower {
        .game-content-tower {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .towerColumns {
            @include themed() {
                display: flex;
                flex-direction: row;

                .towerMultipliers {
                    .multiplier {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        text-align: center;
                        user-select: none;
                        cursor: default;
                        height: 9.97%;
                        margin-right: 25px;
                        color: rgba(t('text'), 0.4);
                    }
                }

                .towerField {
                    display: flex;
                    flex-direction: column;

                    .towerRow {
                        display: flex;
                        flex-direction: row;
                        margin: 5px 0;

                        .cell {
                            display: inline-flex;
                            width: 40px;
                            height: 40px;
                            margin-right: 10px;
                            background: t('secondary');
                            border-radius: 3px;
                            transition: background 0.3s ease;
                            align-items: center;
                            justify-content: center;

                            &:last-child {
                                margin-right: 0;
                            }

                            img {
                                width: 65%;
                                user-select: none;
                                -webkit-user-drag: none;
                            }
                        }

                        .cell.active {
                            cursor: pointer;
                        }

                        .cell.active, .cell.selected {
                            background: t('text') !important;
                        }

                        .cell.red {
                            background: #db4437;
                        }

                        .cell.green {
                            background: #2ecc71;
                        }
                    }
                }
            }
        }

        .overview-render-target {
            display: flex;
            align-items: center;
            justify-content: center;

            .towerColumns {
                @include themed() {
                    .towerField {
                        .towerRow {
                            margin: 2px 0;

                            .cell {
                                width: 20px;
                                height: 20px;
                                margin-right: 5px;
                                border-radius: 1px;
                            }
                        }
                    }
                }
            }
        }
    }
</style>
