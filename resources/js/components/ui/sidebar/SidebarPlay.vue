<template>
    <button class="btn btn-block btn-primary mt-2 p-3 play-button" :disabled="dis" @click="onClick">
        {{ getButtonText() }}
    </button>
</template>

<script>
    import { mapGetters } from 'vuex';
    import AuthModal from '../../modals/AuthModal';
    import Bus from '../../../bus';

    export default {
        computed: {
            ...mapGetters(['gameInstance', 'isGuest', 'demo', 'currencies', 'currency', 'quick'])
        },
        props: ['dis'],
        methods: {
            onClick() {
                const ref = window.$gameRef;

                if(this.gameInstance.playTimeout && this.gameInstance.bettingType === 'manual' || $('.play-button').hasClass('disabled')) return;
                if(this.gameInstance.bettingType === 'auto' && this.isGuest) {
                    AuthModal.methods.open('auth');
                    return;
                }

                const stop = () => this.updateGameInstance((i) => i.game.autoBetSettings.state = false);

                const play = (successCallback = null, errorCallback = null) => {
                    this.updateGameInstance((i) => {
                        i.playTimeout = true;
                        i.game.currentProfit = null;
                    });

                    if(this.isExtendedGameStarted()) {
                        if(!ref.extendedAutoBetHandle || this.gameInstance.bettingType === 'manual') this.finishExtended();
                    } else {
                        if(ref.preStart) ref.preStart();
                        this.whisper('Play', {
                            api_id: this.gameInstance.game.id,
                            bet: ref.customWagerCalculation ? ref.customWagerCalculation() : this.gameInstance.bet,
                            demo: this.demo,
                            currency: this.currency,
                            quick: this.quick,
                            data: typeof ref.getClientData === 'undefined' ? {} : ref.getClientData()
                        }).then(({ response }) => {
                            this.$store.dispatch('pushRecentGame', this.gameInstance.game.id);

                            $('.game-history .history *').removeClass('highlight');
                            $('.resultPopup').stop().fadeOut('fast', function() {
                                $(this).remove();
                            });

                            if(response.game !== undefined) { // instanceof QuickGame
                                setTimeout(() => this.pushStats(response.game), response.game.delay);
                            } else if(response.type === 'extended') {
                                this.updateGameInstance((i) => {
                                    i.playTimeout = false;
                                    i.game.extendedId = response.id;
                                    i.game.extendedState = 'in-progress';
                                });
                            } else if(response.type === 'multiplayer') {
                                this.updateGameInstance((i) => {
                                    i.playTimeout = false;
                                    i.game.extendedId = response.id;
                                    i.game.extendedState = response.canBeFinished ? 'in-progress' : 'finished';
                                });
                            }

                            window.$gameRef.callback(response);
                            if(successCallback != null) successCallback(response);
                        }, (code) => {
                            if(code === 0) {
                                if(typeof errorCallback === 'function') errorCallback(0);
                                return;
                            }

                            this.updateGameInstance((i) => i.playTimeout = false);

                            if(errorCallback != null) errorCallback(code);

                            if (code >= 1) {
                                if(window.$gameRef.errorCallback) window.$gameRef.errorCallback(code);
                                else this.$toast.error(this.$i18n.t('general.error.unknown_error', {'code': code}));
                                return;
                            }

                            if (!_.isInteger(code)) {
                                console.error('Invalid validation data', code);
                                return;
                            }

                            switch (code) {
                                case -1:
                                    this.$toast.error(this.$i18n.t('general.error.wager', { value: this.rawBitcoin(this.currency, 1000) }));
                                    break;
                                case -2:
                                    AuthModal.methods.open('auth');
                                    this.$toast.error(this.$i18n.t('general.error.auth'));
                                    break;
                                case -3:
                                    this.$toast.error(this.$i18n.t('general.error.unknown_game'));
                                    break;
                                case -4:
                                    this.$toast.error(this.$i18n.t('general.error.invalid_wager'));
                                    break;
                                case -5:
                                    this.$toast.error(this.$i18n.t('general.error.disabled'));
                                    break;
                                case -7:
                                    if(this.isGuest) AuthModal.methods.open('auth');
                                    this.$toast.error(this.$i18n.t('general.error.disabled_demo_bets'));
                                    break;
                                case -8:
                                    // Game already has started
                                    break;
                            }
                        });
                    }
                };

                if(this.gameInstance.bettingType === 'manual') play();
                else {
                    this.updateGameInstance((i) => i.game.autoBetSettings.stop = stop);
                    if(this.gameInstance.game.autoBetSettings.state) stop();
                    else {
                        this.updateGameInstance((i) => i.game.autoBetSettings.state = true);

                        const next = () => {
                            if(this.gameInstance.game.autoBetSettings.games > 0 && this.gameInstance.game.autoBetSettings.currentIteration >= this.gameInstance.game.autoBetSettings.games) stop();
                            else {
                                if(this.gameInstance.playTimeout) {
                                    setTimeout(() => {
                                        if(!this.gameInstance.game.autoBetSettings.state) return;
                                        next();
                                    }, 100);
                                    return;
                                }

                                if(+new Date() < this.gameInstance.game.autoBetSettings.timeout) {
                                    setTimeout(next, this.gameInstance.game.autoBetSettings.timeout - +new Date());
                                    return;
                                }

                                this.updateGameInstance((i) => i.game.autoBetSettings.timeout = +new Date() + 50);

                                const handleNext = (win) => {
                                    if (win && this.gameInstance.game.autoBetSettings.stopOnWin) stop();
                                    else {
                                        const handle = ref.customBetIncrease ? ref.customBetIncrease
                                            : (category) => {
                                                if(this.gameInstance.game.autoBetSettings[category].action === 'reset') {
                                                    this.updateGameInstance((i) => {
                                                        i.bet = this.gameInstance.game.autoBetSettings.initialBet;
                                                        Bus.$emit('sidebar:updateBet', { bet: i.bet });
                                                    });
                                                }
                                                else if(this.gameInstance.game.autoBetSettings[category].value > 0) {
                                                    this.updateGameInstance((i) => {
                                                        i.bet = i.bet + ((( this.gameInstance.game.autoBetSettings[category].value / 100) * i.bet));
                                                        Bus.$emit('sidebar:updateBet', { bet: i.bet });
                                                    });
                                                }
                                            };

                                        handle(win ? 'win' : 'loss');
                                        if(this.gameInstance.game.autoBetSettings.state) next();
                                    }
                                };

                                this.updateGameInstance((i) => i.game.autoBetSettings.nextGameHandler = handleNext);

                                if(!ref.extendedAutoBetHandle) play((response) => handleNext(response.game.win), stop);
                                else play(() => ref.extendedAutoBetHandle(() => this.finishExtended(true, (response) => handleNext(response.game.status === 'win'))));

                                this.updateGameInstance((i) => i.game.autoBetSettings.currentIteration++);
                            }
                        };

                        this.updateGameInstance((i) => {
                            i.game.autoBetSettings.initialBet = i.game.autoBetSettings.customBetIncrease ? i.game.autoBetSettings.customBetIncrease('initialBet') : i.bet;
                            i.game.autoBetSettings.currentIteration = 0;
                            i.game.autoBetSettings.next = next;
                        });

                        next();
                    }
                }
            },
            getButtonText() {
                if(this.gameInstance.game.autoBetSettings.state) return this.$i18n.t('general.stop');

                if(this.isExtendedGameStarted()) {
                    if(this.gameInstance.game.currentProfit) return this.$i18n.t('general.take', { value: this.gameInstance.game.currentProfit });
                    return this.$i18n.t('general.cancel');
                }
                return this.gameInstance.bettingType === 'manual' ? this.$i18n.t('general.play') : this.$i18n.t('general.start');
            }
        }
    }
</script>
