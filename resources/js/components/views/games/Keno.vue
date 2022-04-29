<template>
    <div class="keno_grid">
        <div :class="(selected.includes(n) ? 'active' : '') + ' ' + (response.includes(n) ? 'selected' : '')" v-for="n in 40" :key="n" @click="tileClick(n)">
            <svg width="64" height="64" preserveAspectRatio="xMidYMid meet">
                <use href="#gem"></use>
            </svg>
            <span>{{ n }}</span>
        </div>
    </div>
</template>

<script>
    import Bus from '../../../bus';

    export default {
        data() {
            return {
                selected: [],
                response: [],
                autoPickInProcess: false
            }
        },
        watch: {
            selected() {
                this.updateHistory();
            }
        },
        methods: {
            tileClick(n) {
                if(this.selected.length >= 10 && !this.selected.includes(n)) return;

                this.playSound('/sounds/click.mp3');

                if(!this.selected.includes(n)) this.selected.push(n);
                else this.selected.splice(this.selected.indexOf(n), 1);
            },
            callback(response) {
                let foundHistoryIndex = 2;
                this.chain(10, 100, (i) => {
                    const number = response.server_seed.result[i - 1];
                    this.response.push(number);
                    this.playSound(`/sounds/${this.selected.includes(number) ? 'open' : 'empty'}.mp3`);

                    if(this.selected.includes(number)) {
                        $('.history-keno').removeClass('highlight');
                        $(`.history-keno:nth-child(${foundHistoryIndex})`).addClass('highlight');
                        foundHistoryIndex++;
                    }

                    if(i === 9) {
                        this.updateGameInstance((i) => i.playTimeout = false);
                        this.resultPopup(response.game);
                    }
                });
            },
            errorCallback() {
                this.$toast.error(this.$i18n.t('general.error.empty'));
            },
            gameDataRetrieved() {
                this.updateHistory();
            },
            updateHistory() {
                document.querySelectorAll('.history-keno').forEach(e => e.parentNode.removeChild(e));

                if(this.selected.length > 0) {
                    let multipliers = this.gameInstance.game.data[this.selected.length].sort((a, b) => a - b).reverse();
                    for(let i = 0; i < multipliers.length; i++)
                        Bus.$emit('keno:history:addEntry', { html: `<div>${multipliers[i].toFixed(2)}x</div><div>${multipliers.length - i - 1} <i class="fad fa-square d-none d-lg-inline-block"></i></div>` });
                }
            },
            getClientData() {
                return { tiles: this.selected };
            },
            preStart() {
                this.response = [];
            },
            getSidebarComponents() {
                return [
                    { name: 'label', data: { label: this.$i18n.t('general.wager') } },
                    { name: 'wager-classic' },
                    { name: 'buttons', data: { buttons: [
                        { label: this.$i18n.t('general.autopick'), callback: () => {
                            if(this.autoPickInProcess) return;
                            this.autoPickInProcess = true;

                            this.selected = [];

                            let picked = [];
                            while(picked.length <= 10) {
                                let rand = this.random(1, 40);
                                if(picked.includes(rand)) continue;
                                picked.push(rand);
                            }

                            this.chain(10, 100, (index) => {
                                this.selected.push(picked[index]);
                                this.playSound('/sounds/click.mp3');

                                if(index === 9) this.autoPickInProcess = false;
                            });
                        } },
                        { label: this.$i18n.t('general.clear'), preventMark: true, callback: () => {
                            if(this.autoPickInProcess) return;
                            this.selected = [];
                        } } ] } },
                    { name: 'auto-bets' },
                    { name: 'play' },
                    { name: 'footer', data: { buttons: ['help', 'sound', 'stats'] } },
                    { name: 'history', data: { scrollable: false } }
                ];
            }
        }
    }
</script>

<style lang="scss">
    @import 'resources/sass/variables';

    .game-history {
        display: flex;
        height: 84px;
    }

    .history-keno {
        display: inline-flex;
        flex-direction: column;
        text-align: center;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 9px;
        border-radius: 3px;
        line-height: 17px;

        div:first-child {
            font-size: 13px;
        }

        div:last-child {
            font-size: 11px;
        }

        @include themed() {
            $color: rgba(t('text'), 0.25);
            color: $color !important;
            border: 1px solid $color !important;
        }
    }

    .theme--light .history-keno {
        color: black !important;
    }

    .game-content-keno {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .keno_grid {
        display: grid;
        grid-gap: 10px;
        grid-template-columns: repeat(8, 65px);
        grid-template-rows: repeat(5, 65px);
        grid-auto-flow: column;
        position: relative;
        width: 100%;
        justify-content: center;
        transform: translateY(-42px);

        @include themed() {
            div {
                background: rgba(t('text'), 0.1);
                text-align: center;
                transition: background-color 0.3s ease, color 0.3s ease, top 0.3s ease, border-color 0.3s ease;
                position: relative;
                border-bottom: 4px solid rgba(t('text'), 0.05);
                border-top: 4px solid transparent;
                border-radius: 2px 2px 3px 3px;

                svg {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    opacity: 0;
                    transition: opacity 0.3s ease;
                }

                &:hover {
                    cursor: pointer;
                    background: rgba(t('text'), 0.25);
                    border-bottom-color: rgba(t('text'), 0.15);
                    top: -2px;
                }

                span {
                    position: absolute;
                    left: 50%;
                    top: 50%;
                    transform: translate(-50%, -50%);
                    font-size: 1.2em;
                    font-weight: 600;
                }
            }

            div.active {
                background: t('secondary') !important;
                border-bottom-color: darken(t('secondary'), 5%) !important;
                color: white !important;
            }

            div.selected {
                background: rgba(t('text'), 0.05) !important;
                border-bottom-color: transparent !important;
                border-top-color: rgba(t('text'), 0.1) !important;
                color: #e74c3c !important;
            }

            div.active.selected {
                background: t('secondary') !important;
                border-bottom-color: darken(t('secondary'), 5%) !important;
                color: $gray-700 !important;

                svg {
                    opacity: 1;
                }

                span {
                    z-index: 10;
                    margin-top: -1px;
                }
            }
        }

    }

    .overview-render-target .keno_grid {
        grid-template-columns: repeat(8, 35px);
        grid-template-rows: repeat(5, 35px);
        grid-gap: 7px;
        transform: unset !important;
        margin-bottom: 20px;
        margin-top: 10px;

        span {
            font-size: 11px;
        }
    }

    @media (max-width: 1370px){
        .keno_grid {
            grid-template-columns: repeat(8, 40px);
            grid-template-rows: repeat(5, 40px);
            grid-gap: 5px;

            font-size: 13px;
        }
    }

    @include media-breakpoint-down(md) {
        .keno_grid {
            grid-template-columns: repeat(8, 10vw);
            grid-template-rows: repeat(5, 10vw);
            grid-gap: 1.5vw;

            font-size: 11px;
        }

        .game-content-keno {
            margin-bottom: 120px;
        }
    }
</style>
