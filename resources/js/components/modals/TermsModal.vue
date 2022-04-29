<script>
    import Bus from '../../bus';

    import faq from '../../../legal/faq.txt';
    import rain from '../../../legal/rain.txt';
    import tpNotice from '../../../legal/tpNotice.txt';
    import agreement from '../../../legal/agreement.txt';
    import privacy_policy from '../../../legal/privacy_policy.txt';

    export default {
        methods: {
            open(type) {
                Bus.$emit('modal:new', {
                    name: 'terms',
                    component: {
                        data() {
                            return {
                                type: type,
                                terms: {
                                    faq: faq,
                                    rain: rain,
                                    tpNotice: tpNotice,
                                    agreement: agreement,
                                    privacy_policy: privacy_policy
                                }
                            }
                        },
                        template: `
                            <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                                <div v-html="terms[type].replaceAll('\\n', '<br>')"></div>
                            </overlay-scrollbars>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.terms {
        max-width: 350px;

        .os-content {
            padding-top: 10px !important;
        }
    }
</style>
