<template>
    <div v-if="!modules">
        <div class="row page-title">
            <div class="col-md-12">
                <h4 class="mb-1 mt-1">Modules</h4>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-6 col-md-4" v-for="game in games">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-1">{{ game.name }}</h5>
                            <h6 class="text-muted font-weight-normal mt-0 mb-3">{{ game.id }}</h6>
                            <div class="card-text text-center btn-group">
                                <button type="button" class="btn btn-primary btn-sm" @click="load(game, false)">Real</button>
                                <button type="button" class="btn btn-danger btn-sm" @click="load(game, true)">Demo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else>
        <div class="row page-title">
            <div class="col-md-12">
                <h4 class="mb-1 mt-1">{{ game.name }} ({{ isDemo ? 'demo' : 'real' }})</h4>
                <code>{{ game.id }}</code>
                <br>
                <a href="javascript:void(0)" @click="modules = null">< Back</a>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-body pt-2">
                            <h6 class="header-title mb-4">Supported modules</h6>
                            <div class="task-list-items">
                                <div class="card border mb-0" v-for="module in modules.filter((e) => e.supports)">
                                    <div class="card-body p-3">
                                        <h6 class="mt-0 mb-2 font-size-15 text-body" v-html="module.name"></h6>
                                        <p class="text-muted mb-2 font-weight-light" v-html="module.description"></p>
                                        <div class="custom-control custom-checkbox mt-3">
                                            <input :checked="module.isEnabled" type="checkbox" class="custom-control-input" :id="`check-${module.id}`" @change="toggleModule(module.id)">
                                            <label class="custom-control-label font-weight-light" :for="`check-${module.id}`">
                                                Enable
                                            </label>
                                        </div>
                                        <p class="mb-0 mt-4">
                                            <span class="text-nowrap align-middle font-size-13">
                                                Available settings: {{ module.settings.length }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-body pt-2">
                            <h6 class="header-title mb-4">Enabled modules</h6>
                            <div class="task-list-items">
                                <div class="card border mb-0" v-for="module in modules.filter((e) => e.isEnabled)">
                                    <div class="card-body p-3">
                                        <h6 class="mt-0 mb-2 font-size-15 text-body" v-html="module.name"></h6>
                                        <div class="mt-2">
                                            <template v-for="setting in module.settings">
                                                <div class="mb-2" v-if="setting.type === 'input'">
                                                    <div class="font-size-15 font-weight-bold" v-html="setting.name"></div>
                                                    <div class="text-muted font-weight-light" v-html="setting.description"></div>
                                                    <input type="text" class="form-control mt-1" :value="setting.value" :placeholder="setting.defaultValue" @input="changeOption(module.id, setting.id, $event.target.value)">
                                                </div>
                                                <div v-else>
                                                    Unknown option type "{{ setting.type }}"
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import { mapGetters } from 'vuex';

    export default {
        data() {
            return {
                game: null,
                isDemo: null,

                modules: null
            }
        },
        computed: {
            ...mapGetters(['games'])
        },
        methods: {
            changeOption(module, option, value) {
                axios.post('/admin/option_value', {
                    api_id: this.game.id,
                    module_id: module,
                    option_id: option,
                    demo: this.isDemo,
                    value: value
                });
            },
            toggleModule(id) {
                this.modules = null;
                axios.post('/admin/toggle_module', {
                    api_id: this.game.id,
                    module_id: id,
                    demo: this.isDemo
                }).then(() => this.load(this.game, this.isDemo));
            },
            load(game, isDemo) {
                this.modules = null;
                this.game = game;
                this.isDemo = isDemo;

                axios.post('/admin/modules', { game: this.game.id, demo: this.isDemo }).then(({ data }) => this.modules = data);
            }
        }
    }
</script>
