<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'vip',
                    component: {
                        computed: {
                            ...mapGetters(['user', 'currencies'])
                        },
                        data() {
                            return {
                                percent: 0
                            }
                        },
                        mounted() {
                            $('.expandableBlockHeader').on('click', function() {
                                $(this).parent().find('.expandableBlockContent').slideToggle('fast');
                                $(this).find('i:last-child').toggleClass('fa-angle-left').toggleClass('fa-angle-up');
                            });

                            try {
                                this.percent = (this.user.user.vipLevel === 5 ? 100 : (this.currencies.vipClosestWager / this.currencies[this.currencies.vipClosest].vip.breakpoints[this.user.user.vipLevel + 1]) * 100).toFixed(2);
                            } catch (e) {
                                this.percent = 0;
                            }
                        },
                        template: `
                            <div>
                                <img class="vip-logo" src="/img/misc/vip-header.svg" alt>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" :style="{ width: percent + '%' }">{{ percent < 8 ? '' : percent + '%' }}</div>
                                </div>
                                <div class="vipProgress">
                                    <div>
                                        {{ $t('vip.rank.'+(user.user.vipLevel === 5 ? 4 : user.user.vipLevel)) }}
                                    </div>
                                    <div>
                                        <svg v-if="user.user.vipLevel === 0"><use href="#vip-ruby"></use></svg>
                                        <svg v-else-if="user.user.vipLevel === 1"><use href="#vip-emerald"></use></svg>
                                        <svg v-else-if="user.user.vipLevel === 2"><use href="#vip-sapphire"></use></svg>
                                        <svg v-else-if="user.user.vipLevel === 3"><use href="#vip-diamond"></use></svg>
                                        <svg v-else-if="user.user.vipLevel >= 4"><use href="#vip-gold"></use></svg>
                                        {{ $t('vip.rank.'+(user.user.vipLevel === 5 ? 5 : user.user.vipLevel + 1)) }}
                                    </div>
                                </div>
                                <div class="vipDesc mb-2">{{ $t('vip.description', { currency: currencies.vipClosest.toUpperCase() }) }}</div>
                                <div class="font-weight-bold" style="font-size: 1.05em">{{ $t('vip.benefits') }}</div>
                                <div class="vipDesc">{{ $t('vip.benefits_description') }}</div>
                                <div class="expandableBlock">
                                    <div class="expandableBlockHeader">
                                        <svg><use href="#vip-ruby"></use></svg>
                                        {{ $t('vip.rank.1') }}
                                        <i class="fas fa-angle-left"></i>
                                    </div>
                                    <div class="expandableBlockContent" style="display: none;">
                                        <ul>
                                            <li>{{ $t('vip.benefit_list.ruby.1') }}</li>
                                            <li>{{ $t('vip.benefit_list.ruby.2') }}</li>
                                            <li>{{ $t('vip.benefit_list.ruby.3') }}</li>
                                            <li>{{ $t('vip.benefit_list.ruby.4') }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="expandableBlock">
                                    <div class="expandableBlockHeader">
                                        <svg><use href="#vip-emerald"></use></svg>
                                        {{ $t('vip.rank.2') }}
                                        <i class="fas fa-angle-left"></i>
                                    </div>
                                    <div class="expandableBlockContent" style="display: none;">
                                        <ul>
                                            <li>{{ $t('vip.benefit_list.emerald.1') }}</li>
                                            <li>{{ $t('vip.benefit_list.emerald.2') }}</li>
                                            <li>{{ $t('vip.benefit_list.emerald.3') }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="expandableBlock">
                                    <div class="expandableBlockHeader">
                                        <svg><use href="#vip-sapphire"></use></svg>
                                        {{ $t('vip.rank.3') }}
                                        <i class="fas fa-angle-left"></i>
                                    </div>
                                    <div class="expandableBlockContent" style="display: none;">
                                        <ul>
                                            <li>{{ $t('vip.benefit_list.sapphire.1') }}</li>
                                            <li>{{ $t('vip.benefit_list.sapphire.2') }}</li>
                                            <li>{{ $t('vip.benefit_list.sapphire.3') }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="expandableBlock">
                                    <div class="expandableBlockHeader">
                                        <svg><use href="#vip-diamond"></use></svg>
                                        {{ $t('vip.rank.4') }}
                                        <i class="fas fa-angle-left"></i>
                                    </div>
                                    <div class="expandableBlockContent" style="display: none;">
                                        <ul>
                                            <li>{{ $t('vip.benefit_list.diamond.1') }}</li>
                                            <li>{{ $t('vip.benefit_list.diamond.2') }}</li>
                                            <li>{{ $t('vip.benefit_list.diamond.3') }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="expandableBlock">
                                    <div class="expandableBlockHeader">
                                        <svg><use href="#vip-gold"></use></svg>
                                        {{ $t('vip.rank.5') }}
                                        <i class="fas fa-angle-left"></i>
                                    </div>
                                    <div class="expandableBlockContent" style="display: none;">
                                        <ul>
                                            <li>{{ $t('vip.benefit_list.gold.1') }}</li>
                                            <li>{{ $t('vip.benefit_list.gold.2') }}</li>
                                            <li>{{ $t('vip.benefit_list.gold.3') }}</li>
                                            <li>{{ $t('vip.benefit_list.gold.4') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        `
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.vip {
        width: 500px;

        .vipTree {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            margin-bottom: -37px;
            padding-top: 30px;

            img {
                width: 100px;
                position: relative;
                margin-left: -10px;

                &:nth-child(1) {
                    margin-left: 0;
                }

                &:nth-child(2) {
                    margin-top: -40px;
                    z-index: 4;
                }

                &:nth-child(3) {
                    margin-top: -75px;
                    z-index: 5;
                }

                &:nth-child(4) {
                    margin-top: -40px;
                    z-index: 4;
                }
            }
        }

        .vip-logo {
            width: 40%;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 25px;
        }

        .vipDesc {
            @include themed() {
                color: rgba(t('text'), 0.8);
                font-size: 0.9em;
            }
        }

        .progress {
            height: 14px;
            border-radius: 3px;
            margin: 2.5em 0 0;

            @include themed() {
                background: darken(t('sidebar'), 3%);

                .progress-bar {
                    height: 14px;
                    background: t('secondary');
                    font-size: 0.65em;
                }
            }
        }

        .vipProgress {
            display: flex;
            flex-direction: row;
            margin-top: 10px;
            margin-bottom: 2.5em;

            div {
                display: inline-flex;
                position: relative;
                svg {
                    width: 14px;
                    height: 14px;
                    position: absolute;
                    margin-left: -20px;
                    top: 50%;
                    transform: translateY(-50%);
                }
            }

            div:last-child {
                margin-left: auto;
            }
        }

        .expandableBlock {
            margin-top: 15px;

            @include themed() {
                .expandableBlockHeader {
                    background: darken(t('sidebar'), 3%);
                    border-radius: 0.25rem;
                    padding: 0.75rem 1.5rem;
                    display: flex;
                    align-content: center;
                    cursor: pointer;

                    svg {
                        width: 14px;
                        height: 14px;
                        margin-right: 10px;
                        margin-top: 4px;
                    }

                    i {
                        position: absolute;
                        right: 35px;
                        margin-top: 5px;
                    }
                }

                .expandableBlockContent {
                    border: 1px solid darken(t('sidebar'), 3%);
                    border-top: none;
                    padding: 15px 23px;

                    ul {
                        margin-bottom: 0;
                        list-style: none;
                        padding-left: 0;
                    }
                }
            }
        }
    }

    @media(max-width: 500px) {
        .xmodal.vip {
            width: 100vw !important;
        }
    }
</style>
