<template>
    <div class="game-sidebar">
        <div class="game-sidebar-sticky">
            <div class="game-sidebar-tabs">
                <div :class="'game-sidebar-tab ' + (gameInstance.bettingType === 'manual' ? 'active' : '')" @click="setBettingType('manual')">
                    {{ $t('general.bets.manual') }}
                </div>
                <div v-if="gameInstance.game && gameInstance.game.autoBetSettings.supported" :class="'game-sidebar-tab ' + (gameInstance.bettingType === 'auto' ? 'active' : '')" @click="setBettingType('auto')">
                    {{ $t('general.bets.auto') }}
                </div>
            </div>
            <component v-for="(c, i) in components" :key="i" :is="'sidebar-' + c.name" :data="c.data"></component>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                components: []
            }
        },
        methods: {
            setBettingType(type) {
                if(this.gameInstance.game.autoBetSettings.state || this.gameInstance.playTimeout) return;
                this.updateGameInstance((i) => i.bettingType = type);
                $('.play-button').removeClass('disabled');
            }
        },
        mounted() {
            this.components = this.$parent.$refs.gameComponent.getSidebarComponents();
        },
        computed: {
            ...mapGetters(['gameInstance'])
        }
    }
</script>

<style lang="scss">
    @media(max-width: 991px) {
        .game-sidebar {
            .game-sidebar-sticky {
                position: relative;
                padding-bottom: 60px;
            }

            .wager-selector {
                width: calc(100% - 135px);
            }

            .play-button {
                position: absolute;
                top: 33px;
                height: 80px;
                width: 120px;
                right: 0;
                margin-top: 0 !important;
            }

            .game-sidebar-tabs {
                position: absolute !important;
                bottom: 0;
                z-index: 2;
                top: unset !important;
                height: 60px;
                margin-bottom: -15px;
            }
        }

        .game-footer {
            margin-top: 10px;
        }
    }
</style>
