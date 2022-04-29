<template>
    <div class="gameCategory">
        <div class="header">
            {{ $t('general.sidebar.' + $route.params.category) }}
        </div>
        <div class="warning" v-if="Object.keys(categoryGames).length === 0">
            {{ $t('general.sidebar.no_games') }}
        </div>
        <games :categories="categoryGames"></games>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                categoryGames: {}
            }
        },
        computed: {
            ...mapGetters(['games', 'recentGameHistory', 'isGuest'])
        },
        created() {
            if(this.$i18n.t('general.sidebar.' + this.$route.params.category) === 'general.sidebar.' + this.$route.params.category) {
                this.$router.push('/404');
                return;
            }

            let games = this.games;
            let validateUrlCategory = true;

            if(this.$route.params.category === 'favorite') {
                if(this.isGuest) return;
                let ids = this.user.user.favoriteGames ? this.user.user.favoriteGames : [];
                games = [];
                _.forEach(ids, (id) => games.push(this.games.filter((e) => e.id === id)[0]));
                validateUrlCategory = false;
            }
            else if(this.$route.params.category === 'recent') {
                games = [];
                _.forEach(this.recentGameHistory, (id) => games.push(this.games.filter((e) => e.id === id)[0]));
                games = games.reverse();
                validateUrlCategory = false;
            }

            let duplicates = [];
            _.forEach(games, (game) => {
                if (!validateUrlCategory || game.category.includes(this.$route.params.category)) {
                    _.forEach(game.category, (category) => {
                        if (duplicates.includes(game.id)) return;
                        duplicates.push(game.id);

                        if (!this.categoryGames[category]) this.categoryGames[category] = [game];
                        else this.categoryGames[category].push(game);
                    });
                }
            });
        }
    }
</script>

<style lang="scss" scoped>
    @import "resources/sass/variables";

    .warning {
        width: 100%;
        text-align: center;
        font-size: 1.1em;
        margin-top: 15px;
     }

    .gameCategory {
        @include themed() {
            .header {
                background: rgba(t('sidebar'), .8);
                backdrop-filter: blur(20px);
                border-bottom: 2px solid t('border');
                margin-top: -15px;
                padding: 35px 45px;
                font-size: 1.8em;
                position: sticky;
                top: 73px;
                z-index: 555;
            }
        }
    }
</style>
