<template>
    <div :class="'floatingButtons ' + (chat ? '' : 'chatIsHidden')">
        <div class="floatingButton" @click="toggleChat">
            <svg><use href="#chat"></use></svg>
        </div>
        <div class="floatingButton" @click="toggleSupport" v-if="!isGuest">
            <svg><use href="#support"></use></svg>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';
    import Bus from '../../bus';

    export default {
        computed: {
            ...mapGetters(['isGuest', 'chat'])
        },
        methods: {
            toggleChat() {
                this.$store.dispatch('toggleChat');
            },
            toggleSupport() {
                Bus.$emit('toggleSupportWindow');
            }
        }
    }
</script>
