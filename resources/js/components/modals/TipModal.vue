<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'tip',
                    component: {
                        data() {
                            return {
                                to: '',
                                amount: 0,
                                public: true,

                                disabled: false,

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
                            ...mapGetters(['channel'])
                        },
                        methods: {
                            tip() {
                                this.disabled = true;

                                axios.post('/api/chat/tip', {
                                    amount: parseFloat(this.amount),
                                    user: this.to,
                                    public: this.public,
                                    channel: this.channel
                                }).then(() => Bus.$emit('modal:close')).catch((error) => {
                                    switch (error.response.data.code) {
                                        case 1:
                                            this.$toast.error(this.$i18n.t('general.chat_commands.modal.tip.invalid_name'));
                                            break;
                                        case 2:
                                            this.$toast.error(this.$i18n.t('general.chat_commands.modal.tip.invalid_amount'));
                                            break;
                                    }

                                    this.disabled = false;
                                });
                            }
                        },
                        template: `
                            <div>
                                <div class="cc_label">{{ $t('general.chat_commands.modal.tip.user') }}</div>
                                <input v-model="to" type="text">
                                <div class="cc_label">{{ $t('general.chat_commands.modal.tip.amount') }}</div>
                                <input v-model="amount" v-money="money" type="text">

                                <div class="custom-control custom-checkbox mt-2">
                                    <label>
                                        <input type="checkbox" class="custom-control-input" v-model="public">
                                        <div class="custom-control-label">{{ $t('general.chat_commands.modal.tip.make_public') }}</div>
                                    </label>
                                </div>

                                <button class="btn btn-primary" @click="tip" :disabled="disabled">{{ $t('general.chat_commands.modal.tip.send') }}</button>
                            </div>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.tip {
        max-width: 250px;
        padding-top: 20px !important;

        .cc_label {
            margin-top: 10px;
            margin-bottom: 5px;
        }
    }
</style>
