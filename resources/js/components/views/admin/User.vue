<template>
    <div class="container-fluid" v-if="info">
        <div class="row page-title">
            <div class="col-md-12">
                <h4 class="mb-1 mt-0">{{ info.user.name }}</h4>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center mt-3">
                            <img :src="info.user.avatar" alt="" class="avatar-lg rounded-circle">
                            <h5 class="mt-2 mb-0">{{ info.user.name }}</h5>
                            <template v-if="info.user.name_history.length > 1">
                                <h6 class="font-weight-normal mt-2 mb-0">Also known as:</h6>
                                <h6 class="text-muted font-weight-normal" v-for="history in info.user.name_history">
                                    {{ new Date(history['time']).toLocaleString() }} - {{ history['name'] }}
                                </h6>
                            </template>

                            <button type="button" :class="`btn ${ info.user.ban ? 'btn-primary' : 'btn-danger' } btn-sm mr-1 mt-1`" @click="ban(info.user._id)">
                                {{ info.user.ban ? 'Unban' : 'Ban' }}
                            </button>
                        </div>

                        <div class="mt-3 pt-2 border-top">
                            <h4 class="mb-3 font-size-15">Info</h4>
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0 text-muted">
                                    <tbody>
                                        <tr>
                                            <th scope="row">Games</th>
                                            <td>{{ info.games }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Register IP</th>
                                            <td>{{ info.user.register_ip }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Latest login IP</th>
                                            <td>{{ info.user.login_ip }}</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">Created at</th>
                                            <th>{{ new Date(info.user.created_at).toLocaleString() }}</th>
                                        </tr>
                                        <tr>
                                            <th scope="row">Last activity</th>
                                            <th>{{ new Date(info.user.latest_activity).toLocaleString() }}</th>
                                        </tr>
                                        <tr>
                                            <th scope="row">Referrer</th>
                                            <th v-html="!info.user.referral ? '-' : '<a href=\'/admin/user/'+info.user.referral+'\'></a>'">View profile</th>
                                        </tr>
                                        <tr>
                                            <th scope="row">2FA status</th>
                                            <th>{{ info.user.tfa_enabled ? 'Enabled' : 'Disabled' }}</th>
                                        </tr>
                                        <tr>
                                            <th scope="row">Access Level</th>
                                            <th>
                                                <select id="access" v-model="userAccess" @change="changeAccess">
                                                    <option value="user">User</option>
                                                    <option value="moderator">Moderator</option>
                                                    <option value="admin">Administrator</option>
                                                </select>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th scope="row">Accounts</th>
                                            <th>
                                                <multiaccounts :id="info.user._id"></multiaccounts>
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-body">
                        <table class="table dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Currency</th>
                                    <th>Games</th>
                                    <th>Wins</th>
                                    <th>Losses</th>
                                    <th>Wagered</th>
                                    <th>Deposited</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Total</td>
                                    <td>{{ info.games }}</td>
                                    <td>{{ info.wins }}</td>
                                    <td>{{ info.losses }}</td>
                                </tr>
                                <tr v-for="currency in currencies" v-if="currency.name">
                                    <td>{{ currency.name }}</td>
                                    <td>{{ info.currencies[currency.id].games }}</td>
                                    <td>{{ info.currencies[currency.id].wins }}</td>
                                    <td>{{ info.currencies[currency.id].losses }}</td>
                                    <td>{{ info.currencies[currency.id].wagered.toFixed(currency.id.startsWith('local_') ? 2 : 8) }} {{ currency.name }}</td>
                                    <td>{{ info.currencies[currency.id].deposited.toFixed(currency.id.startsWith('local_') ? 2 : 8) }} {{ currency.name }}</td>
                                    <td><input class="form-control form-control-sm" :placeholder="currency.name" :value="info.currencies[currency.id].balance.toFixed(currency.id.startsWith('local_') ? 2 : 8)" @input="changeBalance(currency.walletId, $event.target.value)"></td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                        <table id="datatable" class="table dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Game</th>
                                    <th>Date</th>
                                    <th>Wager</th>
                                    <th>Income</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                    <th>Link</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="game in info.gamesArray">
                                    <td>{{ game.game }}</td>
                                    <td>{{ new Date(game.created_at).toLocaleString() }}</td>
                                    <td>{{ game.wager.toFixed(game.currency.startsWith('local_') ? 2 : 8) }}</td>
                                    <td>{{ game.profit.toFixed(game.currency.startsWith('local_') ? 2 : 8) }}</td>
                                    <td>{{ game.status }}</td>
                                    <td style="white-space: normal;">{{ JSON.stringify(game.data) }}</td>
                                    <td>
                                        <a v-if="game.status !== 'in-progress' && game.status !== 'cancelled'" href="javascript:void(0)" @click="viewGame(game._id, game.game)">View</a>
                                        <span v-else>-</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <hr>
                        <table id="transactions" class="table dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th style="width: 80%">Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="transaction in info.transactions">
                                    <td>{{ new Date(transaction.created_at).toLocaleString() }}</td>
                                    <td style="width: 80%">
                                        <div>Message: {{ transaction.data.message ? transaction.data.message : '-' }}</div>
                                        <div>Game: {{ transaction.data.game ? transaction.data.game : '-' }}</div>
                                        <div>
                                            Amount: {{ transaction.amount.toFixed(transaction.currency.startsWith('local_') ? 2 : 8) }} {{ currencies[transaction.currency].name }}
                                            (Before: {{ transaction.old.toFixed(transaction.currency.startsWith('local_') ? 2 : 8) }}, Now: {{ transaction.new.toFixed(transaction.currency.startsWith('local_') ? 2 : 8) }})
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import OverviewModal from "../../modals/OverviewModal";

    export default {
        data() {
            return {
                info: null,
                userAccess: null
            }
        },
        computed: {
            ...mapGetters(['currencies'])
        },
        methods: {
            changeBalance(id, balance) {
                axios.post('/admin/balance', {
                    id: this.info.user._id,
                    balance: balance,
                    currency: id
                });
            },
            viewGame(id, game) {
                this.$router.push('/', () => {
                    setTimeout(() => {
                        OverviewModal.methods.open(id, game);
                    }, 100);
                });
            },
            changeAccess() {
                axios.post('/admin/role', { id: this.info.user._id, role: this.userAccess });
            },
            ban(id) {
                axios.post('/admin/ban', { id: id }).then(() => this.$router.go());
            }
        },
        created() {
            axios.post('/admin/user', { id: this.$route.params.id }).then(({ data }) => {
                this.info = data;

                this.userAccess = this.info.user.access;

                setTimeout(() => {
                    $('#datatable').DataTable({
                        destroy: true,
                        "order": [[1, "desc"]],
                        "language": {
                            "paginate": {
                                "previous": "Previous ",
                                "next": " Next"
                            }
                        },
                        "drawCallback": function() {
                            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                        }
                    });

                    $('#transactions').DataTable({
                        destroy: true,
                        "order": [[0, "desc"]],
                        "language": {
                            "paginate": {
                                "previous": "Previous ",
                                "next": " Next"
                            }
                        },
                        "drawCallback": function() {
                            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                        }
                    });
                }, 100);
            });
        }
    }
</script>

<style lang="scss">
    #transactions {
        color: black !important;
    }
</style>
