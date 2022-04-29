<template>
    <div>
        <input v-model="code">
        <button :disabled="disabled" class="btn btn-primary btn-block mt-2" @click="activate">{{ $t('bonus.promo.activate') }}</button>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                code: '',
                disabled: false
            }
        },
        methods: {
            activate() {
                this.disabled = true;

                axios.post('/api/promocode/activate', { code: this.code }).then(() => {
                    this.disabled = false;

                    this.$toast.success(this.$i18n.t('bonus.promo.success'));
                }).catch((code) => {
                    this.disabled = false;

                    if(code.response.data.code === 1) this.$toast.error(this.$i18n.t('bonus.promo.invalid'));
                    if(code.response.data.code === 2) this.$toast.error(this.$i18n.t('bonus.promo.expired_time'));
                    if(code.response.data.code === 3) this.$toast.error(this.$i18n.t('bonus.promo.expired_usages'));
                    if(code.response.data.code === 4) this.$toast.error(this.$i18n.t('bonus.promo.used'));
                    if(code.response.data.code === 5) this.$toast.error(this.$i18n.t('general.error.promo_limit'));
                    if(code.response.data.code === 7) this.$toast.error(this.$i18n.t('general.error.vip_only_promocode'));
                });
            }
        }
    }
</script>
