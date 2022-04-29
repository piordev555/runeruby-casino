<template>
    <div :class="`supportWindow draggable ${show ? 'active' : ''}`">
        <div class="head">
            {{ $t('help.modal.title') }}
            <i class="fal fa-arrow-left" v-if="pageHistory.length > 1" @click="loadHistory"></i>
            <i class="fal fa-times" @click="show = false"></i>
        </div>
        <div class="supportWindowContent">
            <template v-if="page && response">
                <template v-if="page === 'index'">
                    <div class="contentWrapper">
                        <div class="content">
                            <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, overflowBehavior: { x: 'hidden' }, className: 'os-theme-thin-light' }">
                                <div class="indexTitle">{{ $t('help.modal.page.index.title') }}</div>
                                <div class="indexSubtitle">{{ $t('help.modal.page.index.description') }}</div>

                                <div v-if="user.user.access === 'moderator' || user.user.access === 'admin'" class="indexMenuEntry margin" @click="loadPage('moderate')">
                                    {{ $t('help.modal.page.index.menu.moderate') }}
                                    <i class="fal fa-angle-right"></i>
                                </div>

                                <div v-else class="indexMenuEntry margin" @click="loadPage('live_chat'); this.addBotMessage(this.$i18n.t('help.modal.page.live_chat.welcome_message'))">
                                    {{ $t('help.modal.page.index.menu.ask_question') }}
                                    <i class="fal fa-angle-right"></i>
                                </div>
                                <div class="indexMenuEntry" @click="openFAQ()">
                                    {{ $t('help.modal.page.index.menu.general_questions') }}
                                    <i class="fal fa-angle-right"></i>
                                </div>
                                <div class="indexSubtitle" style="font-weight: 600; margin-top: 15px;">
                                    {{ $t('help.modal.page.index.my_chats') }}
                                </div>
                                <div class="previousChats mt-2">
                                    <div class="empty" v-if="response.length === 0"><i class="fas fa-tumbleweed"></i></div>
                                    <template v-else>
                                        <div class="userChat" @click="currentLiveChat = chat._id; loadPage('chat', { id: chat._id });" :key="chat._id" v-for="chat in response">
                                            <div class="avatar">
                                                <img :src="chat.messages[chat.messages.length - 1].user.avatar" alt>
                                            </div>
                                            <div class="message">
                                                <div class="name">{{ chat.messages[chat.messages.length - 1].user.name }}</div>
                                                <div class="messagePreview">{{ chat.messages[chat.messages.length - 1].message }}</div>
                                            </div>
                                            <div class="unread" v-if="getUnread(chat, 'user') > 0">{{ getUnread(chat, 'user') }}</div>
                                        </div>
                                    </template>
                                </div>
                            </overlay-scrollbars>
                        </div>
                    </div>
                </template>
                <template v-else-if="page === 'moderate'">
                    <div class="empty" v-if="response.length === 0"><i class="fas fa-tumbleweed"></i></div>
                    <template v-else>
                        <overlay-scrollbars :options="{ scrollbars: { autoHide: 'leave' }, overflowBehavior: { x: 'hidden' }, className: 'os-theme-thin-light' }">
                            <div class="userChat" @click="currentLiveChat = chat._id; loadPage('chat', { id: chat._id });" :key="chat._id" v-for="chat in response">
                                <div class="avatar">
                                    <img :src="chat.messages[chat.messages.length - 1].user.avatar" alt>
                                </div>
                                <div class="message">
                                    <div class="name">{{ chat.messages[chat.messages.length - 1].user.name }}</div>
                                    <div class="messagePreview">{{ chat.messages[chat.messages.length - 1].message }}</div>
                                </div>
                                <div class="unread" v-if="getUnread(chat, 'user') > 0">{{ getUnread(chat, 'user') }}</div>
                            </div>
                        </overlay-scrollbars>
                    </template>
                </template>
                <template v-else-if="page === 'chat' || page === 'live_chat'">
                    <div class="contentWrapper chats withFooter">
                        <div class="content">
                            <overlay-scrollbars ref="messages" :options="{ scrollbars: { autoHide: 'leave' }, className: 'os-theme-thin-light' }">
                                <div :class="'message ' + (message.user._id === user.user._id ? 'own' : '')" v-for="message in response.messages">
                                    <div class="avatar" v-if="message.user._id !== user.user._id">
                                        <img :src="message.user.avatar">
                                    </div>
                                    <div class="messageContent">
                                        {{ message.message }}
                                    </div>
                                    <div class="avatar" v-if="message.user._id === user.user._id">
                                        <img :src="message.user.avatar">
                                    </div>
                                </div>
                            </overlay-scrollbars>
                        </div>
                    </div>
                    <div class="chatFooter">
                        <div class="text"><input ref="message" @keyup.enter.exact.prevent="sendMessage()" :placeholder="$t('general.chat.enter_message')"></div>
                        <div class="send"><i class="fas fa-paper-plane" @click="sendMessage()"></i></div>
                    </div>
                </template>
                <template v-else>
                    Unknown page
                </template>
            </template>
            <loader v-else></loader>
        </div>
    </div>
