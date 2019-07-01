<template>
    <div class="container">
        <div class="row mt-5" v-if="$gate.isAdminOrAuthor()">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">All Users</h3>

                        <div class="card-tools">
                            <button class="btn btn-success btn-sm" @click="newModal">
                                Add New <i class="fas  fa-user-plus fa-fw"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover">
                            <tbody>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Registered Date</th>
                                    <th>Modify</th>
                                </tr>
                                <tr v-for="user in users.data" :key="user.id ">
                                    <td>{{ user.id }}</td>
                                    <td>{{ user.name }}</td>
                                    <td>{{ user.email }}</td>
                                    <td>{{ user.type | upText }}</td>
                                    <td>{{ user.created_at | myDate }}</td>
                                    <td>
                                        <a href="#" @click="editModal(user)">
                                            <i class="fa fa-edit blue"></i>
                                        </a>
                                        <a href="#" @click="deleteUser(user.id)">
                                            <i class="fa fa-trash red"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <pagination :data="users" @pagination-change-page="getResults"></pagination>
                    </div>
                </div>
                <!-- /.card -->
            </div>
        </div>

        <div v-if="!$gate.isAdminOrAuthor()">
            <not-found></not-found>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="addNewUser" tabindex="-1" role="dialog" aria-labelledby="addNewUserLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <!--<h5 class="modal-title" id="addNewUserLabel">{{ editMode ? 'Edit User' : 'Add New User' }}</h5>-->
                        <h5 v-show="!editMode" class="modal-title" id="addNewUserLabel">Add New User</h5>
                        <h5 v-show="editMode" class="modal-title" id="addNewUserLabel">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="editMode ? updateUser() : createUser()" @keydown="form.onKeydown($event)">
                        <div class="modal-body">

                            <div class="form-group">
                                <input v-model="form.name" type="text" name="name" placeholder="Enter Name"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('name') }">
                                <has-error :form="form" field="name"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.email" type="email" name="email" placeholder="Enter Email Address"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('email') }">
                                <has-error :form="form" field="email"></has-error>
                            </div>

                            <div class="form-group">
                                <textarea v-model="form.bio" type="text" name="bio" placeholder="Enter Short Bio For User (Optional)"
                                          class="form-control" :class="{ 'is-invalid': form.errors.has('bio') }"></textarea>
                                <has-error :form="form" field="bio"></has-error>
                            </div>

                            <div class="form-group">
                                <select v-model="form.type" name="type" id="type" class="form-control" :class="{ 'is-invalid': form.errors.has('type') }">
                                    <option value="">Select User Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">Standard User</option>
                                    <option value="author">Author</option>
                                </select>
                                <has-error :form="form" field="type"></has-error>
                            </div>

                            <div class="form-group">
                                <input v-model="form.password" type="password" name="password" placeholder="Enter Password"
                                       class="form-control" :class="{ 'is-invalid': form.errors.has('password') }">
                                <has-error :form="form" field="password"></has-error>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                            <button v-show="!editMode" type="submit" class="btn btn-primary">Create</button>
                            <button v-show="editMode" type="submit" class="btn btn-success">Update</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</template>

<script>
    export default {
        data(){
            return {
                editMode: false,
                users: {},
                form: new Form({
                    id: '',
                    name: '',
                    email: '',
                    password: '',
                    type: '',
                    bio: '',
                    photo: '',
                })
            }
        },
        methods: {
            getResults(page = 1){
                axios.get('api/user?page=' + page)
                    .then(response => {
                        this.users = response.data;
                    });
            },
            updateUser(){

                this.$Progress.start();

                this.form.put('api/user/'+this.form.id)
                    .then(() => {

                        $('#addNewUser').modal('hide');

                        // toast.fire({
                        //     type: 'success',
                        //     title: 'User updated successfully'
                        // })

                        swal.fire(
                            'Updated!',
                            'Information has been updated.',
                            'success'
                        );

                        Fire.$emit('afterUpdateUser');

                        this.$Progress.finish();
                    })
                    .catch(() => {
                        this.$Progress.fail()
                    })

            },
            editModal(user){
                this.editMode = true;
                this.form.reset();
                $('#addNewUser').modal('show');
                this.form.fill(user);
            },
            newModal(){
                this.editMode = false;
                this.form.reset();
                $('#addNewUser').modal('show');
            },
            deleteUser(id){
                swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        // send request to server to delete
                        this.form.delete('api/user/' + id).then(() => {

                            swal.fire(
                                'Deleted!',
                                'User deleted.',
                                'success'
                            );

                            Fire.$emit('AfterDeleteUser');

                        }).catch(() => {
                            swal("Failed", "There was something wrong", "warning");
                        })
                    }
                })
            },
            loadUsers(){
                if(this.$gate.isAdminOrAuthor()){
                    axios.get('api/user').then(({ data}) => (this.users = data));
                }
            },
            createUser(){

                this.$Progress.start()

                this.form.post('api/user')
                    .then(() => {
                        //after a user is created a new event is created "AfterCreateUser"
                        Fire.$emit('AfterCreateUser');

                        $('#addNewUser').modal('hide');

                        toast.fire({
                            type: 'success',
                            title: 'User created successfully'
                        })

                        this.$Progress.finish()
                    })
                    .catch(() => {

                    });

            }
        },
        created(){

            this.loadUsers();

            // an option to refresh users every 3 seconds, but not good because even if a user isn't doing
            // anything on the page it still sends request to the server. this is not a good practise

            // setInterval(() => this.loadUsers(), 3000);

        //    we listen and get if the AfterCreateUser event has been created, we then respond by loading the users
            Fire.$on('AfterCreateUser', () => {
                this.loadUsers()
            })

            Fire.$on('AfterDeleteUser', () => {
                this.loadUsers()
            })

            Fire.$on('afterUpdateUser', () => {
                this.loadUsers()
            })

        }
    }
</script>
