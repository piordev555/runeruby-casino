<template></template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';
    import OverlayScrollbars from 'overlayscrollbars';

    export default {
        mounted() {
            if(!document.querySelector('.game-history')) {
                const history = document.createElement('div');
                history.className = 'game-history';
                document.querySelector('.game-content').appendChild(history);
            }

            if(this.data.scrollable) {
                const e = document.querySelector('.game-history');
                e.className = 'game-history os-host-flexbox';
                OverlayScrollbars(e, {
                    scrollbars: { autoHide: 'leave' },
                    className: 'os-theme-thin-light'
                });
            }

            Bus.$on(this.gameInstance.game.id + ':history:addEntry', ({ html, type = 'prepend' }) => {
                const e = document.createElement('div'), s = `.game-history ${this.data.scrollable ? '.os-content' : ''}`;
                e.innerHTML = html;
                e.className = 'history history-' + this.gameInstance.game.id;
                type === 'prepend' ? document.querySelector(s).prepend(e) : document.querySelector(s).appendChild(e);
            }, true);
        },
        computed: {
            ...mapGetters(['gameInstance'])
        },
        props: {
            data: {
                type: Object,
                default() {
                    return {};
                }
            }
        }
    }
</script>
