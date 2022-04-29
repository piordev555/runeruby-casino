<template>
    <div class="container-fluid">
        <div :class="`game-container game-${$route.params.id}`">
            <div class="row">
                <div class="col">
                    <game-sidebar></game-sidebar>
                </div>
                <div class="col">
                    <div :class="`game-content game-content-${$route.params.id} game-type-local`">
                        <component ref="gameComponent" :is="$route.params.id"></component>
                    </div>
                </div>
            </div>
        </div>
        <div class="game-footer"></div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                game: null
            }
        },
        computed: {
            ...mapGetters(['games', 'gameInstance'])
        },
        created() {
            _.forEach(this.games, (e) => {
                if(e.id === this.$route.params.id) this.game = e;
            });

            if(!this.game) this.$router.push('/');
        },
        mounted() {
            this.createGameInstance(this.$route.params.id, this.$refs.gameComponent);
            window.$gameRef = this.$refs.gameComponent;

            this.updateGameInstance((i) => {
                i.bettingType = 'manual';
                i.playTimeout = false;
            });
        }
    }
</script>

<style lang="scss">
    .game-type-local {
        padding: 15px;
    }
</style>
