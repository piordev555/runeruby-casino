<script>
    import Bus from '../../bus';

    export default {
        methods: {
            open(currencies) {
                Bus.$emit('modal:new', {
                    name: 'rankings',
                    component: {
                        data() {
                            return {
                                currencySelected: null,
                                expand: false,
                                currenciesVisible: null,

                                leaderboard: null
                            }
                        },
                        watch: {
                            currencySelected() {
                                this.load();
                            }
                        },
                        created() {
                            this.currenciesVisible = currencies;
                            this.currencySelected = this.currenciesVisible[Object.keys(this.currenciesVisible).filter(e => e.includes('_'))[0]];
                            this.load();
                        },
                        methods: {
                            load() {
                                this.leaderboard = null;

                                axios.post('/api/leaderboard', {
                                    positions: 100,
                                    type: 'all',
                                    currency: this.currencySelected.id,
                                    orderBy: 'wager'
                                }).then(({ data }) => this.leaderboard = data);
                            }
                        },
                        template: `
                            <div>
                                <div class="walletExchangeSelector" @click="expand = !expand" v-if="currencySelected">
                                    <div class="wesContainer">
                                        <div class="icon"><icon :icon="currencySelected.icon" :style="{ color: currencySelected.style }"></icon></div>
                                        <div class="name">{{ currencySelected.name }}</div>
                                    </div>
                                    <div class="exchangeList" v-if="expand">
                                        <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                                            <div class="elEntry" v-for="currency in currenciesVisible" v-if="currency.balance" @click="currencySelected = currency">
                                                <div class="icon"><icon :icon="currency.icon" :style="{ color: currency.style }"></icon></div>
                                                <div class="name">{{ currency.name }}</div>
                                            </div>
                                        </overlay-scrollbars>
                                    </div>
                                </div>
                                <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light rankings-table' }" v-if="leaderboard">
                                    <table class="live-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ $t('general.bets.player') }}</th>
                                                <th>{{ $t('general.profile.wager') }}</th>
                                                <th>{{ $t('general.profile.profit') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="live_games">
                                            <tr v-for="(entry, i) in leaderboard" :class="i === 0 ? 'first' : (i === 1 ? 'second' : (i === 2 ? 'third' : ''))">
                                                <th>
                                                    <div>
                                                        {{ i + 1 }}
                                                    </div>
                                                </th>
                                                <th>
                                                    <div class="playerTh">
                                                        <router-link :to="'/profile/' + entry.entry.user">{{ entry.user.name }}</router-link>
                                                    </div>
                                                </th>
                                                <th>
                                                    <div>
                                                        {{ entry.entry.wager.toFixed(currencySelected.id.startsWith('local_') ? 2 : 8) }}
                                                    </div>
                                                </th>
                                                <th>
                                                    <div>
                                                        {{ entry.entry.profit.toFixed(currencySelected.id.startsWith('local_') ? 2 : 8) }}
                                                    </div>
                                                </th>
                                            </tr>
                                        </tbody>
                                    </table>
                                </overlay-scrollbars>
                                <loader v-else></loader>
                            </div>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.rankings {
        @include themed() {
            max-width: 450px;

            .first th div {
                color: #ffdf7c;

                a {
                    color: #ffdf7c;
                }
            }

            .second th div {
                color: #bfdffb;

                a {
                    color: #bfdffb;
                }
            }

            .third th div {
                color: #d0898f;

                a {
                    color: #d0898f;
                }
            }

            .playerTh {
                max-width: 115px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .modal_content {
                padding-top: 20px;
            }

            .rankings-table {
                max-height: 350px;
                min-height: 300px;
            }

            .walletExchangeSelector {
                position: relative;
                cursor: pointer;
                margin-right: 15px;
                width: 100px;

                &:last-child {
                    margin-right: 0;
                }

                .exchangeList {
                    position: absolute;
                    left: 0;
                    width: 100%;
                    z-index: 5;

                    .os-host {
                        max-height: 300px;
                    }

                    .elEntry {
                        background: t('body');
                        transition: background .3s ease;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        padding: 10px 0;

                        &:hover {
                            background: lighten(t('body'), 5%);
                        }

                        .icon {
                            width: 25px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            margin-right: 5px;
                        }
                    }
                }

                .wesContainer {
                    display: flex;
                    padding: 6px 13px;
                    border-radius: 3px;
                    background: t('body');
                    transition: background .3s ease;

                    &:hover {
                        background: lighten(t('body'), 5%);
                    }

                    .icon {
                        width: 30px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin-right: 5px;
                    }

                    .name {
                        margin-right: 10px;
                    }
                }
            }
        }
    }
</style>
