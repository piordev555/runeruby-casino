<template>
    <div>
        <div class="container-fluid">
            <div class="row page-title">
                <div class="col-md-12">
                    <div class="float-right">
                        <router-link tag="button" to="/admin/wallet_ignored" class="btn btn-danger">Ignored</router-link>
                    </div>
                    <h4 class="mb-1 mt-1">Withdraws & Payments</h4>
                </div>
            </div>
        </div>
        <div class="row" v-if="withdraws">
            <div class="col-12 col-md-8">
                <div class="card">
                    <div class="card-body p-3">
                        <template v-if="withdraws.withdraws.length === 0">
                            <div class="text-center">Nothing here</div>
                        </template>
                        <div class="row" v-else>
                            <div v-for="withdraw in withdraws.withdraws" :class="`col-sm-6 col-md-6 col-lg-4 ${withdraw.user.vipLevel === 5 ? 'order-1' : 'order-2'}`">
                                <div class="card" :style="withdraw.user.vipLevel === 5 ? 'border: 1px solid #00fffb' : ''">
                                    <div class="card-body p-3">
                                        <div class="media">
                                            <img :src="withdraw.user.avatar" class="mr-3 avatar-lg rounded">
                                            <div class="media-body">
                                                <button class="btn btn-primary btn-sm" @click="ignoreWithdraw(withdraw.withdraw._id)" style="position: absolute; right: 15px;">-</button>
                                                <h5 class="mt-1 mb-0">{{ withdraw.user.name }}</h5>
                                                <h6 class="font-weight-normal mt-1 mb-1">
                                                    <router-link :to="`/admin/user/${withdraw.user._id}`">View profile</router-link>
                                                </h6>
                                                <p class="text-muted">
                                                    <strong>Balance:</strong>
                                                    <br>
                                                    {{ withdraw.user.balance.toFixed(withdraw.withdraw.currency.startsWith('local_') ? 2 : 8) }}
                                                    {{ currencies[withdraw.withdraw.currency].name }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row mt-2 border-top pt-2">
                                            <div class="col-12">
                                                <div class="media">
                                                    <div class="media-body">
                                                        <h5 class="mt-2 pt-1 mb-0 font-size-16">Withdraw</h5>
                                                        <h6 class="font-weight-normal mt-0">
                                                            {{ withdraw.withdraw.sum }}
                                                            {{ currencies[withdraw.withdraw.currency].name }}
                                                            {{ new Date(withdraw.withdraw.created_at).toLocaleString() }}
                                                        </h6>
                                                        <h6 class="font-weight-normal mt-1" v-if="withdraw.withdraw.type">{{ withdraw.withdraw.type }}</h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="media">
                                                    <div class="media-body">
                                                        <h5 class="mt-2 pt-1 mb-0 font-size-16">Address</h5>
                                                        <h6 class="font-weight-normal mt-0">
                                                            <strong>{{ currencies[withdraw.withdraw.currency].name }}</strong> {{ withdraw.withdraw.address }}
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <multiaccounts :id="withdraw.user._id"></multiaccounts>
                                            </div>
                                        </div>
                                        <div class="row mt-3 text-center">
                                            <div class="col">
                                                <button @click="acceptWithdraw(withdraw.withdraw._id)" type="button" class="btn btn-primary btn-sm btn-block mr-1">Mark as accepted</button>
                                            </div>
                                            <div class="col">
                                                <button @click="declineWithdraw(withdraw.withdraw._id)" type="button" class="btn btn-white btn-sm btn-block">Decline</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card">
                    <div class="card-body p-3">
                        <template v-if="withdraws.invoices.length === 0">
                            <div class="text-center">Nothing here</div>
                        </template>
                        <div v-else>
                            <div class="media border-top pt-3" v-for="invoice in withdraws.invoices" v-if="invoice.invoice.status !== 0">
                                <img :src="invoice.user.avatar" class="avatar rounded mr-3">
                                <div class="media-body">
                                    <h6 class="mt-1 mb-0 font-size-15">{{ invoice.user.name }}</h6>
                                    <h6 class="text-muted font-weight-normal mt-2 mb-3">{{ invoice.invoice.sum.$numberDecimal ? parseFloat(invoice.invoice.sum.$numberDecimal).toFixed(invoice.invoice.currency.startsWith('local_') ? 2 : 8) : invoice.invoice.sum.toFixed(2) }} {{ currencies[invoice.invoice.currency].name }}<br><span class="font-weight-light">{{ new Date(invoice.invoice.created_at).toLocaleString() }}</span></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                withdraws: null
            }
        },
        computed: {
            ...mapGetters(['currencies'])
        },
        created() {
            axios.post('/admin/wallet/info').then(({ data }) => this.withdraws = data);
        },
        methods: {
            ignoreWithdraw(id) {
                axios.post('/admin/wallet/ignore', { id: id }).then(() => this.$router.go());
            },
            acceptWithdraw(id) {
                axios.post('/admin/wallet/accept', { id: id }).then(() => this.$router.go());
            },
            declineWithdraw(id) {
                axios.post('/admin/wallet/decline', { id: id, reason: prompt('Decline reason') }).then(() => this.$router.go());
            }
        }
    }
</script>
