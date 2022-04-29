<template>
    <div class="container-fluid">
        <div class="row page-title align-items-center">
            <div class="col-sm-4 col-xl-6">
                <h4 class="mb-1 mt-0">Stats</h4>
            </div>
        </div>

        <div class="dashboard">
            <div class="spinner-border d-flex ml-auto mr-auto" v-if="!deposits"></div>
            <div v-else v-html="deposits"></div>
        </div>
        <div class="dashboard_analytics">
            <div class="spinner-border d-flex ml-auto mr-auto mt-3" v-if="!analytics"></div>
            <div v-else v-html="analytics"></div>
        </div>
        <div class="dashboard_games">
            <div class="spinner-border d-flex ml-auto mr-auto mt-3" v-if="!gamesAnalytics"></div>
            <div v-else v-html="gamesAnalytics"></div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                deposits: null,
                analytics: null,
                gamesAnalytics: null
            }
        },
        methods: {
            loadScripts() {
                if(this.deposits && this.analytics && this.gamesAnalytics) {
                    setTimeout(() => {
                        _.forEach($('script'), (e) => {
                            $('body').append($('<script>').html($(e).html()));
                            window.dispatchEvent(new Event('resize'));
                        });
                    }, 100);
                }
            }
        },
        watch: {
            deposits() {
                this.loadScripts();
            },
            analytics() {
                this.loadScripts();
            },
            gamesAnalytics() {
                this.loadScripts();
            }
        },
        created() {
            axios.post('/admin/stats/deposits').then(({ data }) => this.deposits = data.dashboard).catch(() => this.$toast.error('Failed to load income data'));
            axios.post('/admin/stats/analytics').then(({ data }) => this.analytics = data.analytics).catch(() => this.$toast.error('Failed to load analytics data'));
            axios.post('/admin/stats/games').then(({ data }) => this.gamesAnalytics = data.games).catch(() => this.$toast.error('Failed to load game data'));
        }
    }
</script>
