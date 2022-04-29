<script>
    import Bus from '../../bus';
    import AuthModal from "./AuthModal";

    export default {
        methods: {
            open(user, token) {
                Bus.$emit('modal:new', {
                    name: 'resetPassword',
                    title: 'general.auth.forgot_password',
                    dismissible: false,
                    closeOnBackdrop: false,
                    component: {
                        data() {
                            return {
                                userId: user,
                                token: token,
                                isValid: null,
                                password: '',
                                confirmPassword: '',
                                sending: false
                            }
                        },
                        methods: {
                            send() {
                                if(this.sending) return;
                                this.sending = true;
                                if(this.password !== this.confirmPassword) {
                                    this.$toast.error(this.$i18n.t('general.auth.passwords_invalid_match'));
                                    this.sending = false;
                                    return;
                                }
                                axios.post('/auth/resetPassword', {
                                    type: 'reset',
                                    user: this.userId,
                                    token: this.token,
                                    password: this.password
                                }).then(() => {
                                    this.$toast.success(this.$i18n.t('general.profile.password_changed'));
                                    Bus.$emit('modal:close');
                                    AuthModal.methods.open('auth');
                                }).catch(() => this.sending = false);
                            }
                        },
                        mounted() {
                            axios.post('/auth/resetPassword', {
                                type: 'validateToken',
                                user: this.userId,
                                token: this.token
                            }).then(() => this.isValid = true).catch(() => {
                                Bus.$emit('modal:close');
                                alert('This password reset request isn\'t valid, request a new one.');
                            });
                        },
                        template: `
                                    <div>
                                        <loader v-if="isValid === null"></loader>
                                        <div v-else>
                                            <div class="mt-3">{{ $t('general.profile.password_new') }}</div>
                                            <input class="mt-1" type="password" v-model="password">
                                            <div class="mt-2">{{ $t('general.profile.password_repeat') }}</div>
                                            <input class="mt-1" type="password" v-model="confirmPassword">
                                            <button :disabled="sending || password.length < 8" @click="send" class="btn btn-primary btn-block mt-3">{{ $t('general.auth.reset_password') }}</button>
                                        </div>
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

    .xmodal.resetPassword {
        max-width: 400px;
    }
</style>
