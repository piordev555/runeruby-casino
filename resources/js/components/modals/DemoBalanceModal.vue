<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'demo_balance',
                    title: 'general.head.wallet_demo',
                    component: {
                        computed: {
                            ...mapGetters(['currency', 'currencies'])
                        },
                        data() {
                            return {
                                getting: false
                            }
                        },
                        methods: {
                            get() {
                                this.getting = true;
                                axios.post('/api/promocode/demo').then(() => Bus.$emit('close')).catch(() => this.getting = false);
                            }
                        },
                        template: `
                            <div>
                                <div class="divider">
                                    <div class="line"></div>
                                    <i class="fas fa-coins"></i>
                                    <div class="line"></div>
                                </div>
                                <div class="wallet-content">
                                    <div class="notice" v-if="currencies[currency].balance.demo > 0">
                                        {{ $t('general.wallet.demo.error') }}
                                    </div>
                                    <div class="notice" v-else>
                                        <div class="btn btn-primary" @click="get" :disabled="getting">{{ $t('general.wallet.demo.obtain') }}</div>
                                    </div>
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

    .xmodal.demo_balance {
        max-width: 400px;

        .wallet-content {
            margin-top: 15px;
        }

        @include themed() {
            .notice {
                border-radius: 3px;
                border: 1px solid rgba(t('text'), 0.2);
                padding: 20px;
                text-align: center;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 95px;
                flex-direction: column;
            }
        }
    }
</style>
