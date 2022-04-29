<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'vip_bonus',
                    component: {
                        data() {
                            return {
                                vip: ''
                            }
                        },
                        mounted() {
                            switch (this.user.user.vipLevel) {
                                case 0: this.vip = 'none'; break;
                                case 1: this.vip = 'ruby'; break;
                                case 2: this.vip = 'emerald'; break;
                                case 3: this.vip = 'sapphire'; break;
                                case 4: this.vip = 'diamond'; break;
                                case 5: this.vip = 'gold'; break;
                            }

                            this.$store.dispatch('update');
                        },
                        computed: {
                            ...mapGetters(['user', 'currency'])
                        },
                        methods: {
                            collect() {
                                axios.post('/api/promocode/vipBonus').then(() => Bus.$emit('modal:close'));
                            }
                        },
                        template: `
                            <div>
                                <div v-if="user.user.weekly_bonus_obtained">
                                    <div class="unavailable">
                                        <div class="slanting">
                                            <div class="unavailableContent" v-html="$t('vip.bonus.timeout')"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="font-weight-bold mb-2 mt-2">{{ $t('vip.bonus.progress_title') }}</div>
                                <div class="bonus-image">
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" :style="{ width: (user.user.weekly_bonus ?? 0).toFixed(2) + '%' }">{{ user.user.weekly_bonus ?? 0 }}%</div>
                                    </div>
                                    <div :class="'btn btn-primary mt-2' + (user.user.weekly_bonus < 0.1 ? 'disabled' : '')" v-html="$t('general.take', { value: (((user.user.weekly_bonus ?? 0) / 100) * user.user.vipBonus).toFixed(currency.startsWith('local_') ? 2 : 8) })" @click="user.user.weekly_bonus < (currency.startsWith('local_') ? 0.1 : 0.00000001) ? '' : collect()"></div>
                                </div>

                                <div class="font-weight-bold mt-2" style="font-size: 1.05em">{{ $t('vip.bonus.title') }}</div>
                                <div class="vipDesc" v-html="$t('vip.bonus.description', { vip: '<svg style=\\'width: 14px; height: 14px;\\'><use href=\\'#vip-'+this.vip+'\\'></use></svg>' })"></div>
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

    .xmodal.vip_bonus {
        width: 400px;

        .vipDesc {
            @include themed() {
                color: rgba(t('text'), 0.8);
                font-size: 0.9em;
            }
        }

        .bonus-image {
            background-image: url('/img/misc/bonus.svg');
            background-size: cover;
            position: relative;
            height: 120px;

            .btn {
                position: absolute;
                width: 50%;
                right: 10px;
                bottom: 10px;
                font-size: 0.9em;
                text-transform: uppercase;
                font-weight: 600;
            }
        }

        .progress {
            position: absolute;
            bottom: 10px;
            left: 10px;
            width: 40%;
            height: 14px;
            border-radius: 3px;

            @include themed() {
                background: darken(t('sidebar'), 3%);

                .progress-bar {
                    height: 14px;
                    background: t('secondary');
                    font-size: 0.65em;
                }
            }
        }
    }
</style>
