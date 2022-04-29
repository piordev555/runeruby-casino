<script>
import Bus from '../../bus';
export default {
    name: 'modal',
    created() {
        Bus.$on('modal:new', options => {
            const defaults = {
                title: null,
                dismissible: true,
                name: '',
                escapable: false,
                closeOnBackdrop: true,
                backButton: false,
                onClose() {},
                onDismiss() {}
            };
            options = Object.assign(defaults, options);
            this.modals.push(options);
            Bus.$emit('opened', {
                index: this.$last,
                options
            });
            this.body.classList.add('show');
            document.querySelector('.modals').scrollTop = 0;
        });
        Bus.$on('modal:toggleBackButton', data => {
            this.modals[this.$last].backButton = data;
        });
        Bus.$on('modal:close', data => {
            let index = null;
            if (data && data.$index) index = data.$index;
            if (data && data._isVue) {
                for (let [idx, modal] of this.$refs.components.entries()) {
                    if (data === modal) {
                        index = idx;
                        break;
                    }
                }
            }
            if (index === null) index = this.$last;
            this.close(data, index);
        });
        Bus.$on('modal:dismiss', index => {
            if (index === null) index = this.$last;
            this.dismiss(index);
        });
    },
    data() {
        return {
            modals: []
        }
    },
    computed: {
        current() {
            return this.modals[this.$last];
        },
        $last() {
            return this.modals.length - 1;
        },
        body() {
            if(typeof document !== 'undefined') return document.querySelector('body');
        }
    },
    methods: {
        back() {
            Bus.$emit('modal:back');
        },
        splice(index = null) {
            if (index === -1) return;
            // If there's nothing to close, ignore it
            if (!this.modals.length) return;
            // If there's no index, pop() it
            if (index === null) this.modals.pop();
            else this.modals.splice(index, 1);
            // And if it was the last window, notify that all instances are destroyed
            if (!this.modals.length) {
                this.body.classList.remove('show');
                Bus.$emit('destroyed');
            }
        },
        doClose(data = null, index) {
            if(!this.modals.length) return;
            if(!this.modals[index]) return;
            this.splice(index);
            // Firefox fix
            const modals = document.querySelector('.modals');
            if (modals) modals.scrollTop = 0;
        },
        // Close the modal and pass any given data
        close(data = null, index = null) {
            // Can't close if there's no modal open
            if (this.modals.length === 0) return;
            let localIndex = index;
            // If the index is a function, pass the current open modal index
            if (index && typeof index === 'function') localIndex = index(data, this.modals);
            // If the index is either null or undefined
            if (typeof localIndex !== 'number') localIndex = this.$last;
            // Notify the app about this window being closed
            Bus.$emit('closed', {
                index: localIndex,
                instance: this.modals[index],
                data
            });
            // Close callback
            if (localIndex !== false && this.modals[localIndex]) this.modals[localIndex].onClose(data);
            this.doClose(data, localIndex);
        },
        // Dismiss the active modal
        dismiss(index = null) {
            // Can't dismiss modal if there's no modal open
            if (this.modals.length === 0) return;
            let localIndex = index;
            // If the index is a function, pass the current open modal index
            if (index && typeof index === 'function') localIndex = index(this.$last);
            // If the index is either null or undefined
            if (typeof localIndex !== 'number') localIndex = this.$last;
            // Check dismiss callback result for prevention
            if (this.modals[localIndex].onDismiss() === false) return;
            // Notify the app about this window being closed
            Bus.$emit('dismissed', {
                index: localIndex,
                instance: this.modals[localIndex]
            });
            this.doClose(null, localIndex);
        },
        getCssClasses(index) {
            const modal = this.modals[index];
            let classNames = modal.name;
            if (index < this.$last) classNames += ' disabled';
            return classNames;
        },
        handleEscapeKey(e) {
            if (!this.modals.length) return;
            if (this.current.escapable) this.dismiss();
        },
        handleBackdropClick() {
            if (!this.modals.length) return;
            if (this.current.closeOnBackdrop === true) this.dismiss();
        }
    }
}
</script>

