<template>
    <admin-layout v-if="!isGuest && user.user.access === 'admin' && $route.fullPath.toLowerCase().startsWith('/admin')"></admin-layout>
    <website-layout v-else></website-layout>
</template>

<script>
    import { mapGetters } from 'vuex';
    import Bus from "../bus";

    export default {
        created() {
            this.$store.dispatch('changeLocale', this.$store.state.locale);
            this.$store.dispatch('switchTheme', this.$store.state.theme);
            this.$store.dispatch('update');
            this.$store.dispatch('updateData');

            this.reconnectToWS();

            if(typeof URLSearchParams === 'function') {
                const params = new URLSearchParams(window.location.search);
                if(params.has('c')) this.setCookie('c', params.get('c'));
            }

            //let countDownDate = new Date("Jan 9, 2021 13:00:00 UTC").getTime(), now = new Date().getTime(), distance = countDownDate - now;
            //if(distance > 0 && this.$route.fullPath !== '/coming-soon-avoid' && this.getCookie('avoidComingSoon') !== 'true') this.$router.push('/coming-soon');
        },
        computed: {
            ...mapGetters(['user', 'isGuest', 'currencies', 'currency'])
        },
        methods: {
            reconnectToWS() {
                window.Echo.connector.disconnect();

                window.Echo = new window.LaravelEcho({
                    broadcaster: 'socket.io',
                    host: `${window.location.hostname}:2087`,
                    auth: {
                        headers: {
                            Authorization: `Bearer ${this.isGuest ? null : this.user.token}`
                        }
                    }
                });

                window.Echo.connector.socket.on('connect', () => Bus.$emit('ws:connect'));
                window.Echo.connector.socket.on('disconnect', () => Bus.$emit('ws:disconnect'));

                Echo.private(`App.User.${this.isGuest ? 'Guest' : this.user.user._id}`)
                    .listen('WhisperResponse', (e) => Bus.$emit('event:whisperResponse', e))
                    .listen('BalanceModification', (e) => Bus.$emit('event:balanceModification', e));

                if(!this.isGuest && (this.user.user.access === 'moderator' || this.user.user.access === 'admin'))
                    Echo.channel('Support').listen('SupportMessage', (e) => Bus.$emit('event:supportMessageAdmin', e));

                if(!this.isGuest)
                    Echo.channel(`Support.${this.user.user._id}`).listen('SupportMessage', (e) => Bus.$emit('event:supportMessage', e));

                Echo.channel('Everyone').listen('ChatMessage', (e) => Bus.$emit('event:chatMessage', e))
                    .listen('ChatRemoveMessages', (e) => Bus.$emit('event:chatRemoveMessages', e))
                    .listen('NewQuiz', (e) => Bus.$emit('event:chatNewQuiz', e))
                    .listen('QuizAnswered', (e) => Bus.$emit('event:chatQuizAnswered', e))
                    .listen('LiveFeedGame', (e) => Bus.$emit('event:liveGame', e))

                    .listen('MultiplayerBettingStateChange', (e) => Bus.$emit('event:multiplayerBettingStateChange', e))
                    .listen('MultiplayerBetCancellation', (e) => Bus.$emit('event:multiplayerBetCancellation', e))
                    .listen('MultiplayerGameFinished', (e) => Bus.$emit('event:multiplayerGameFinished', e))
                    .listen('MultiplayerGameBet', (e) => Bus.$emit('event:multiplayerGameBet', e))
                    .listen('MultiplayerTimerStart', (e) => Bus.$emit('event:multiplayerTimerStart', e))
                    .listen('MultiplayerDataUpdate', (e) => Bus.$emit('event:multiplayerDataUpdate', e));

                if(!this.isGuest) Echo.channel(`App.User.${this.user.user._id}`).notification((notification) => Bus.$emit('event:notification', notification));
            }
        },
        watch: {
            locale() {
                this.$router.replace({ params: { lang: this.locale } }).catch(() => {});
            },
            isGuest() {
                this.reconnectToWS();

                if(this.$route.meta.requiresAuth) this.$router.push('/');
                else if(this.$route.meta.requiresAccess) {
                    const access = {
                        user: 0,
                        moderator: 1,
                        admin: 2
                    };
                    if(this.isGuest || access[this.user.user.access ?? 'user'] < access[this.$route.meta.requiresAccess]) this.$router.push('/');
                }
            }
        }
    }
</script>
