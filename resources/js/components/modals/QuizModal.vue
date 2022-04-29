<script>
    import Bus from '../../bus';
    import { mapGetters } from 'vuex';

    export default {
        methods: {
            open() {
                Bus.$emit('modal:new', {
                    name: 'quizModal',
                    component: {
                        data() {
                            return {
                                question: '',
                                answer: '',

                                disabled: false
                            }
                        },
                        methods: {
                            send() {
                                this.disabled = true;

                                axios.post('/api/chat/moderate/quiz', {
                                    question: this.question,
                                    answer: this.answer
                                }).then(() => Bus.$emit('modal:close')).catch(() => this.disabled = false);
                            }
                        },
                        template: `
                                <div>
                                    <div class="cc_label">{{ $t('general.chat_commands.modal.quiz.question') }}</div>
                                    <input v-model="question" type="text">

                                    <div class="cc_label">{{ $t('general.chat_commands.modal.quiz.answer') }}</div>
                                    <input v-model="answer" type="text">

                                    <button class="btn btn-primary mt-2" @click="send" :disabled="disabled">{{ $t('general.chat_commands.modal.rain.send') }}</button>
                                </div>`
                    }
                });
            }
        }
    }
</script>

<style lang="scss">
    @import "resources/sass/variables";

    .xmodal.quizModal {
        max-width: 250px;
        padding-top: 20px !important;

        .cc_label {
            margin-bottom: 5px;
        }
    }
</style>
