<script>
    import Bus from '../../bus';

    window._2faCallbacks = [];

    export default {
        methods: {
            open(error, resolve, reject) {
                window._2faCallbacks.push({
                    error: error,
                    resolve: resolve,
                    reject: reject
                });

                if($('.xmodal.tfa').length === 0) Bus.$emit('modal:new', {
                    name: 'tfa',
                    dismissible: false,
                    closeOnBackdrop: false,
                    component: {
                        data() {
                            return {
                                value: '',
                                disabled: false
                            }
                        },
                        methods: {
                            nextInput(value) {
                                if(value.length > 1) {
                                    $(`.xmodal.tfa .inputs input:nth-child(${this.value.length + 1})`).focus();
                                    this.update();
                                    return;
                                }

                                if(value.length > 0) {
                                    this.value += value;
                                    $(`.xmodal.tfa .inputs input:nth-child(${this.value.length + 1})`).focus();
                                } else {
                                    this.value = this.value.substring(0, this.value.length - 1);
                                    $(`.xmodal.tfa .inputs input:nth-child(${this.value.length})`).focus();
                                }

                                this.update();

                                if(this.value.length === 6) {
                                    this.disabled = true;
                                    axios.post('/api/user/2fa_validate', { code: this.value }).then(() => {
                                        Bus.$emit('modal:close');

                                        _.forEach(window._2faCallbacks, (error) => axios.request(error.error.config).then(error.resolve).catch(error.reject));

                                        window._2faCallbacks = [];
                                    }).catch(() => {
                                        this.value = '';
                                        this.update();
                                        $(`.xmodal.tfa .inputs input:nth-child(1)`).focus();
                                        this.$toast.error(this.$i18n.t('general.profile.error_2fa'));
                                        this.disabled = false;
                                    })
                                }
                            },
                            update() {
                                for(let i = 0; i < 6; i++) $(`.xmodal.tfa .inputs input:nth-child(${i + 1})`).val(this.value[i] ? this.value[i] : '');
                            },
                            clickInput(n) {
                                if(!this.value[n]) $(`.xmodal.tfa .inputs input:nth-child(${this.value.length + 1})`).focus();
                            }
                        },
                        template: `
                            <div>
                                <div class="tfah">2FA</div>
                                <div class="tfad">{{ $t('general.profile.2fa_description') }}</div>

                                <div class="inputs">
                                    <input maxlength="2" @input="nextInput($event.target.value)" @click="clickInput(0)" :disabled="disabled">
                                    <input maxlength="2" @input="nextInput($event.target.value)" @click="clickInput(1)" :disabled="disabled">
                                    <input maxlength="2" @input="nextInput($event.target.value)" @click="clickInput(2)" :disabled="disabled">
                                    <input maxlength="2" @input="nextInput($event.target.value)" @click="clickInput(3)" :disabled="disabled">
                                    <input maxlength="2" @input="nextInput($event.target.value)" @click="clickInput(4)" :disabled="disabled">
                                    <input maxlength="1" @input="nextInput($event.target.value)" @click="clickInput(5)" :disabled="disabled">
                                </div>

                                <div class="tfaStatus">{{ disabled ? '...' : $t('general.profile.2fa_digits', { 'digits': 6 - value.length  }) }}</div>
                            </div>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.tfa {
        position: relative;
        z-index: 41000 !important;
        max-width: 380px;

        .inputs {
            display: flex;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            width: fit-content;

            input {
                width: 50px;
                height: 60px;
                line-height: 100%;
                border: 0;
                outline: 0;
                font-size: 25px;
                word-spacing: 0;
                overflow: hidden;
                text-align: center;

                @include themed() {
                    background: lighten(t('input'), 2%);
                }
            }
        }

        .tfaStatus {
            border-radius: 3px;
            width: 100%;
            padding: 20px;
            text-align: center;
            font-weight: 600;
            user-select: none;
            margin-top: 20px;
            @include themed() {
                border: 1px solid darken(t('input'), 2%);
                background: lighten(t('input'), 2%);
            }
        }

        .tfah {
            text-align: center;
            font-weight: 600;
            font-size: 2em;
            margin-top: 20px;
        }

        .tfad {
            text-align: center;
            margin-bottom: 20px;
        }

    }
</style>
