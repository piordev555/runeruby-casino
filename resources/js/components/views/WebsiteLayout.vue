<template>
    <div class="d-flex" v-if="!$route.fullPath.startsWith('/admin')">
        <modal></modal>
        <website-notifications></website-notifications>
        <div class="wrapper">
            <layout-sidebar></layout-sidebar>
            <div class="pageWrapper">
                <layout-header></layout-header>
                <global-notifications></global-notifications>
                <router-view :key="$route.fullPath" class="pageContent"></router-view>
                <live-feed></live-feed>
                <layout-footer></layout-footer>
            </div>
        </div>
        <chat></chat>
        <floating-buttons></floating-buttons>
        <mobile-menu></mobile-menu>
        <profit-monitoring></profit-monitoring>
        <support-chat v-if="!isGuest"></support-chat>

        <symbols></symbols>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        computed: {
            ...mapGetters(['isGuest'])
        }
    }
</script>
