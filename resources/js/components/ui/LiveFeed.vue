<template>
    <div class="container-fluid">
        <div class="live">
            <div class="header">
                <div class="live_header">
                    <div class="pulsating-circle"></div>
                    <span class="liveAnimation">{{ $t('general.latest_bets') }}</span>
                </div>
                <div class="live_tabs">
                    <div class="tabs">
                        <div v-if="!isGuest" @click="$store.dispatch('setLiveChannel', 'mine')" :class="`tab ${liveChannel === 'mine' ? 'active' : ''}`">{{ $t('general.bets.mine') }}</div>
                        <div @click="$store.dispatch('setLiveChannel', 'all')" :class="`tab ${liveChannel === 'all' ? 'active' : ''}`">{{ $t('general.bets.all') }}</div>
                        <div @click="$store.dispatch('setLiveChannel', 'high_rollers')" :class="`tab ${liveChannel === 'high_rollers' ? 'active' : ''}`">{{ $t('general.bets.high_rollers') }}</div>
                        <div @click="$store.dispatch('setLiveChannel', 'lucky_wins')" :class="`tab ${liveChannel === 'lucky_wins' ? 'active' : ''}`">{{ $t('general.bets.lucky_wins') }}</div>
                    </div>
                    <select @change="$store.dispatch('setLiveFeedEntryCount', parseFloat(liveFeedEntriesWrap))" v-model="liveFeedEntriesWrap">
                        <option value="10" :selected="liveFeedEntries === 10">10</option>
                        <option value="25" :selected="liveFeedEntries === 25">25</option>
                        <option value="50" :selected="liveFeedEntries === 50">50</option>
                    </select>
                </div>
            </div>
            <div class="live_table_container">
                <loader v-if="!lastGames"></loader>
                <table class="live-table" v-else>
                    <thead>
                        <tr>
                            <th>{{ $t('general.bets.game') }}</th>
                            <th>{{ $t('general.bets.player') }}</th>
                            <th class="d-none d-md-table-cell">{{ $t('general.bets.time') }}</th>
                            <th class="d-none d-md-table-cell">{{ $t('general.bets.bet') }}</th>
                            <th class="d-none d-md-table-cell">{{ $t('general.bets.mul') }}</th>
                            <th>{{ $t('general.bets.win') }}</th>
                        </tr>
                    </thead>
                    <tbody class="live_games">
                        <tr v-for="game in lastGames">
                            <th>
                                <div class="gameIcon">
                                    <router-link :to="`/casino/game/${game.metadata.id}`" tag="div" class="icon d-none d-md-inline-block">
                                        <icon :icon="game.metadata.icon"></icon>
                                    </router-link>
                                    <div class="name">
                                        <div><router-link :to="`/casino/game/${game.metadata.id}`">{{ game.metadata.name }}</router-link></div>
                                        <a href="javascript:void(0)" @click="openOverviewModal(game.game._id, game.game.game)">{{ $t('general.overview') }}</a>
                                    </div>
                                </div>
                            </th>
                            <th>
                                <div>
                                    <router-link :to="game.user.private_bets !== true || (isGuest ? false : user.user.access !== 'user') ? `/profile/${game.user._id}` : $route.path">
                                        <span v-if="game.user.private_bets && (isGuest ? true : user.user.access === 'user')"><icon icon="fad fa-user-secret mr-1"></icon> {{ $t('general.bets.hidden_name') }}</span>
                                        <span v-else>{{ game.user.name }}</span>
                                    </router-link>
                                </div>
                            </th>
                            <th class="d-none d-md-table-cell">
                                <div><span>{{ new Date(game.game.created_at).toLocaleTimeString() }}</span></div>
                            </th>
                            <th data-highlight class="d-none d-md-table-cell">
                                <div>
                                    <unit :to="game.game.currency" :value="game.game.wager"></unit>
                                    <icon :icon="currencies[game.game.currency].icon" :style="{ color: currencies[game.game.currency].style }"></icon>
                                </div>
                            </th>
                            <th data-highlight class="d-none d-md-table-cell">
                                <div>{{ (game.game.status === 'win' || game.game.multiplier < 1 ? game.game.multiplier : 0).toFixed(2) }}x</div>
                            </th>
                            <th>
                                <div :class="game.game.status === 'win' ? 'live-win' : ''">
                                    <unit :to="game.game.currency" :value="game.game.profit"></unit>
                                    <icon :icon="currencies[game.game.currency].icon" :style="{ color: currencies[game.game.currency].style }"></icon>
                                </div>
                            </th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import Bus from '../../bus';
    import OverviewModal from '../modals/OverviewModal';

    export default {
        data() {
            return {
                lastGames: null,
                liveFeedEntriesWrap: 10
            }
        },
        watch: {
            liveChannel() {
                this.getGames();
            },
            liveFeedEntries() {
                this.getGames();
            },
            lastGames() {
                if(this.lastGames && this.lastGames.length >= this.liveFeedEntries) this.lastGames.pop();
            }
        },
        computed: {
            ...mapGetters(['liveFeedEntries', 'isGuest', 'liveChannel', 'user', 'currencies'])
        },
        created() {
            this.getGames();
            this.liveFeedEntriesWrap = this.liveFeedEntries;

            Bus.$on('event:liveGame', (e) => {
                if(this.liveChannel === 'mine' && e.user._id !== this.user.user._id) return;
                if(this.liveChannel === 'lucky_wins' && (e.game.multiplier < 10 || e.game.status !== 'win')) return;
                if(this.liveChannel === 'high_rollers' && e.game.wager < this.currencies[e.game.currency].highRollerRequirement) return;
                setTimeout(() => this.lastGames.unshift(e), e.delay);
            });
        },
        methods: {
            getGames() {
                this.lastGames = null;
                axios.post('/api/data/latestGames', {
                    type: this.liveChannel,
                    count: this.liveFeedEntries
                }).then(({ data }) => this.lastGames = data.reverse());
            },
            openOverviewModal(id, game) {
                OverviewModal.methods.open(id, game);
            }
        }
    }
</script>
