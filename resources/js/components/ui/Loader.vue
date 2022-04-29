<template>
    <div class="loaderContainer">
        <div class='loader'><div><div><div><div><div><div></div></div></div></div></div></div></div>
    </div>
</template>

<style lang="scss">
    @import "resources/sass/variables";

    .loader {
        position: relative;
        width: 150px;
        height: 150px;
        display: block;
        overflow: hidden;

        div {
            height: 100%;
        }
    }

    .loader, .loader div {
        @include themed() {
            border-radius: 50%;
            padding: 8px;
            border: 2px solid transparent;
            animation: rotate linear 3.5s infinite;
            border-top-color: rgba(t('text'), .7);
            border-bottom-color: t('secondary');
        }
    }

    @keyframes rotate {
        0% {
            transform: rotate(0deg);
        }
        50% {
            transform: rotate(180deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
</style>
