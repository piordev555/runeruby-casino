<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'rainModal',
                    component: {
                        data() {
                            return {
                                amount: 0,
                                number_of_users: 10,

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
                            rain() {
                                this.disabled = true;

                                axios.post('/api/chat/rain', {
                                    amount: parseFloat(this.amount),
                                    users: this.number_of_users,
                                    channel: this.channel
                                }).then(() => Bus.$emit('modal:close')).catch((error) => {
                                    switch (error.response.data.code) {
                                        case 1:
                                            this.$toast.error(this.$i18n.t('general.chat_commands.modal.rain.invalid_users_length'));
                                            break;
                                        case 2:
                                            this.$toast.error(this.$i18n.t('general.chat_commands.modal.rain.invalid_amount'));
                                            break;
                                    }

                                    this.disabled = false;
                                });
                            }
                        },
                        template: `
                                <div>
                                    <div class="cc_label">{{ $t('general.chat_commands.modal.rain.amount') }}</div>
                                    <input v-model="amount" v-money="money" type="text">

                                    <div class="cc_label">{{ $t('general.chat_commands.modal.rain.number_of_users') }}</div>
                                    <input v-model="number_of_users" type="text">

                                    <button class="btn btn-primary mt-2" @click="rain" :disabled="disabled">{{ $t('general.chat_commands.modal.rain.send') }}</button>
                                </div>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.rainModal {
        max-width: 250px;
        padding-top: 20px !important;

        .cc_label {
            margin-top: 10px;
            margin-bottom: 5px;
        }
    }
</style>
