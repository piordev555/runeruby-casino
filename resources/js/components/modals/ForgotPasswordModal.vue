<script>
    import Bus from '../../bus';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'forgotPassword',
                    title: 'general.auth.forgot_password',
                    component: {
                        data() {
                            return {
                                email: null,
                                sent: false,
                                sending: false
                            }
                        },
                        methods: {
                            send() {
                                if(this.sending) return;
                                this.sending = true;
                                axios.post('/auth/resetPassword', { login: this.email }).then(() => {
                                    this.sent = true;
                                    this.sending = false;
                                }).catch(() => {
                                    this.sent = true;
                                    this.sending = false;
                                });
                            }
                        },
                        template: `
                                <div>
                                    <div v-if="!sent">
                                        <div class="mt-3">{{ $t('general.auth.email_or_login') }}</div>
                                        <input class="mt-1" type="text" v-model="email">
                                        <button :disabled="sending" @click="send" class="btn btn-primary btn-block mt-3">{{ $t('general.auth.reset_password') }}</button>
                                    </div>
                                    <div v-else class="text-center mt-3 mb-2">{{ $t('general.auth.email_was_sent') }}</div>
                                </div>
                            `
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";
    .xmodal.forgotPassword {
        max-width: 400px;
    }
</style>
