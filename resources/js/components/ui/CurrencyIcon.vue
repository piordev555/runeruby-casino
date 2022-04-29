<template>
    <i :class="computedCurrency.icon" :style="{ color: computedCurrency.style }"></i>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        computed: {
            ...mapGetters(['currencies']),
            computedCurrency() {
                return this.currencies[this.currency];
            }
        },
        props: {
            currency: {
                type: String,
                required: true
            }
        }
    }
</script>
