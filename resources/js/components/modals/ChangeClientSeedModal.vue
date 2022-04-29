<script>
import Bus from '../../bus';
import { mapGetters } from 'vuex';

export default {
    methods: {
        open() {
            Bus.$emit('modal:new', {
                name: 'change_client_seed',
                component: {
                    data() {
                        return {
                            client_seed: null,
                            saving: false
                        }
                    },
                    computed: {
                        ...mapGetters(['user'])
                    },
                    created() {
                        this.client_seed = this.user.user.client_seed;
                    },
                    methods: {
                        save() {
                            this.saving = true;
                            axios.post('/api/user/client_seed_change', {
                                client_seed: this.client_seed
                            }).then(() => {
                                Bus.$emit('modal:close');
                                this.$store.dispatch('update');
                            }).catch(() => {
                                this.saving = false;
                            });
                        },
                        close() {
                            Bus.$emit('modal:close');
                        }
                    },
                    template: `
                        <div>
                            <input class="mt-4 mb-2" type="text" v-model="client_seed" :placeholder="$t('general.profile.client_seed')">
                            <div class="btn-group">
                                <button class="btn btn-primary mr-2" :disabled="saving" @click="save">{{ $t('general.change') }}</button>
                                <button class="btn btn-secondary" @click="close">{{ $t('general.cancel') }}</button>
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

    .xmodal.change_client_seed {
        max-width: 400px;
    }
</style>
