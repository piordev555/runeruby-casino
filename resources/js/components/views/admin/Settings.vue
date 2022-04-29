<template>
    <div>
        <div class="row page-title">
            <div class="col-md-12">
                <div class="float-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#create">Create</button>
                </div>
                <h4 class="mb-1 mt-0">Settings</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-lg-6" v-for="setting in settingsMutable">
                <div class="card">
                    <div class="card-body">
                        <h5><a href="javascript:void(0)" class="text-dark">{{ setting.name }}</a></h5>
                        <div class="text-muted">
                            {{ setting.description }}
                            <div class="form-group mt-2">
                                <input @input="change(setting.name, $event.target.value)" :value="setting.value" type="text" class="form-control" placeholder="Value">
                            </div>
                        </div>
                    </div>
                    <div class="card-body border-top">
                        <div class="row align-items-center">
                            <div class="col-sm-auto">
                                <ul class="list-inline mb-0">
                                    <li class="list-inline-item pr-2">
                                        <a @click="remove(setting.name)" href="javascript:void(0)" class="text-muted d-inline-block">
                                            Remove
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row page-title" v-if="settingsImmutable.length > 0">
            <div class="col-md-12">
                <h4 class="mb-1 mt-0">System Settings</h4>
                <small><code>Read-only</code></small>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-lg-6" v-for="setting in settingsImmutable">
                <div class="card">
                    <div class="card-body">
                        <h5><a href="javascript:void(0)" class="text-dark">{{ setting.name }}</a></h5>
                        <div class="text-muted">
                            {{ setting.description }}
                            <div class="form-group mt-2">
                                <input readonly :value="setting.value" type="text" class="form-control" placeholder="Value">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="create" tabindex="-1" style="display: none;" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header py-3 px-4 border-bottom-0 d-block">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h5 class="modal-title">New Key</h5>
                    </div>
                    <div class="modal-body p-4">
                        <form class="needs-validation" name="event-form" id="form-event" novalidate="">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Key</label>
                                        <input class="form-control" placeholder="Key" type="text" id="key" v-model="newSettingKey">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Description</label>
                                        <input class="form-control" placeholder="Description" id="description" v-model="newSettingDescription">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6"></div>
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-light mr-1" id="close" data-dismiss="modal">Close</button>
                                    <div class="btn btn-success" id="finish" @click="create">Create</div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        created() {
            axios.post('/admin/settings/get').then(({ data }) => {
                this.settingsMutable = data.mutable;
                this.settingsImmutable = data.immutable;
            });
        },
        data() {
            return {
                settingsMutable: [],
                settingsImmutable: [],

                newSettingKey: '',
                newSettingDescription: ''
            }
        },
        methods: {
            change(key, value) {
                axios.post('/admin/settings/edit', { key: key, value: value.length === 0 ? 'null' : value });
            },
            remove(key) {
                axios.post('/admin/settings/remove', { key: key }).then(() => this.$router.go());
            },
            create() {
                axios.post('/admin/settings/create', { key: this.newSettingKey, description: this.newSettingDescription }).then(() => window.location.reload());
            }
        }
    }
</script>
