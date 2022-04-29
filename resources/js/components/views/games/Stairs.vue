<template>
    <div>
        <div class="stairsColumns">
            <div class="stairsMultipliers">
                <div v-if="gameInstance.game.data[mines]" v-for="m in gameInstance.game.data[mines]" class="multiplier">{{ abbreviate(m) }}</div>
            </div>
            <div class="stairsContainer">
                <div class="stairsRow" v-for="(row, rowId) in rows" :data-row-id="rows.length - 1 - rowId" :set="cell = 0">
                    <div :data-cell-id="type === 0 ? null : cell" :set="type === 0 ? null : cell++" v-for="(type) in row" :class="`stairsCell ${type === 0 ? 'stairsInvisible' : 'stairsVisible'}`" @click="cellClick(rows.length - 1 - rowId, $event.target)"></div>
                </div>
                <div class="character stand"></div>
            </div>
        </div>
        <div class="d-none">
            <icon icon="stairs" data-icon-clone></icon>
            <icon icon="ladder" data-ladder-clone></icon>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                mines: 1,

                rows: [
                    [1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    [1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0],
                    [0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
                    [0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
                    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
                    [0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0],
                    [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0],
                    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
                    [0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1],
                    [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0],
                    [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]
                ]
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
                    $('[data-cell-id]').removeClass('selected').removeClass('active').find('svg').remove();
                    $('.character').fadeOut('fast', () => $('.character').attr('data-flip', 'false').attr('style', '').attr('class', 'character stand').fadeIn('fast', () => this.setRow(0)));
                } else {
                    $('[data-cell-id]').removeClass('active');
                    $('.character').attr('class', 'character victory');
                    if(response) this.resultPopup(response.game);
                }
            },
            cellClick(row, target) {
                const cell = $(target).data('cell-id');
                const e = $(`[data-row-id="${row}"] [data-cell-id="${cell}"]`);

                if(!e.hasClass('active')) return;
                this.setRow(row, false);
                this.sendTurn({ cell: cell }, (response) => {
                    if(response.type === 'fail') {
                        this.setRow(row, true);
                        return;
                    }

                    $('.character').attr('class', 'character run').animate({ left: e.position().left }, e.position().left === $('.character').position().left ? 0 : 1000, () => {
                        setTimeout(() => {
                            _.forEach(response.data.death, (deathCell) => this.dropStone(row, deathCell));

                            $('.character').attr('class', 'character climb').animate({top: $(`[data-row-id="${row}"] [data-cell-id="${cell}"]`).position().top - $('.character').height()}, 800, () => {
                                if (response.type === 'finish') {
                                    this.finishExtended(false);
                                    $('.character').attr('class', 'character victory');
                                    this.playSound('/sounds/win.mp3');
                                    this.resultPopup(response.game);
                                }

                                if (response.type === 'continue') {
                                    this.setRow(row + 1, true);
                                    $('.character').attr('class', 'character stand');
                                    this.playSound('/sounds/guessed.mp3');
                                }
                                if (response.type === 'lose') {
                                    this.finishExtended(false);
                                    $('.character').attr('class', 'character death');
                                    this.playSound('/sounds/lose.mp3');
                                    this.resultPopup(response.game);
                                }
                            });
                        }, 200);
                    });

                    e.addClass('selected');
                    e.append($('[data-ladder-clone]').clone().attr('data-ladder-clone', null));
                });
            },
            gameDataRetrieved() {
                $(`[data-cell-id]`).on('mouseover', function() {
                    if(!$(this).hasClass('active')) return;
                    $('.character').attr('data-flip', $(this).position().left < $('.character').position().left);
                });
            },
            restore(game) {
                for(let i = 0; i < game.history.length; i++) $(`[data-row-id="${i}"] [data-cell-id="${game.history[i]}"]`).addClass('selected');
                this.setRow(game.history.length);
                if(game.history.length > 0) {
                    const e = $(`[data-row-id="${game.history.length - 1}"] [data-cell-id="${game.history[game.history.length - 1]}"]`);
                    $('.character').css({ top: e.position().top - $('.character').height(), left: e.position().left });
                }
            },
            dropStone(row, cell) {
                const transformId = `_${Math.random()}`;
                const e = $('[data-icon-clone]').clone().attr('data-transform-id', transformId).attr('data-icon-clone', null);
                $(`[data-row-id="${row}"] [data-cell-id="${cell}"]`).append(e);
                e.hide().css({ top: -40 });
                setTimeout(() => $(`[data-transform-id="${transformId}"]`).show().animate({top: -14}, 700), 300);
            },
            setRow(id, active = true) {
                $(`[data-row-id="${id}"] .stairsCell`).toggleClass('active', active);
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
                        { label: 4, callback: () => this.mines = 4 },
                        { label: 5, callback: () => this.mines = 5 },
                        { label: 6, callback: () => this.mines = 6 },
                        { label: 7, callback: () => this.mines = 7 }
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

    .game-stairs {
        .game-content-stairs {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes character-climb {
            from { background-position-x: 0; }
            to { background-position-x: -253px; }
        }

        @keyframes character-stand {
            from { background-position-x: 0; }
            to { background-position-x: -236px; }
        }

        @keyframes character-victory {
            from { background-position-x: 0; }
            to { background-position-x: -253px; }
        }

        @keyframes character-death {
            from { background-position-x: 0; }
            to { background-position-x: -(352px - (352 / 10)); }
        }

        @keyframes character-run {
            from { background-position-x: 0; }
            to { background-position-x: -295px; }
        }

        .stairsColumns {
            @include themed() {
                display: flex;
                flex-direction: row;

                .stairsMultipliers {
                    display: flex;
                    flex-direction: column-reverse;
                    margin-right: 15px;

                    .multiplier {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 33%;
                        text-align: center;
                        color: rgba(t('text'), 0.45);
                        cursor: default;
                        user-select: none;
                    }

                    .multiplier.active {
                        color: t('text');
                    }
                }

                .stairsContainer {
                    display: flex;
                    flex-direction: column;
                    position: relative;

                    .character {
                        pointer-events: none;
                        height: 36px;
                        width: 24px;
                        position: absolute;
                        bottom: -27px;
                    }

                    [data-flip="true"] {
                        transform: scaleX(-1);
                    }

                    .character.stand {
                        background-image: url('/img/stairs/stand.png');
                        animation: character-stand 0.8s steps(10);
                        animation-iteration-count: infinite;
                    }

                    .character.run {
                        background-image: url('/img/stairs/run.png');
                        animation: character-run 0.8s steps(10);
                        animation-iteration-count: infinite;
                    }

                    .character.victory {
                        background-image: url('/img/stairs/victory.png');
                        animation: character-victory 0.8s steps(10);
                        animation-iteration-count: infinite;
                    }

                    .character.death {
                        background-image: url('/img/stairs/death.png');
                        animation: character-death 0.8s steps(9);
                        animation-fill-mode: forwards;
                        animation-iteration-count: 1;
                        width: 30px;
                    }

                    .character.climb {
                        background-image: url('/img/stairs/climb.png');
                        animation: character-climb 0.8s steps(10);
                        animation-iteration-count: infinite;
                    }

                    .stairsRow {
                        display: flex;
                        flex-direction: row;
                        margin: 11px 0;

                        .stairsCell {
                            display: inline-flex;
                            width: 100%;
                            padding: 10px 20px;
                            margin-right: 4px;
                            border-radius: 3px;
                            transition: background 0.3s ease;
                            position: relative;

                            svg {
                                width: 85%;
                                position: absolute;
                                left: 50%;
                                transform: translateX(-50%);
                            }

                            .fa-ladder {
                                color: t('secondary');
                                font-size: 3em;
                                top: -4px;
                            }
                        }

                        .stairsCell.stairsVisible {
                            background: t('secondary');
                            cursor: default;
                        }

                        .stairsCell.stairsVisible.active {
                            cursor: pointer;
                        }

                        .stairsCell.stairsVisible.active,
                        .stairsCell.stairsVisible.selected {
                            background: t('text');
                        }

                        .stairsCell.stairsInvisible {
                            background: transparent;
                            pointer-events: none;
                        }

                    }

                }
            }
        }

        @media (max-width: 1650px) {
            .stairsColumns {
                @include themed() {
                    .stairsMultipliers {
                        .multiplier {
                            height: 22.6%;
                        }
                    }

                    .stairsContainer {
                        .stairsRow {
                            margin: 11px 0;

                            .stairsCell {
                                padding: 7px 13px;
                            }
                        }
                    }
                }
            }
        }

        @media (max-width: 1400px) {
            .stairsColumns {
                .character {
                    transform: scale(0.5) !important;
                    margin-top: 9px;
                }

                [data-flip="true"] {
                    transform: scale(0.5) scaleX(-1) !important;
                }

                .fa-ladder {
                    top: -16px !important;
                }

                @include themed() {
                    .stairsMultipliers {
                        .multiplier {
                            height: 30.5%;
                            font-size: 0.805em;
                        }
                    }

                    .stairsContainer {
                        .stairsRow {
                            margin: 11px 0;

                            .stairsCell {
                                padding: 5px 9px;
                            }
                        }
                    }
                }
            }
        }

        @media (max-width: 1100px) {
            .stairsColumns {
                @include themed() {
                    .stairsMultipliers {
                        .multiplier {
                            height: 34.8%;
                            font-size: 0.7em;
                        }
                    }

                    .stairsContainer {
                        .stairsRow {
                            margin: 11px 0;

                            .stairsCell {
                                padding: 4px 6px;
                                border-radius: 1px;
                            }
                        }
                    }
                }
            }
        }

        @media (max-width: 485px) {
            .stairsColumns {
                @include themed() {
                    .stairsMultipliers {
                        .multiplier {
                            height: 43.9%;
                            font-size: 0.6em;
                        }
                    }

                    .stairsContainer {
                        .stairsRow {
                            margin: 11px 0;

                            .stairsCell {
                                padding: 3px 5px;
                                border-radius: 1px;
                            }
                        }
                    }
                }
            }
        }
    }

    .overview-render-target {
        .stairsColumns {
            .stairsContainer {
                .stairsRow {
                    margin: 5px 0 !important;

                    .stairsCell {
                        padding: 4px 7px !important;
                        border-radius: 1px !important;
                    }
                }
            }
        }
    }
</style>
