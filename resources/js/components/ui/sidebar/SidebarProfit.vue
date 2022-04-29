<template>
    <input type="text" readonly :value="value">
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                value: 0
            }
        },
        computed: {
            ...mapGetters(['currency'])
        },
        created() {
            this.value = this.currency.startsWith('local_') ? 2 : 8;

            Bus.$on('profit:update', (e) => this.value = e.toFixed(this.currency.startsWith('local_') ? 2 : 8));
        }
    }
</script>
