<template>
    <div>
        <div class="row page-title">
            <div class="col-md-12">
                <div class="float-right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#create_standalone">Send notification</button>
                    <button class="btn btn-danger" data-toggle="modal" data-target="#create">Send push notification</button>
                </div>
                <h4 class="mb-1 mt-0">Notifications</h4>
            </div>
        </div>
        <div class="row" v-if="data.subscribers">
            <div class="col-12">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="media p-3">
                            <div class="media-body">
                                <span class="text-muted text-uppercase font-size-12 font-weight-bold">Subscribers</span>
                                <h2 class="mb-0">{{ data.subscribers }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row" v-if="data.global">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <a href="javascript:void(0)" class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#create_global">
                            Create
                        </a>
                        <h5 class="card-title mt-0 mb-0 header-title">Global Notifications</h5>

                        <div class="table-responsive mt-4">
                            <table class="table table-hover table-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th scope="col" style="width: 10%">Icon</th>
                                        <th scope="col">Text</th>
                                        <th scope="col" style="width: 10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="notification in data.global">
                                        <td style="width: 10%"><span class="badge badge-soft-danger py-1">{{ notification.icon }}</span></td>
                                        <td v-html="notification.text"></td>
                                        <td style="width: 10%"><button class="btn btn-danger btn-sm" @click="remove(notification._id)">Remove</button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="create_global" tabindex="-1" style="display: none;" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header py-3 px-4 border-bottom-0 d-block">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h5 class="modal-title">Global Notification</h5>
                    </div>
                    <div class="modal-body p-4">
                        <form class="needs-validation" name="event-form" novalidate="">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">FontAwesome 5/Win5X Icon</label>
                                        <input class="form-control" placeholder="Title" type="text" value="fal fa-exclamation-triangle" id="icon_global" v-model="icon">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Text</label>
                                        <input class="form-control" placeholder="Text" id="text_global" v-model="text">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6"></div>
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-light mr-1" id="close_global" data-dismiss="modal">Close</button>
                                    <div class="btn btn-success" id="finish_global" @click="sendGlobal">Send</div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="create_standalone" tabindex="-1" style="display: none;" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header py-3 px-4 border-bottom-0 d-block">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h5 class="modal-title">Notification</h5>
                    </div>
                    <div class="modal-body p-4">
                        <form class="needs-validation" name="event-form" novalidate="">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Title</label>
                                        <input class="form-control" placeholder="Title" type="text" id="title_standalone" v-model="title">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Text</label>
                                        <input class="form-control" placeholder="Text" id="message_standalone" v-model="text">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6"></div>
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-light mr-1" id="close_standalone" data-dismiss="modal">Close</button>
                                    <div class="btn btn-success" id="finish_standalone" @click="sendWeb">Send</div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="create" tabindex="-1" style="display: none;" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header py-3 px-4 border-bottom-0 d-block">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                        <h5 class="modal-title">Push Notification</h5>
                    </div>
                    <div class="modal-body p-4">
                        <form class="needs-validation" name="event-form" novalidate="">
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Title</label>
                                        <input class="form-control" placeholder="Title" type="text" id="title" v-model="title">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="control-label">Text</label>
                                        <input class="form-control" placeholder="Text" id="message" v-model="text">
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6"></div>
                                <div class="col-6 text-right">
                                    <button type="button" class="btn btn-light mr-1" id="close" data-dismiss="modal">Close</button>
                                    <div class="btn btn-success" id="finish" @click="sendPush">Send</div>
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
            axios.post('/admin/notifications/data').then(({ data }) => this.data = data);
        },
        data() {
            return {
                data: {},

                title: '',
                text: '',
                icon: ''
            }
        },
        methods: {
            remove(id) {
                axios.post('/admin/notifications/global_remove', { id: id }).then(() => this.$router.go());
            },
            sendPush() {
                axios.post('/admin/notifications/browser', {
                    title: $('#title').val(),
                    message: $('#message').val()
                }).then(() => {
                    $('#create').modal('hide');
                    $('.modal-backdrop').remove();
                    this.$toast.success('Success');
                });
            },
            sendWeb() {
                axios.post('/admin/notifications/standalone', {
                    title: this.title,
                    message: this.text
                }).then(() => {
                    $('#create_standalone').modal('hide');
                    $('.modal-backdrop').remove();
                    this.$toast.success('Success');
                });
            },
            sendGlobal() {
                axios.post('/admin/notifications/global', {
                    icon: $('#icon_global').val(),
                    text: $('#text_global').val()
                }).then(() => this.$router.go());
            }
        }
    }
</script>
