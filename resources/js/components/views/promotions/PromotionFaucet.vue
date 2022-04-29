<template>
    <div>
        <div class="wheel"></div>
        <div class="btn btn-primary btn-block mt-2 wheelSpin">{{ $t('general.spin') }}</div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                interval: null
            }
        },
        computed: {
            ...mapGetters(['currencies', 'currency', 'user'])
        },
        mounted() {
            const timeout = () => {
                if(this.interval != null) {
                    clearInterval(this.interval);
                    this.interval = null;
                }

                if(window.next && +new Date() / 1000 < window.next) {
                    const timer = () => {
                        const diff = ((window.next - (Date.now() / 1000)) | 0);
                        let hours = Math.floor((diff % (60 * 60 * 24)) / (60 * 60));
                        let minutes = ((diff % 3600) / 60) | 0;
                        let seconds = (diff % 60) | 0;

                        if(hours === 0 && minutes === 0 && seconds < 1) {
                            clearInterval(this.interval);
                            $('.wheelSpin').toggleClass('disabled', false).html(this.$i18n.t('bonus.spin_now'));
                            return;
                        }

                        hours = hours < 10 ? "0" + hours : hours;
                        minutes = minutes < 10 ? "0" + minutes : minutes;
                        seconds = seconds < 10 ? "0" + seconds : seconds;

                        $('.wheelSpin').html(this.$i18n.t('bonus.next_spin') + `${hours}:${minutes}:${seconds}`).toggleClass('disabled', true);
                    };

                    this.interval = setInterval(() => {
                        if($('.wheelSpin').html().includes('00:00:00')) {
                            clearInterval(this.interval);
                            return;
                        }

                        timer();
                    }, 1000);
                    timer();
                } else {
                    $('#reload').html(this.$i18n.t('bonus.spin_now'));
                    $('.wheelSpin').toggleClass('disabled', false);
                }
            };

            window.next = this.user.user.bonus_claim ? +new Date(this.user.user.bonus_claim) / 1000 : 0;
            timeout();

            const p = this.currencies[this.currency].price, rewards = [
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 0.30)),
                    color: '#f46e42'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 0.40)),
                    color: '#508bf0'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 0.50)),
                    color: '#df1347'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 0.80)),
                    color: '#d1d652'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 1.00)),
                    color: '#ffc645'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 2.00)),
                    color: '#f46e42'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 3.00)),
                    color: '#508bf0'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 5.00)),
                    color: '#df1347'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 7.00)),
                    color: '#d1d652'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 10.00)),
                    color: '#ffc645'
                },
                {
                    value: this.rawBitcoin(this.currency, this.usdToToken(p, 50.00)),
                    color: '#f46e42'
                }
            ];

            let slides = [];
            _.forEach(rewards, (reward) => {
                slides.push({
                    text: `${reward.value} ${this.currencies[this.currency].name.substr(0, 3)}`,
                    value: slides.length,
                    border: {
                        radius: 3.25,
                        fill: reward.color
                    }
                });
            });

            $('.wheel').wheel({
                slices: slides,
                selector: 'value',
                width: 350,
                text: {
                    color: "white",
                    size: 11,
                    offset: 5,
                    arc: false
                },
                outer: {
                    width: 0,
                    color: 'transparent'
                },
                inner: {
                    width: 11,
                    color: '#222127'
                },
                line: {
                    width: 3,
                    color: '#222127'
                },
                slice: {
                    background: '#2a2a2f'
                }
            });

            $('.wheel').wheel('onStep', () => this.playSound('/sounds/tick.mp3'));

            $('.wheel').wheel('onComplete', () => {
                timeout();
                $('.wheelSpin').addClass('disabled');
            });

            $('.wheelSpin').on('click', () => {
                if($('.wheelSpin').hasClass('disabled')) return;
                $('.wheelSpin').toggleClass('disabled', true);

                axios.post('/api/promocode/bonus').then(({ data }) => {
                    window.next = data.next;
                    $('.wheel').wheel('start', data.slice);
                }).catch((error) => {
                    $('.wheelSpin').toggleClass('disabled', false);
                    if(error.response.data.code === 2) this.$toast.error(this.$i18n.t('general.error.should_have_empty_balance'));
                });
            });
        }
    }
</script>
