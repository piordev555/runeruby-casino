<template>
    <div v-if="gameInstance.bettingType === 'auto'">
        <div class="auto-bet-container">
            <div class="auto-bet-overlay" v-if="gameInstance.game.autoBetSettings.state"></div>
            <div class="game-sidebar-label mt-2">{{ $t('general.bets.games') }}</div>
            <div class="wager-classic input-override">
                <input class="autoBetGames" :readonly="this.infinity" type="text" v-model="this.infinity ? '∞' : gameInstance.game.autoBetSettings.games" @input="setGames($event.target.value)" :placeholder="$t('general.bets.games')">
                <div class="wager-input-controls">
                    <div :class="'control autoBetInfinity ' + (this.infinity ? 'active' : '')" @click="toggleInfinity"><i class="far fa-infinity"></i></div>
                </div>
            </div>
            <div class="game-sidebar-label mt-2">{{ $t('general.bets.on_win') }}</div>
            <div class="auto-bet-controls">
                <button :class="'btn btn-primary ' + (gameInstance.game.autoBetSettings['win'].action === 'reset' ? 'active' : '')" @click="setAction('win', 'reset')">{{ $t('general.bets.reset') }}</button>
                <button :class="'btn btn-primary ' + (gameInstance.game.autoBetSettings['win'].action === 'increase' ? 'active' : '')" @click="setAction('win', 'increase')">{{ $t('general.bets.increase') }}</button>
                <span class="input-append-percent"><input type="text" value="0" @input="setActionValue('win', $event.target.value)"><span>%</span></span>
            </div>
            <div class="game-sidebar-label mt-2">{{ $t('general.bets.on_loss') }}</div>
            <div class="auto-bet-controls">
                <button :class="'btn btn-primary ' + (gameInstance.game.autoBetSettings['loss'].action === 'reset' ? 'active' : '')" @click="setAction('loss', 'reset')">{{ $t('general.bets.reset') }}</button>
                <button :class="'btn btn-primary ' + (gameInstance.game.autoBetSettings['loss'].action === 'increase' ? 'active' : '')" @click="setAction('loss', 'increase')">{{ $t('general.bets.increase') }}</button>
                <span class="input-append-percent"><input type="text" value="0" @input="setActionValue('loss', $event.target.value)"><span>%</span></span>
            </div>
            <div class="custom-control custom-checkbox mt-2">
                <label>
                    <input type="checkbox" class="custom-control-input victoryStop" @click="toggleVictoryStop" :checked="gameInstance.game.autoBetSettings.stopOnWin">
                    <div class="custom-control-label">{{ $t('general.bets.victory_stop') }}</div>
                </label>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            toggleVictoryStop() {
                const instance = this.gameInstance;
                instance.game.autoBetSettings.stopOnWin = !instance.game.autoBetSettings.stopOnWin;
                this.$store.dispatch('setGameInstance', instance);
            },
            setAction(on, action) {
                const instance = this.gameInstance;
                instance.game.autoBetSettings[on].action = action;
                this.$store.dispatch('setGameInstance', instance);
            },
            setActionValue(on, value) {
                const instance = this.gameInstance;
                instance.game.autoBetSettings[on].value = parseInt(value);
                this.$store.dispatch('setGameInstance', instance);
            },
            setGames(value) {
                const instance = this.gameInstance;
                instance.game.autoBetSettings.games = parseInt(value);
                this.$store.dispatch('setGameInstance', instance);
            },
            toggleInfinity() {
                this.infinity = !this.infinity;
                this.setGames(0);
            }
        },
        data() {
            return {
                infinity: true
            }
        },
        computed: {
            ...mapGetters(['gameInstance'])
        },
        mounted() {
            const instance = this.gameInstance;
            instance.game.autoBetSettings.supported = true;
            this.$store.dispatch('setGameInstance', instance);
        }
    }
</script>
