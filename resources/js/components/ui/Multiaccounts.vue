<template>
    <div class="media" v-if="info">
        <div class="media-body">
            <h6 class="font-weight-normal mt-0">
                <template v-if="!info.user.register_multiaccount_hash || !info.user.login_multiaccount_hash">
                    <div class="text-danger" v-if="!info.user.register_multiaccount_hash">Cleared cookies before registration</div>
                    <div class="text-danger" v-if="!info.user.login_multiaccount_hash">Cleared cookies before authorization</div>
                </template>
                <div v-if="info.same_register_hash.length <= 1 && info.same_login_hash.length <= 1 && info.same_register_ip.length <= 1 && info.same_login_ip.length <= 1">Good standing</div>
                <template v-else>
                    <template v-if="info.same_register_hash.length > 1 && info.user.register_multiaccount_hash">
                        <div class="text-danger">Same registration hash:</div>
                        <router-link :to="`/admin/user/${user._id}`" v-for="user in info.same_register_hash"  :key="Math.random()">{{ user.name }}<br></router-link>
                    </template>
                    <template v-if="info.same_login_hash.length > 1 && info.user.login_multiaccount_hash">
                        <div class="text-danger">Same auth hash:</div>
                        <router-link :to="`/admin/user/${user._id}`" v-for="user in info.same_login_hash"  :key="Math.random()">{{ user.name }}<br></router-link>
                    </template>
                    <template v-if="info.same_register_ip.length > 1">
                        <div class="text-danger">Same registration IP:</div>
                        <router-link :to="`/admin/user/${user._id}`" v-for="user in info.same_register_ip" :key="Math.random()">{{ user.name }}<br></router-link>
                    </template>
                    <template v-if="info.same_register_ip.length > 1">
                        <div class="text-danger">Same auth IP:</div>
                        <router-link :to="`/admin/user/${user._id}`" v-for="user in info.same_login_ip"  :key="Math.random()">{{ user.name }}<br></router-link>
                    </template>
                </template>
            </h6>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['id'],
        data() {
            return {
                info: null
            }
        },
        created() {
            axios.post('/admin/checkDuplicates', { id: this.id }).then(({ data }) => this.info = data);
        }
    }
</script>
