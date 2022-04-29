<template></template>

<script>
    import Bus from '../../../bus';
    import { mapGetters } from 'vuex';
    import HelpModal from "../../modals/HelpModal";
    import ChangeClientSeedModal from "../../modals/ChangeClientSeedModal";

    export default {
        props: {
            data: {
                type: Object
            }
        },
        computed: {
            ...mapGetters(['gameInstance', 'sound', 'quick'])
        },
        mounted() {
            const createButton = (icon, callback) => {
                const e = document.createElement('div');
                e.className = 'action';
                e.innerHTML = `<i class="${icon}"></i>`;
                document.querySelector('.game-footer').appendChild(e);
                e.onclick = () => callback(e);
            }

            createButton('fas fa-balance-scale-right', () => ChangeClientSeedModal.methods.open());

            _.forEach(this.data.buttons, (e) => {
                switch (e) {
                    case "help":
                        createButton('fas fa-question-circle', () => HelpModal.methods.open(this.gameInstance.game.id));
                        break;
                    case "sound":
                        createButton(`fas fa-volume ${this.sound ? 'active' : ''}`, (e) => {
                            this.$store.dispatch('setSoundState', !this.sound);
                            e.querySelector('i').className = `fas fa-volume ${this.sound ? 'active' : ''}`;
                        });
                        break;
                    case "stats":
                        createButton(`fas fa-chart-area`, () => Bus.$emit('profitMonitoring:toggle'));
                        break;
                    case "quick":
                        createButton(`fas fa-bolt ${this.quick ? 'active' : ''}`, (e) => {
                            this.$store.dispatch('setQuickState', !this.quick);
                            e.querySelector('i').className = `fas fa-bolt ${this.quick ? 'active' : ''}`;
                        })
                        break;
                    default:
                        throw new Error(`Undefined footer type ${e}`);
                }
            });
        }
    }
</script>
