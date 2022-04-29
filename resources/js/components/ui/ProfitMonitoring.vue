<template>
    <div class="draggableWindow" v-if="show">
        <div class="head">
            {{ $t('general.profit_monitoring.title') }}
            <i class="far fa-redo-alt" @click="reset"></i>
            <i class="fal fa-times" @click="show = false"></i>
        </div>
        <div class="content">
            <div class="row">
                <div class="col-6">
                    {{ $t('general.profit_monitoring.wins') }}
                    <span class="float-right text-success">{{ wins }}</span>
                </div>
                <div class="col-6">
                    {{ $t('general.profit_monitoring.losses') }}
                    <span class="float-right text-danger">{{ losses }}</span>
                </div>
            </div>
            <div class="profit-monitor-chart"></div>
            <div class="row">
                <div class="col-6">
                    <div>{{ $t('general.profit_monitoring.wager') }}</div>
                    <span>{{ wager.toFixed(currency.startsWith('local_') ? 2 : 8) }}</span> <icon :icon="currencies[currency].icon" :style="{ color: currencies[currency].style }"></icon>
                </div>
                <div class="col-6">
                    <div>{{ $t('general.profit_monitoring.profit') }}</div>
                    <span>{{ profit.toFixed(this.currency.startsWith('local_') ? 2 : 8) }}</span> <icon :icon="currencies[currency].icon" :style="{ color: currencies[currency].style }"></icon>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                show: false,

                wager: 0,
                profit: 0,
                wins: 0,
                losses: 0,

                chart: null,
                series: [],

                init: false
            }
        },
        created() {
            Bus.$on('event:liveGame', (e) => {
                if(this.isGuest) return;

                if(e.game.type !== 'multiplayer' || e.game.user !== this.user.user._id) return;
                setTimeout(() => this.pushStats(e.game), e.delay);
            });

            Bus.$on('profitMonitoring:toggle', () => this.show = !this.show);
            Bus.$on('profitMonitoring:pushStats', (game) => this.pushStats(game));
        },
        updated() {
            if(!this.init) {
                this.init = true;
                const chart = new ApexCharts(document.querySelector(".profit-monitor-chart"), {
                    series: [{
                        name: this.$i18n.t('general.profit'),
                        data: []
                    }],
                    noData: {
                        text: this.$i18n.t('general.profit_monitoring.no_data')
                    },
                    chart: {
                        zoom: {
                            enabled: false
                        },
                        toolbar: {
                            show: false
                        },
                        type: 'area',
                        height: 200
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        colors: [this.theme === 'dark' ? '#ec487f' : '#1652f0']
                    },
                    fill: {
                        type: 'gradient',
                        colors: [this.theme === 'dark' ? '#ec487f' : '#1652f0'],
                        gradient: {
                            shadeIntensity: 1,
                            inverseColors: false,
                            opacityFrom: 0.5,
                            opacityTo: 0,
                            stops: [0, 90, 100]
                        }
                    },
                    xaxis: {
                        type: 'numeric',
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        labels: {
                            show: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    yaxis: {
                        tickAmount: 1,
                        floating: false,

                        labels: {
                            show: false
                        },
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false
                        }
                    },
                    tooltip: {
                        x: {
                            show: false,
                        },
                        fixed: {
                            enabled: false,
                            position: 'topRight'
                        }
                    },
                    grid: {
                        borderColor: 'transparent',
                        yaxis: {
                            lines: {
                                offsetX: -30
                            }
                        },
                        padding: {
                            left: -10,
                            right: 0
                        }
                    }
                });
                chart.render();

                let x, y;
                $('.draggableWindow .head').on('mousedown', function (e) {
                    if (e.offsetX === undefined) {
                        x = e.pageX - $(this).offset().left;
                        y = e.pageY - $(this).offset().top;
                    } else {
                        x = e.offsetX;
                        y = e.offsetY;
                    }

                    $('body').addClass('noselect');
                });

                $('body').on('mouseup', function (e) {
                    $('body').removeClass('noselect');
                });

                $('body').on('mousemove', function (e) {
                    if ($(this).hasClass('noselect')) $('.draggableWindow').offset({
                        top: e.pageY - y,
                        left: e.pageX - x
                    });
                });

                this.chart = chart;
                this.reset();
            }
        },
        computed: {
            ...mapGetters(['currencies', 'currency', 'gameInstance', 'isGuest', 'user', 'theme'])
        },
        methods: {
            pushStats(game) {
                this.wager += this.gameInstance.bet;
                if(game.win || game.status === 'win') {
                    this.wins += 1;
                    this.profit += game.profit - this.gameInstance.bet;
                } else {
                    this.losses += 1;
                    this.profit -= this.gameInstance.bet;
                }

                this.pushToSeries();
                this.update();
            },
            pushToSeries() {
                if(this.series.length === 0) this.series.push({
                    x: 0,
                    y: 0.00
                });
                this.series.push({
                    x: this.series.length + 1,
                    y: parseFloat(this.profit.toFixed(this.currency.startsWith('local_') ? 2 : 8))
                });
            },
            reset() {
                this.wager = 0;
                this.profit = 0;
                this.wins = 0;
                this.losses = 0;
                this.series = [];

                this.update();
            },
            update() {
                if(this.chart) this.chart.updateSeries([{
                    name: this.$i18n.t('general.profit'),
                    data: this.series
                }]);
            }
        }
    }
</script>
