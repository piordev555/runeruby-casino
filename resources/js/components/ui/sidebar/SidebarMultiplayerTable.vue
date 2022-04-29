<template>
    <div class="sidebarMultiplayerBets">
        <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">

        </overlay-scrollbars>
    </div>
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

    export default {
        mounted() {
            Bus.$on('sidebar:multiplayer:add', ({ user, game, additional = null }) => {
                $('.sidebarMultiplayerBets .os-content').append(`
                    <div class="sidebarMultiplayerBet">
                        <div class="user">
                            ${user.vipLevel > 0 ? this.vipIcon(user.vipLevel) : ''}
                            <a href="/profile/${user._id}" target="_blank">${user.name}</a>
                        </div>
                        <div class="bet">
                            ${this.rawBitcoin(game.currency, game.wager)} ${this.currencies[game.currency].name}
                        </div>
                        ${additional ? `<div class="additional">
                            ${additional}
                        </div>` : ''}
                    </div>
                `);
            }, true);
            Bus.$on('sidebar:multiplayer:clear', () => $('.sidebarMultiplayerBets .os-content').html(''), true);
        },
        methods: {
            vipIcon(level) {
                switch (level) {
                    case 1: return `<svg><use href="#vip-ruby"></use></svg>`;
                    case 2: return `<svg><use href="#vip-emerald"></use></svg>`;
                    case 3: return `<svg><use href="#vip-sapphire"></use></svg>`;
                    case 4: return `<svg><use href="#vip-diamond"></use></svg>`;
                    case 5: return `<svg><use href="#vip-gold"></use></svg>`;
                    default: return ``;
                }
            }
        },
        computed: {
            ...mapGetters(['currencies'])
        }
    }
</script>
