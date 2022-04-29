<template>
    <div :class="['game-sidebar-buttons-container'].concat(data.classList ? data.classList : [])">
        <div v-for="(button, i) in data.buttons" :key="i" :class="'game-sidebar-buttons-container-button ' + (button.preventMark ? '' : ((button.first === undefined && i === 0 && active === -1) || active === i ? 'active' : (button.first ? 'active' : ''))) + ' ' + (button.class ? button.class : '')"
                @click="button.preventMark ? false : (gameInstance.playTimeout || gameInstance.game.extendedState === 'in-progress' ? false : active = i); (gameInstance.playTimeout || gameInstance.game.extendedState === 'in-progress') && !button.isAction ? false : onClick(button, $event)">
            <span v-if="!button.hideLabel" v-html="button.label"></span>
            <input v-if="button.isInput" :disabled="gameInstance.playTimeout || gameInstance.game.extendedState === 'in-progress'" type="number" :class="inputError ? 'error' : ''" :placeholder="$t('general.mines')" v-model="inputValue[i]" @input="onInput(button, i)">
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        props: {
            data: {
                type: Object,
                default: {}
            }
        },
        computed: {
            ...mapGetters(['gameInstance'])
        },
        data() {
            return {
                active: -1,
                inputValue: _.fill(new Array(this.data.buttons.length), ''),
                inputError: false
            }
        },
        methods: {
            onInput(button, i) {
                const val = parseInt(this.inputValue[i]);
                if(isNaN(val) || val < button.input.min || val > button.input.max) this.inputError = true;
                else {
                    this.inputError = false;
                    button.callback(val);
                }
            },
            onClick(button, target) {
                if($(target.target).is('span')) target = target.path[1];
                else target = target.target;

                if($(target).hasClass('disabled')) return;

                if(button.type === 'input') {
                    if(button.isInput) {
                        const val = parseInt(this.inputValue);
                        if(!(isNaN(val) || val < button.input.min || val > button.input.max)) button.callback(val);
                    }

                    button.hideLabel = true;
                    button.isInput = true;
                } else button.callback();
            }
        }
    }
</script>
