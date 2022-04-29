<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        computed: {
            ...mapGetters(['isGuest', 'currencies'])
        },
        methods: {
            open(game_id, api_id) {
                Bus.$emit('modal:new', {
                    name: 'overview',
                    component: {
                        computed: {
                            ...mapGetters(['channel'])
                        },
                        data() {
                            return {
                                response: null
                            }
                        },
                        created() {
                            this.whisper('Info', { game_id: game_id }).then((response) => this.response = response);
                        },
                        methods: {
                            close() {
                                Bus.$emit('modal:close');
                            },
                            share(type) {
                                if(!this.response) return;

                                const share_url = `${window.location.origin}?game=${this.response.info.game}-${this.response.info._id}`;
                                switch (type) {
                                   case "vk":
                                       window.open(`https://vk.com/share.php?url=${share_url}&title=${this.$i18n.t('general.share_text')}`, '_blank');
                                       break;
                                   case "chat":
                                       if(this.isGuest) return;
                                       Bus.$emit('modal:close');
                                       axios.post('/api/chat/link_game', { id: game_id, channel: this.channel });
                                       break;
                                   case "twitter":
                                       window.open(`https://twitter.com/intent/tweet?hashtags=runeruby.com&text=${this.$i18n.t('general.share_text')}&url=${share_url}`, '_blank');
                                       break;
                                   case "telegram":
                                       window.open(`https://telegram.me/share/url?url=${share_url}&text=${this.$i18n.t('general.share_text')}`, '_blank');
                                       break;
                               }
                            }
                        },
                        template: `
                            <div>
                                <div class="overview-share-options" v-if="response">
                                    <a @click="share('chat')" v-if="!isGuest" href="javascript:void(0)">
                                        <i class="fas fa-comments"></i>
                                    </a>
                                    <a @click="share('vk')" href="javascript:void(0)">
                                        <i class="fab fa-vk"></i>
                                    </a>
                                    <a @click="share('twitter')" href="javascript:void(0)">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a @click="share('telegram')" href="javascript:void(0)">
                                        <i class="fab fa-telegram"></i>
                                    </a>
                                </div>

                                <loader v-if="!response"></loader>

                                <div v-if="response">
                                    <strong>{{ response.metadata.name }}</strong> #{{ response.info.id }}
                                </div>

                                <div class="overview-player" v-if="response">
                                    <div>{{ $t('general.bets.player') }}:
                                    <router-link :to="'/profile/' + response.info.user" @click="close" v-if="!response.user.private_bets">
                                       {{ response.user.name }}
                                    </router-link>
                                    <a href="javascript:void(0)" v-else>{{ $t('general.bets.hidden_name') }}</a>
                                </div>
                                </div>
                                <div class="overview-bet" v-if="response">
                                    <div class="option">{{ $t('general.bets.bet') }}: <span><icon :icon="currencies[response.info.currency].icon" :style="{ color: currencies[response.info.currency].style }"></icon> {{ rawBitcoin(response.info.currency, response.info.wager) }}</span></div>
                                    <div class="option">{{ $t('general.bets.mul') }}: <span>{{ response.info.status === 'lose' ? '0.00' : response.info.multiplier.toFixed(2) }}x</span></div>
                                    <div class="option">{{ $t('general.bets.win') }}: <span><icon :icon="currencies[response.info.currency].icon" :style="{ color: currencies[response.info.currency].style }"></icon> {{ rawBitcoin(response.info.currency, response.info.profit) }}</span></div>
                                </div>

                                <template v-if="response.info.server_seed">
                                    <div class="client_seed mt-2" v-if="response && response.info.nonce !== -1">
                                        <div>{{ $t('general.fairness.client_seed') }}</div>
                                        <router-link :to="'/fairness?verify='+response.info.game+'-'+response.info.server_seed+'-'+response.info.client_seed+'-'+response.info.nonce" @click.native="close">{{ response.info.client_seed }}</router-link>
                                    </div>
                                    <div class="server_seed mt-2" v-if="response && response.info.nonce !== -1">
                                        <div>{{ $t('general.fairness.server_seed') }}</div>
                                        <router-link :to="'/fairness?verify='+response.info.game+'-'+response.info.server_seed+'-'+response.info.client_seed+'-'+response.info.nonce" @click.native="close">{{ response.info.server_seed }}</router-link>
                                    </div>
                                    <div class="nonce mt-2" v-if="response && response.info.nonce !== -1">
                                        <div>{{ $t('general.fairness.nonce') }}</div>
                                        <router-link :to="'/fairness?verify='+response.info.game+'-'+response.info.server_seed+'-'+response.info.client_seed+'-'+response.info.nonce" @click.native="close">{{ response.info.nonce }}</router-link>
                                    </div>
                                    <div class="nonVerifiable mt-2" v-if="response && response.info.nonce === -1">
                                        {{ $t('general.non_verifiable') }}
                                    </div>
                                </template>
                            </div>
                        </div>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.overview {
        width: 450px;

        .overview-share-options {
            margin-top: 15px;
        }

        .option span {
            white-space: nowrap;
            width: 90%;
            text-overflow: ellipsis;
            overflow: hidden;
        }

        .nonVerifiable {
            text-align: center;
            color: lightgray;
        }
    }
</style>
