<template>
    <div>
        <div class="row page-title align-items-center">
            <div class="col-sm-4 col-xl-6">
                <h4 class="mb-1 mt-0">Users</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="datatable" class="table dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Created at</th>
                                </tr>
                            </thead>
                            <tbody>
                                <router-link v-if="users" tag="tr" :to="'/admin/user/'+user._id" v-for="user in users" style="cursor: pointer" :key="user._id">
                                    <td><img alt :src="user.avatar" style="width: 32px; height: 32px; margin-right: 5px;"> {{ user.name }}</td>
                                    <td>{{ new Date(user.created_at).toLocaleString() }}</td>
                                </router-link>
                                <div v-else>This may take a while...</div>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    require('datatables.net');

    export default {
        data() {
            return {
                users: null
            }
        },
        created() {
            axios.post('/admin/users').then(({data}) => {
                this.users = data;

                setTimeout(() => {
                    $('#datatable').DataTable({
                        destroy: true,
                        "language": {
                            "paginate": {
                                "previous": "Previous ",
                                "next": " Next"
                            }
                        },
                        "drawCallback": function () {
                            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                        }
                    });
                }, 1000);
            });
        }
    }
</script>

<style lang="scss">
    #datatable {
        color: black;
    }
</style>
