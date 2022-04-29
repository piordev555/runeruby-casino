<template>
    <div>
        <div id="wrapper">
            <div class="navbar navbar-expand flex-column flex-md-row navbar-custom">
                <div class="container-fluid">
                    <router-link to="/admin" class="navbar-brand mr-0 mr-md-2 logo" style="font-size: 1.8em; cursor: pointer">
                        runeruby.com
                    </router-link>
                    <ul class="navbar-nav bd-navbar-nav flex-row list-unstyled menu-left mb-0">
                        <li class="">
                            <button class="button-menu-mobile open-left disable-btn">
                                <feather class="menu-icon" type="menu"></feather>
                                <feather class="close-icon" type="x"></feather>
                            </button>
                        </li>
                    </ul>
                    <ul class="navbar-nav flex-row ml-auto d-flex list-unstyled topnav-menu float-right mb-0" @click="toggleRightBar">
                        <li class="dropdown notification-list">
                            <a href="javascript:void(0);" class="nav-link">
                                <feather type="settings"></feather>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="left-side-menu">
                <div class="sidebar-content">
                    <div id="sidebar-menu" class="slimscroll-menu">
                        <ul class="metismenu" id="menu-bar">
                            <li class="menu-title">Website</li>
                            <li>
                                <router-link to="/">
                                    <feather type="chevron-left"></feather>
                                    <span> Back</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin">
                                    <feather type="activity"></feather>
                                    <span> Dashboard</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/modules">
                                    <feather type="git-merge"></feather>
                                    <span> Modules</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/wallet">
                                    <feather type="clock"></feather>
                                    <span class="badge badge-primary float-right" v-if="info">
                                        {{ info.withdraws }}
                                    </span>
                                    <span> Withdraws</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/promo">
                                    <feather type="percent"></feather>
                                    <span> Promocodes</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/users">
                                    <feather type="users"></feather>
                                    <span> Users</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/notifications">
                                    <feather type="bell"></feather>
                                    <span> Notifications</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/currency">
                                    <feather type="disc"></feather>
                                    <span> Coins</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/activity">
                                    <feather type="alert-triangle"></feather>
                                    <span> Activity</span>
                                </router-link>
                            </li>
                            <li>
                                <router-link to="/admin/bot">
                                    <feather type="shuffle"></feather>
                                    <span> Bot</span>
                                </router-link>
                            </li>
                            <li class="menu-title">Server</li>
                            <li>
                                <router-link to="/admin/settings">
                                    <feather type="settings"></feather>
                                    <span> Settings</span>
                                </router-link>
                            </li>
                            <li>
                                <a href="javascript:void(0)" onclick="window.open('/admin/logs', '_blank');">
                                    <feather type="align-left"></feather>
                                    <span class="badge badge-danger float-right" style="background: #fd0c31" v-if="info">
                                        {{ info.logs.critical }}
                                    </span>
                                    <span class="badge badge-danger float-right" style="position: relative; right: 5px" v-if="info">
                                        {{ info.logs.error }}
                                    </span>
                                    <span> Logs</span>
                                </a>
                            </li>
                            <li>
                                <a href="javascript:void(0)">
                                    <feather type="hash"></feather>
                                    <span class="badge badge-primary float-right" v-if="info">
                                        {{ info.version }}
                                    </span>
                                    <span> Version</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="content-page">
                <div class="content">
                    <router-view :key="$route.fullPath" class="container-fluid pageContent"></router-view>
                </div>
                <footer class="footer">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                {{ new Date().getFullYear() }} &copy; runeruby.com
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
        <div class="right-bar">
            <div class="rightbar-title">
                <h5 class="m-0">Games</h5>
            </div>
            <div class="slimscroll-menu">
                <div class="p-3">
                    <div v-for="game in games" :key="game.id" v-if="!game.isPlaceholder" class="custom-control custom-checkbox mb-2 clickable" @click="toggleGame(game.id)">
                        <input :checked="!game.isDisabled" type="checkbox" class="custom-control-input">
                        <label class="custom-control-label clickable">{{ game.name }}</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="rightbar-overlay" @click="toggleRightBar"></div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                info: null
            }
        },
        computed: {
            ...mapGetters(['games'])
        },
        mounted() {
            axios.post('/admin/info').then(({ data }) => this.info = data);
        },
        methods: {
            toggleGame(id) {
                axios.post('/admin/toggle', { name: id });
                this.$store.dispatch('updateData');
            },
            toggleRightBar() {
                const active = $('.rightbar-overlay').hasClass('active');
                $('.rightbar-overlay').toggle().toggleClass('active');
                $('.right-bar').css({'right': active ? '-270px' : 0});
            }
        }
    }
</script>