</template>

<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';
    import TermsModal from "../modals/TermsModal";

    export default {
        data() {
            return {
                show: false,

                pageHistory: [],
                currentLiveChat: null,
                page: null,

                response: null
            }
        },
        computed: {
            ...mapGetters(['user'])
        },
        methods: {
            sendMessage(message = null, callback = null) {
                if(!message) message = this.$refs['message'].value;

                this.whisper('SupportMessage', {
                    channel: this.currentLiveChat,
                    message: message
                }).then((e) => {
                    this.currentLiveChat = e.id;
                    if(callback) callback();
                }, (e) => {
                    switch (e) {
                        case 1: this.$toast.error(this.$i18n.t('chat.error.length')); break;
                        case 2: this.$toast.error(this.$i18n.t('chat.error.muted')); break;
                        case 3: this.$toast.error('Unknown chat id'); break;
                        case 4: this.$toast.error(this.$i18n.t('chat.error.closed')); break;
                    }
                });

                if(!callback) this.$refs['message'].value = '';
            },
            getUnread(chat, target) {
                let unread = 0;
                _.forEach(chat.messages, (message) => {
                    if(message.created_at <= (target === 'user' ? chat.user_read : chat.support_read)) return;
                    unread++;
                });
                return unread;
            },
            openFAQ() {
                TermsModal.methods.open('faq');
            },
            loadHistory() {
                if(this.pageHistory.length === 0) return;

                const entry = this.pageHistory[this.pageHistory.length < 2 ? 0 : this.pageHistory.length - 2];
                this.loadPage(entry.page, entry.data, true);

                this.pageHistory.pop();
                this.currentLiveChat = null;
            },
            addBotMessage(message) {
                this.response.messages.push({ message: message, user: { avatar: '/favicon.png' } });
            },
            loadPage(page, data = {}, fromHistory = false) {
                if(!fromHistory) {
                    this.pageHistory.push({
                        page: page,
                        data: data
                    });
                }

                this.response = null;
                this.page = page;

                switch (page) {
                    case 'index':
                        axios.post('/api/user/supportChats').then(({ data }) => this.response = data);
                        break;
                    case 'moderate':
                        axios.post('/api/chat/moderate/support').then(({ data }) => this.response = data);
                        break;
                    case 'chat':
                        axios.post('/api/user/supportChat', { id: data.id }).then(({ data }) => {
                            this.response = data;
                            setTimeout(() => this.$refs.messages._osInstnce.scroll({ y: '100%' }), 150);
                        });
                        break;
                    case 'live_chat':
                        this.response = { messages: [] };
                        this.currentLiveChat = 'new';
                        break;
                }
            }
        },
        mounted() {
            Bus.$on('support:deposit', (e) => {
                this.show = false;
                this.loadPage('live_chat', {}, true);
                this.sendMessage('< Deposit Request >',() => {
                    this.show = true;
                    Bus.$emit('support:deposit:ok');
                    this.addBotMessage('Your deposit request has been created. Our operators will answer soon.');
                });
            });

            Bus.$on('support:withdraw', (e) => {
                this.show = false;
                this.loadPage('live_chat', {}, true);
                this.sendMessage('< Withdraw Request >',() => {
                    this.show = true;
                    Bus.$emit('support:deposit:ok');
                    this.addBotMessage('Your withdraw request has been created. Our operators will answer soon.');
                });
            });

            Bus.$on('event:supportMessage', (e) => {
                if(!this.show) {
                    if(e.message.user._id !== this.user.user._id) {
                        this.playSound('/sounds/ball4.mp3', 200);
                    }
                }

                if(this.page === 'index') this.loadPage('index');
                else if(this.currentLiveChat === e.chat._id || (e.chat.user === this.user.user._id && (this.page === 'chat' || this.page === 'live_chat'))) {
                    this.response.messages.push(e.message);
                    setTimeout(() => this.$refs.messages._osInstnce.scroll({ y: '100%' }), 150);
                }
            });

            Bus.$on('event:supportMessageAdmin', (e) => {
                if(this.currentLiveChat === e.chat._id) {
                    this.response.messages.push(e.message);
                    setTimeout(() => this.$refs.messages._osInstnce.scroll({ y: '100%' }), 150);
                }
                else if(this.page === 'moderate') this.loadPage('moderate');
            });

            Bus.$on('toggleSupportWindow', () => this.show = !this.show);

            let x, y;
            $('.supportWindow .head').on('mousedown', function (e) {
                if (e.offsetX === undefined) {
                    x = e.pageX - $(this).offset().left;
                    y = e.pageY - $(this).offset().top;
                } else {
                    x = e.offsetX;
                    y = e.offsetY;
                }

                $('body').addClass('noselect');
            });

            $('body').on('mouseup', function (e) {
                $('body').removeClass('noselect');
            });

            $('body').on('mousemove', function (e) {
                if ($(this).hasClass('noselect')) $('.supportWindow').offset({
                    top: e.pageY - y,
                    left: e.pageX - x
                });
            });

            this.loadPage('index', {});
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .supportWindow {
        .os-host, .os-content {
            height: 450px;
        }

        .chats {
            .os-host, .os-content {
                height: 380px !important;
            }
        }

        @include themed() {
            bottom: 85px;
            right: $chat-width + 20px;
            width: 350px;
            height: 520px;

            .userChat {
                display: flex;
                flex-direction: row;
                align-items: center;
                position: relative;
                transition: background 0.3s ease;
                background: t('sidebar');
                cursor: pointer;
                padding: 15px;

                &:hover {
                    background: t('body');
                }

                .avatar {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-right: 15px;

                    img {
                        width: 32px;
                        height: 32px;
                        border-radius: 50%;
                    }
                }

                .message {
                    display: flex;
                    flex-direction: column;
                    width: 100%;
                    margin-bottom: unset !important;

                    .name {
                        font-weight: 600;
                    }

                    .messagePreview {
                        text-overflow: ellipsis;
                        width: 225px;
                        overflow: hidden;
                        white-space: nowrap;
                    }
                }

                .unread {
                    position: absolute;
                    top: 50%;
                    right: 15px;
                    transform: translateY(-50%);
                    border-radius: 50%;
                    background: t('body');
                    width: 32px;
                    height: 32px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    text-align: center;
                    font-size: 0.8em;
                }
            }

            .supportWindowContent {
                height: 100%;
                display: flex;
                flex-direction: column;

                .loaderContainer {
                    margin: auto;
                }
            }

            .indexTitle {
                font-size: 2.4em;
                font-weight: 600;
                font-family: 'Roboto', sans-serif;
            }

            .indexSubtitle {
                font-size: 1.1em;
                margin-top: -5px;
                font-family: 'Roboto', sans-serif;
            }

            .indexMenuEntry {
                background: t('sidebar');
                padding: 10px 15px;
                transition: background 0.3s ease;
                cursor: pointer;
                display: flex;
                align-items: center;

                .fa-angle-right {
                    margin-left: auto;
                }

                &:hover {
                    background: lighten(t('input'), 1.5%);
                }
            }

            .empty {
                width: fit-content;
                font-size: 6.5em;
                opacity: 0.3;
                height: fit-content;
                margin-left: auto;
                margin-right: auto;
            }

            .indexMenuEntry.margin {
                margin-top: 50px;
            }

            .message {
                display: flex;
                position: relative;
                margin-bottom: 10px;

                *:last-child {
                    margin-bottom: 0;
                }

                .messageContent {
                    padding: 10px 15px;
                    border-radius: 3px;
                    width: fit-content;
                    max-width: 70%;
                    font-size: 14px;
                    background: lighten(t('input'), 1.5%);
                    overflow-wrap: break-word;
                    margin-top: 10px;

                    &:first-child {
                        margin-top: 0;
                    }

                    float: left;
                    clear: both;
                }

                .avatar {
                    margin-right: 10px;
                    cursor: pointer;
                    margin-top: auto;

                    img, svg {
                        width: 32px;
                        height: 32px;
                        border-radius: 50%;
                    }
                }
            }

            .message.own {
                .avatar {
                    margin-right: unset;
                    margin-left: 10px;
                }

                .messageContent {
                    background: t('secondary');
                    float: right;
                    color: white;
                    margin-left: auto;
                }
            }

            .contentWrapper {
                height: calc(100% - 43px);
            }

            .contentWrapper.withFooter {
                height: calc(100% - 43px - 85px);
            }

            .chatFooter {
                display: flex;
                padding: 15px;
                height: 86px;

                .text {
                    width: 90%;

                    input {
                        appearance: none;
                        background: none;
                        border: none;
                        border-bottom: 2px solid #3c3b44;
                        width: 100%;
                        color: white;
                        height: 56px;
                    }
                }

                .send {
                    display: flex;
                    width: 10%;
                    justify-content: center;

                    i {
                        color: t('secondary');
                        margin-top: auto;
                        margin-left: 14px;
                        margin-right: auto;
                        font-size: 1.1em;
                        cursor: pointer;
                        transition: color 0.3s ease;

                        &:hover {
                            color: lighten(t('secondary'), 1.5%);
                        }
                    }
                }
            }

            .content {
                padding: 15px;
            }
        }
    }
</style>
