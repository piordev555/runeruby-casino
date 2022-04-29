<template>
    <div class="page_not_found container-fluid">
        <div class="title">{{ $t('404.title') }}</div>
        <div class="subtitle">{{ $t('404.subtitle') }}</div>
    </div>
</template>

<style lang="scss">
.page_not_found {
    text-align: center;
    .title {
        font-weight: 600;
        font-size: 3.5em;
    }
    .subtitle {
        font-size: 1.05em;
        margin-top: 15px;
    }
}
@media(max-width: 1000px) {
    .page_not_found {
        .title {
            font-size: 2.0em !important;
        }
        .subtitle {
            font-size: 1.0em !important;
        }
    }
}
</style>
