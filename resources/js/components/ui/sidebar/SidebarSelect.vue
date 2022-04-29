<template>
    <select v-model="value">
        <option v-for="option in data.options" :value="option.value" v-html="option.text"></option>
    </select>
</template>

<script>
    export default {
        data() {
            return {
                value: this.data.options[0].value
            }
        },
        mounted() {
            if(this.data.value) this.value = this.data.value;
        },
        watch: {
            value() {
                this.data.onSelect(this.value);
            }
        },
        props: ['data']
    }
</script>

<style lang="scss" scoped>
    @import "resources/sass/variables";

    select {
        width: 100%;
    }
</style>
