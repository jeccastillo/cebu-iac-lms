<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            User Group
            <small>                
                <a class="btn btn-app" href="#" data-toggle="modal" data-target="#addFunction" ><i class="fa fa-plus"></i>Add Function</a>                
            </small>
        </h1>
        <hr />
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h3>Group</h3>
            </div>
            <div class="box-body">                
                <h4>Add/Edit Group</h4>
                <form method="post" @submit.prevent="submitGroup">
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>Group Name</label>
                            <input type="text" required class="form-control" v-model="request.group_name" />
                        </div>
                    </div>  
                    <hr />
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
                <hr />
                <div class="row">
                    <div class="col-sm-4 col-md-3" v-for="(fn,index) in group_functions">
                        <div class="panel panel-default">
                            <div class="panel-body">
                                {{ fn.name }}
                                <hr />
                                <div class="checkbox">
                                    <label>
                                        <input v-model="group_functions[index].read" role="switch" type="checkbox">
                                        Read
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input v-model="group_functions[index].write" role="switch" type="checkbox">
                                        Write
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <form method="post" @submit.prevent="submitUser">
                    <h4>Add User</h4>
                    <div class="row">
                        <div class="col-sm-6 form-group">
                            <label>User</label>
                            <select class="form-control" name="user_id" v-model="add_user.user_id">
                                <option v-for="item in faculty" :value="item.intID">{{ item.strLastname + " " + item.strFirstname }}</option>
                            </select>
                        </div>
                    </div>  
                    <hr />
                    <button type="submit" class="btn btn-primary">Add User</button>
                </form>
                <hr />
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>User</th>                                                    
                            <th>Actions</th>                                                       
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="group_users.length == 0">
                            <td colspan='8'>No Users for this Group</td>
                        </tr>
                        <tr v-else v-for="item in group_users">
                            <td>{{ item.strLastname + " " + item.strFirstname }}</td>                            
                            <td>
                                <a class="btn btn-primary" @click.prevent="deleteUser(item.uaid)">Delete</a> 
                            </td>                            
                        </tr>
                    </tbody>
                </table>                              
            </div>        
        </div>
        
    </div>
    <div class="modal fade" id="addFunction" role="dialog">
        <form ref="add_function" @submit.prevent="addFunction" method="post"  class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Add New Function</h4>
                </div>
                <div class="modal-body">
                    <div class="row">                        
                        <div class="form-group col-sm-6">
                            <label>Name</label>
                            <input required v-model="add_function.name" type="text" class="form-control">
                        </div>                       
                        <div class="form-group col-sm-6">
                            <label>Serial</label>
                            <input type="text" v-model="add_function.serial" class="form-control" />
                        </div>                        
                    </div>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
    </div>
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',       
        id: '<?php echo $id; ?>',                
        group_users:[],
        group_functions:[],
        faculty: [],
        request:{
            id: '<?php echo $id; ?>',
            group_name: undefined,    
            group_functions: undefined,        
        },
        add_function:{
            name: undefined,
            serial: undefined,
        },
        add_user:{
            user_id: undefined,
            group_id: undefined,
        }
    },

    mounted() {

        let url_string = window.location.href;        
                
            //this.loader_spinner = true;
            axios.get(this.base_url + 'group/group_data/'+this.id)
                .then((data) => {  
                    if(data.data.group){
                        this.add_user.group_id = this.id;
                        this.request = data.data.group;                        
                        this.group_users = data.data.group_users;   
                        this.group_functions = data.data.functions;      
                        this.faculty = data.data.faculty;                               
                    }
                })
            .catch((error) => {
                console.log(error);
                
            });    

    },

    methods: {              
        submitGroup: function(){            
            Swal.fire({
                title: 'Submit Group?',
                text: "Continue?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    this.request.group_functions = JSON.stringify(this.group_functions);
                    for (const [key, value] of Object.entries(this.request)) {
                        formdata.append(key,value);
                    }                                                              
                    return axios
                        .post('<?php echo base_url(); ?>group/submit_group',formdata, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            console.log(data.data);
                            if (data.data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    document.location = base_url + 'group/add_group/' + data.data.group_id;
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )
                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            });
        },
        deleteUser: function(id){
            Swal.fire({
                title: 'Delete User?',
                text: "Continue deleting user?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();                    
                    formdata.append('id',id);                    
                    return axios
                    .post(base_url + 'group/delete_user',formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                    .then(data => {
                        console.log(data.data);
                        if (data.data.success) {
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Failed!',
                                data.data.message,
                                'error'
                            )
                        }
                    });
                    
                },
                allowOutsideClick: () => !Swal.isLoading()
            });  
        },
        submitUser: function(){
            Swal.fire({
                title: 'Add User?',
                text: "Continue adding user?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    for (const [key, value] of Object.entries(this.add_user)) {
                        formdata.append(key,value);
                    }
                    return axios
                    .post(base_url + 'group/submit_user',formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                    .then(data => {
                        console.log(data.data);
                        if (data.data.success) {
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Failed!',
                                data.data.message,
                                'error'
                            )
                        }
                    });
                    
                },
                allowOutsideClick: () => !Swal.isLoading()
            });   
        },
        addFunction: function(){
            Swal.fire({
                title: 'Add Function?',
                text: "Continue adding function?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    for (const [key, value] of Object.entries(this.add_function)) {
                        formdata.append(key,value);
                    }
                    return axios
                    .post(base_url + 'group/add_function',formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                    .then(data => {
                        console.log(data.data);
                        if (data.data.success) {
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Failed!',
                                data.data.message,
                                'error'
                            )
                        }
                    });
                    
                },
                allowOutsideClick: () => !Swal.isLoading()
            });    
        }
        
       
                                       
    }

})
</script>

