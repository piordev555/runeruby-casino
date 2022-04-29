<template>
    <span>{{ computedValue }}</span>
</template>

<script>
    import bitcoin from 'bitcoin-units';
    import { mapGetters } from 'vuex';

    export default {
        computed: {
            computedValue() {
                switch (this.unit) {
                    default: return this.value.toFixed(2);
                    case "k": {
                        return Math.abs(this.value) > 999 ? Math.sign(this.value)*((Math.abs(this.value)/1000).toFixed(1)) + ' K' : this.value.toFixed(2);
                    }
                    case "m": {
                        return Math.abs(this.value) > 999999 ? Math.sign(this.value)*((Math.abs(this.value)/1000000).toFixed(1)) + ' M' : this.value.toFixed(2);
                    }
                    case "b": {
                        return Math.abs(this.value) > 9999999 * 100 + 99 ? Math.sign(this.value)*((Math.abs(this.value)/1000000000).toFixed(1)) + ' B' : this.value.toFixed(2);
                    }
                }
            },
            ...mapGetters(['unit'])
        },
        props: ['value', 'to']
    }
</script>
