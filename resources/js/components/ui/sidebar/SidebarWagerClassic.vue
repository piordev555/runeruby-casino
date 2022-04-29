<template>
    <div class="wager-classic wager-selector">
        <input type="text" v-model.lazy="bet" v-money="money" :placeholder="$t('general.wager')" :disabled="gameInstance.game && gameInstance.game.extendedState === 'in-progress'">
        <div class="wager-input-controls">
            <div class="control" @click="gameInstance.game && gameInstance.game.extendedState === 'in-progress' ? null : bet = (parseFloat(bet) / 2).toFixed(currency.startsWith('local_') ? 2 : 8)"><i class="fas fa-slash"></i></div>
            <div class="control" @click="gameInstance.game && gameInstance.game.extendedState === 'in-progress' ? null : bet = (parseFloat(bet) * 2).toFixed(currency.startsWith('local_') ? 2 : 8)"><i class="fas fa-asterisk"></i></div>
        </div>
        <div class="wager-controls">
            <div class="control" @click="gameInstance.game && gameInstance.game.extendedState === 'in-progress' ? null : bet = (parseFloat(bet) + (currency.startsWith('local_') ? 1000.00 : 0.001)).toFixed(currency.startsWith('local_') ? 2 : 8)">+{{ currency.startsWith('local_') ? 1000.00 : 0.001 }}</div>
            <div class="control" @click="gameInstance.game && gameInstance.game.extendedState === 'in-progress' ? null : bet = (parseFloat(bet) + (currency.startsWith('local_') ? 5000.00 : 0.10)).toFixed(currency.startsWith('local_') ? 2 : 8)">+{{ currency.startsWith('local_') ? 5000.00 : 0.10 }}</div>
            <div class="control" @click="gameInstance.game && gameInstance.game.extendedState === 'in-progress' ? null : bet = (parseFloat(bet) + (currency.startsWith('local_') ? 10000.00 : 0.25)).toFixed(currency.startsWith('local_') ? 2 : 8)">+{{ currency.startsWith('local_') ? 10000.00 : 0.25 }}</div>
            <div class="control" @click="gameInstance.game && gameInstance.game.extendedState === 'in-progress' ? null : bet = (parseFloat(bet) + (currency.startsWith('local_') ? 50000.00 : 0.50)).toFixed(currency.startsWith('local_') ? 2 : 8)">+{{ currency.startsWith('local_') ? 50000.00 : 0.50 }}</div>
        </div>
    </div>
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

    export default {
        props: {
            data: {
                type: Object
            }
        },
        watch: {
            bet() {
                const instance = this.gameInstance;
                instance.bet = parseFloat(this.bet);
                this.$store.dispatch('setGameInstance', instance);

                Bus.$emit('sidebar:update', { type: 'bet', value: this.bet });
            },
            currency() {
                this.setPrecision();
            }
        },
        data() {
            return {
                bet: 0.00000000,
                money: {
                    decimal: '.',
                    thousands: '',
                    prefix: '',
                    suffix: '',
                    precision: 8,
                    masked: false
                }
            }
        },
        computed: {
            ...mapGetters(['isGuest', 'gameInstance', 'currency'])
        },
        methods: {
            setPrecision() {
                this.bet = this.currency.startsWith('local_') ? 100000.00 : 0.00000000;
                this.money.precision = this.currency.startsWith('local_') ? 2 : 8;
            }
        },
        mounted() {
            this.setPrecision();

            Bus.$on('sidebar:updateBet', (e) => this.bet = e.bet.toFixed(this.currency.startsWith('local_') ? 2 : 8));
        }
    }
</script>