<template>
    <transition tag="div" name="modal">
        <div class="modals" v-show="modals.length" tabindex="0" @keyup.esc.prevent="handleEscapeKey($event)" @click="handleBackdropClick()">
            <div class="xmodal" v-for="(modal, index) in modals" :key="index" :class="getCssClasses(index)" @click.stop>
                <i class="modal-close fal fa-times" v-if="modal.dismissible" @click="handleBackdropClick"></i>
                <div v-if="modal.title" class="heading">
                    <transition :name="modal.page === 0 ? 'slide-right' : 'slide-left'" mode="out-in" tag="div">
                        <i class="fal fa-angle-left back" v-if="modal.backButton" key="1"></i>
                    </transition>
                    <span v-if="modal.title" class="title">{{ $t(modal.title) }}</span>
                </div>
                <div class="modal_template">
                    <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                        <component :is="modal.component" v-bind="modal.props" ref="components" class="modal_content"></component>
                    </overlay-scrollbars>
                </div>
            </div>
        </div>
    </transition>
</template>

<style lang="scss">
    @import "resources/sass/variables";

    body.show {
        overflow: hidden;
    }

    .modals {
        background-color: rgba(0,0,0,.5);
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 50000;
        overflow-x: hidden;
        overflow-y: auto;
        perspective: 500px;
        transition: opacity .4s ease;
        display: flex;
    }

    .xmodal {
        @include themed() {
            background: t('sidebar');
        }

        .modal-close {
            position: absolute;
            right: 15px;
            top: 15px;
            opacity: .3;
            cursor: pointer;
            transition: opacity .3s ease;
            z-index: 25;

            &:hover {
                opacity: 1;
            }
        }

        max-height: 75vh;
        border-radius: 3px;
        transition: all 0.6s ease;
        will-change: transform;
        width: 600px;
        height: fit-content;
        display: flex !important;
        flex-direction: column;
        outline: unset !important;
        overflow-x: hidden;
        margin: auto;

        &.disabled {
            opacity: 0.2;
            &::after {
                background: transparent;
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 100;
            }
        }

        .modal_template {
            height: calc(100% - 75px);
            overflow: hidden;

            .modal_content {
                overflow: hidden;
                padding: 0 20px 20px;
                display: flex;
                flex-direction: column;
            }

            .os-host {
                max-height: calc(75vh - 35px);
            }
        }

        .__view {
            height: fit-content;
            max-height: 80vh;
        }

        .heading {
            width: 100%;
            font-size: 1.2em;
            font-weight: 100;
            text-align: center;
            padding: 10px 10px 0;
        }

        .modalTitle {
            font-size: 1.1em;
        }

        @include themed() {
            .modal-tabs {
                display: flex;
                flex-direction: row;
                width: calc(100% - 40px);
                margin: 30px 20px;

                .modal-tab {
                    width: 100%;
                    background: t('input');
                    transition: background 0.3s ease;
                    text-align: center;
                    padding: 2px;
                    cursor: pointer;

                    &:hover {
                        background: lighten(t('input'), 2%);
                    }

                    &:first-child {
                        border-top-left-radius: 3px;
                        border-bottom-left-radius: 3px;
                    }

                    &:last-child {
                        border-top-right-radius: 3px;
                        border-bottom-right-radius: 3px;
                    }
                }

                .modal-tab.active {
                    background: t('secondary') !important;
                    color: white;
                }
            }
        }

        input {
            @include themed() {
                border: 1px solid darken(t('input'), 2%);
                background: lighten(t('input'), 2%);
                padding: 8px 15px;

                &:not(:read-only), &:not(:disabled) {
                    &:hover, &:active {
                        background: t('input');
                    }
                }
            }
        }

        @include themed() {
            .content {
                color: t('text');
                background: t('sidebar');
                border: 1px solid rgba(t('sidebar'), 0.04);
                padding: 15px;
                width: 100%;
                transform: scale(0.7);
                opacity: 0;
                transition: all 0.3s;
                height: fit-content;
                min-height: 150px;
            }

            .loaderContainer {
                margin: auto;
                margin-top: 20px;
            }

            .unavailable {
                z-index: 4;
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                @include blur(t('sidebar'), 0.8, 0.95, 24px);
                border-radius: 5px;
                .slanting {
                    transform: skewY(-5deg) translateY(-50%);
                    padding: 25px;
                    position: absolute;
                    top: 50%;
                    background: rgba(t('text'), 0.05);
                    width: 100%;
                    .unavailableContent {
                        font-size: 15px;
                        transform: skewY(5deg);
                        text-align: center;
                    }
                }
            }
        }
    }

    .modal-enter, .modal-leave-active {
        opacity: 0;
    }

    .modal-enter .xmodal, .modal-leave-active .xmodal {
        opacity: 0;
        transform: scale(0.95);
    }

    @media(max-width: 750px) {
        .xmodal {
            width: 100vw !important;
            border-radius: 0 !important;
        }
    }
</style>
