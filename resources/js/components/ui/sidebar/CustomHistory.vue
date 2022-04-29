<template>
    <div class="customHistory" :data-custom-history="id"></div>
</template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                id: '_'+Math.random()
            }
        },
        watch: {
            entries() {
                if(this.entries.length > 30) this.entries.pop()
            }
        },
        computed: {
            ...mapGetters(['gameInstance'])
        },
        mounted() {
            const popover = (e, { clientSeed, serverSeed, nonce, placement = 'right' }) => {
                $(e).popover({
                    content: `
                        <div><strong>Client seed:</strong> ${clientSeed}</div>
                        <div><strong>Server seed:</strong> ${serverSeed}</div>
                        <div><strong>Nonce:</strong> ${nonce}</div>
                        <div><a class="disable-pjax" target="_blank" href="/fairness?verify=${this.gameInstance.id}-${serverSeed}-${clientSeed}-${nonce}">${this.$i18n.t('general.verify')}</a></div>
                    `,
                    html: true,
                    placement: placement,
                    trigger: 'manual'
                }).on('mouseenter', function() {
                    let _this = this;
                    $(this).popover('show');
                    $('.popover').on('mouseleave', () => $(_this).popover('hide'));
                }).on('mouseleave', function() {
                    let _this = this;
                    setTimeout(() => {
                        if(!$('.popover:hover').length) $(_this).popover('hide');
                    }, 10);
                });
            };

            Bus.$on('game:customHistory:add', (e) => {
                const el = $('<div>').addClass('element').html(e.text).attr('style', e.style);
                popover(el, e.seed);
                $(`[data-custom-history="${this.id}"]`).prepend(el);
                el.hide().slideDown('fast');
            });
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .customHistory {
        position: absolute;
        right: 60px;
        top: 50%;
        transform: translateY(-50%);
        display: flex;
        flex-direction: column;
        text-align: center;
        height: 180px;
        width: 45px;
        border-radius: 5px;
        overflow: hidden;

        @include themed() {
            background: rgba(darken(t('sidebar'), 10%), .5);
        }

        .element {
            padding: 0.955em 0;
            display: flex;
            align-content: center;
            justify-content: center;
            font-size: 0.9em;
            font-weight: 600;
            color: black;
            min-height: 45px;
        }
    }

    @include media-breakpoint-down(md) {
        .customHistory {
            transform: translateY(-50%) translateX(100%) scale(0.7);
        }
    }
</style>
