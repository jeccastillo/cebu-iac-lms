<aside class="right-side" id="registration-container">    
<section class="content-header">
        <h1>
            User Group
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
                <!-- <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Term</th>
                            <th>Deficiency</th>
                            <th>Department</th>
                            <th>Remarks</th>
                            <th>Date Added</th>
                            <th>Added By</th>
                            <th>Date Resolved</th>
                            <th>Resolved By</th>
                            <th>Status</th>                             
                            <th>Actions</th>                                                       
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="deficiencies.length == 0">
                            <td colspan='8'>No Deficiencies for this term</td>
                        </tr>
                        <tr v-else v-for="item in deficiencies">
                            <td>{{ item.enumSem + " " + item.term_label + " " + item.strYearStart + "-" + item.strYearEnd}}</td>
                            <td>{{ item.details }}</td>
                            <td>{{ item.department }}</td>
                            <td>{{ item.remarks }}</td>
                            <td>{{ item.date_added }}</td>
                            <td>{{ item.added_by }}</td>
                            <td>{{ item.date_resolved }}</td>
                            <td>{{ item.resolved_by }}</td>
                            <td>{{ item.status  }}</td>
                            <td v-if="item.department == request.department && item.status != 'resolved' || user.intUserLevel == 2">
                                <a class="btn btn-primary" @click.prevent="resolveDeficiency(item.id)">Resolve</a> <a v-if="user.intUserLevel == 2 || user.special_role == 2" class="btn btn-success" href="#" data-toggle="modal" data-target="#temporaryResolve" @click="setResolveID(item.id)">Temp Resolve</a>
                            </td>
                            <td v-else></td>
                        </tr>
                    </tbody>
                </table>                               -->
            </div>        
        </div>
        
    </div>
    
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',       
        id: '<?php echo $id; ?>',        
        group_access:[],
        group_users:[],
        request:{
            id: '<?php echo $id; ?>',
            group_name: undefined,            
        }  
        
    },

    mounted() {

        let url_string = window.location.href;        
                
            //this.loader_spinner = true;
            axios.get(this.base_url + 'group/group_data/'+this.id)
                .then((data) => {                                      
                    this.request = data.data.group;
                    this.group_access = data.data.group_access;
                    this.group_users = data.data.group_users;                                        
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
        
       
                                       
    }

})
</script>

