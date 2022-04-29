const NodeRSA = require('node-rsa');
const Vue = require('vue');
import Bus from '../bus';
import { mapGetters } from 'vuex';

import publicKey from '../../../public/public.key';

const whispers = {
    data: {},
    publicKey: publicKey,
    queue: []
}

Vue.mixin({
    computed: {
        ...mapGetters(['user', 'isGuest'])
    },
    created() {
        /*setInterval(() => {
            const q = whispers.queue[0];
            if(!q) return;

            if(!window.$pingSent) {
                if(window.$pingTried) return;
                window.$pingTried = true;

                Bus.$emit('ws:pingStatus', false);

                this.whisper('Ping').then(() => {
                    window.$pingSent = true;
                    Bus.$emit('ws:pingStatus', true);
                });
                setTimeout(() => window.$pingTried = false, 1000);
                return;
            }

            whispers.queue = whispers.queue.filter((e) => e !== q);
            this.whisper(q.event, q.data).then(q.resolve, q.reject);
        }, 100);*/
        window.whisperTest = this.whisper;
    },
    methods: {
        async whisper(event, request = {}) {
            //const isGuest = this.isGuest, user = this.user;
            return new Promise((resolve, reject) => {
                /*if(!window.$pingSent && event !== 'Ping') {
                    whispers.queue.push({
                        event: event,
                        data: data,
                        resolve: resolve,
                        reject: reject
                    });
                    return;
                }

                const id = '_' + Math.random();

                const key = new NodeRSA();
                key.setOptions({ encryptionScheme: 'pkcs1_oaep' });
                key.importKey(publicKey, 'pkcs8-public-pem');

                window.Echo.private('Whisper').whisper(event, {
                    id: id,
                    data: data,
                    token: isGuest ? null : key.encrypt(user.token, 'base64')
                });

                whispers.data[id] = {
                    name: event,
                    request: data,
                    time: +new Date(),
                    resolve: resolve,
                    reject: reject
                };

                 */

                const time = +new Date();

                const handleApiResponse = (url, json) => {
                    if(json.message != null && json.errors != null) {
                        reject(0);
                        return;
                    }

                    if(json.code != null) {
                        if(Vue.prototype.$isDebug()) console.error(url, json.code + ' > ' + json.message);
                        reject(json.code);
                        return;
                    }

                    if(Vue.prototype.$isDebug()) console.log(url, +new Date() - time + 'ms', json);
                    resolve(json);
                }

                axios.post('/api/whisper', {
                    event: event,
                    data: request
                }).then(({ data }) => {
                    handleApiResponse(event, data);
                }).catch((e) => {
                    reject(e)
                });
            });
        }
    }
});

/*
Bus.$on('event:whisperResponse', (e) => {
    const whisper = whispers.data[e.id];
    if(whisper !== undefined) {
        const handleApiResponse = (url, json, resolve, reject) => {
            if(json.message != null && json.errors != null) {
                reject(0);
                return;
            }

            if(json.code != null) {
                if(Vue.prototype.$isDebug()) console.error(url, json.code + ' > ' + json.message);
                reject(json.code);
                return;
            }

            if(Vue.prototype.$isDebug()) console.log(url, json);
            resolve(json);
        }

        handleApiResponse(`WS ${whisper.name} > ${+new Date() - whisper.time}ms > ${JSON.stringify(whisper.request)}`, e.data, whisper.resolve, whisper.reject);
        delete whispers.data[e.id];
    }
});
 */
