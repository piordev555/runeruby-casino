<script>
    import Bus from '../../bus';

    export default {
        methods: {
            open(users) {
                Bus.$emit('modal:new', {
                    name: 'onlineUsers',
                    component: {
                        data() {
                            return {
                                users: users
                            }
                        },
                        template: `
                            <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                                <div>Total: {{ users.length }} user(s)</div>
                                <div class="mt-2 mb-2" v-for="(user, i) in users">
                                    {{ i + 1 }}. {{ user }}
                                </div>
                            </overlay-scrollbars>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.onlineUsers {
        max-width: 350px;

        .modal_content {
            padding-top: 20px;
        }
    }
</style>
