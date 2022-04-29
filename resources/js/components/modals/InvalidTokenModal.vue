<script>
    import Bus from '../../bus';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'invalid_token',
                    dismissible: false,
                    closeOnBackdrop: false,
                    component: {
                        template: `
                            <div>
                                <loader></loader>
                            </div>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    .xmodal.invalid_token {
        width: 225px;
        border-radius: 50%;
        height: 225px;
        display: flex;
        align-items: center;
        justify-content: center;

        .loaderContainer {
            margin-top: 0 !important;
        }
    }
</style>
