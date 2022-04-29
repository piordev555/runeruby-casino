<template>
    <div :class="'game-sidebar-label mt-1' + ' ' + (data.class ? data.class : '')">{{ data.label }}</div>
</template>

<script>
    export default {
        props: {
            data: {
                type: Object
            }
        }
    }
</script>
