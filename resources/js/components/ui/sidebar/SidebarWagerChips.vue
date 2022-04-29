<template>
    <div class="wager-chip wager-selector os-host-flexbox">
        <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
            <div v-for="(chip, i) in data.chips" :key="i" :class="`chip ${active === i ? 'active' : ''}`" :data-display="chip[1].toFixed(currency.startsWith('local_') ? 2 : 8)" :data-value="currency.startsWith('local_') ? chip[0].toFixed(2) : chip[1].toFixed(8)" @click="click(chip, i)">
                {{ abbreviate(chip[1]) }}
            </div>
        </overlay-scrollbars>
    </div>
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            click(chip, i) {
                if(this.gameInstance.game && this.gameInstance.game.extendedState === 'in-progress') return;
                this.active = i;
                Bus.$emit('sidebar:chipSelect', { value: chip[1], displayValue: chip[1] });
            }
        },
        computed: {
            ...mapGetters(['gameInstance', 'currency'])
        },
        data() {
            return {
                active: 0
            }
        },
        props: {
            data: {
                type: Object,
                default() {
                    return { chips: [[1, 1000], [10, 1000 * 10], [100, 1000 * 100], [1000, 1000 * 1000], [10000, 1000 * 10000], [100000, 1000 * 100000], [1000000, 1000 * 1000000], [10000000, 1000 * 10000000], [100000000, 100000000 * 1000], [1000000000, 1000 * 1000000000], [10000000000, 1000 * 10000000000], [100000000000, 100000000000 * 1000]] }
                }
            }
        }
    }
</script>
