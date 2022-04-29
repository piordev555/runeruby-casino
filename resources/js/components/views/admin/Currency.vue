<template>
    <div>
        <div class="row page-title">
            <div class="col-md-12">
                <h4 class="mb-1 mt-1">Coins</h4>
            </div>
        </div>
        <div class="container-fluid" v-if="data">
            <div class="row" v-if="availableCurrencies.length === 0">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div>
                                Loading...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-sm-6 col-md-4" v-for="currency in currencies" v-if="currency.balance">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">{{ currency.name }}</h5>
                            <div class="mt-2" v-if="data.options && data.options[currency.id]">
                                <div class="form-group mt-2" v-for="option in data.options[currency.id]">
                                    <label data-toggle="tooltip" data-placement="top" :title="option.id">{{ option.name }}</label>
                                    <input :disabled="option.readOnly" :value="option.value" @input="change(currency.id, option.id, $event.target.value)" type="text" class="form-control">
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
        computed: {
            ...mapGetters(['currencies'])
        },
        created() {
            axios.post('/admin/currencySettings').then(({ data }) => {
                let duplicates = [];
                _.forEach(data.coins, (e) => {
                    if(e.balance && !duplicates.includes(e.walletId)) {
                        duplicates.push(e.walletId);
                        this.availableCurrencies.push(e);
                    }
                });

                this.data = data;
            });

            axios.post('/admin/currencyBalance').then(({ data }) => this.balance = data);
        },
        data() {
            return {
                data: {},
                balance: {},

                address: '',
                amount: 0.00000000,

                availableCurrencies: [],

                ethDepositAddr: ''
            }
        },
        methods: {
            sendEthDeposits() {
                this.$toast.success('Success');
                axios.post('/admin/ethereumNativeSendDeposits', { toAddr: this.ethDepositAddr });
            },
            toggleCurrency(walletId, type) {
                axios.post('/admin/toggleCurrency', {
                    walletId: walletId,
                    type: type
                }).then(() => this.$store.dispatch('updateData'));
            },
            send() {
                axios.post('/admin/wallet/transfer', {
                    'currency': $('#cs_currency').val(),
                    'amount': this.amount,
                    'address': this.address
                }).then(() => {
                    this.$toast.success('Success');
                }).catch(() => {
                    this.$toast.error('Error');
                })
            },
            change(cId, oId, value) {
                axios.post('/admin/currencyOption', { currency: cId, option: oId, value: value });
            },
            autogen() {
                if($('#autogen').hasClass('disabled')) return;
                $('#autogen').attr('disabled', 'disabled').addClass('disabled').html('Generating...');

                const request = new XMLHttpRequest();
                request.responseType = 'blob';

                request.addEventListener('readystatechange', function(e) {
                    if(request.readyState === 4) window.location.reload();
                });

                request.open('get', '/admin/wallet/autoSetup');
                request.send();
            }
        }
    }
</script>
