<template>
    <input type="text" @input="onInput($event.target.value)" :value="value" :disabled="gameInstance.game && gameInstance.game.extendedState === 'in-progress'">
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                value: ''
            }
        },
        computed: {
            ...mapGetters(['gameInstance'])
        },
        mounted() {
            if(this.data.value) this.value = this.data.value;
        },
        methods: {
            onInput(value) {
                const oldValue = this.value;
                if(this.data.callback) {
                    if(this.data.callback(value)) this.value = value;
                    else this.value = oldValue;
                }
                else this.value = value;
            }
        },
        props: ['data']
    }
</script>
