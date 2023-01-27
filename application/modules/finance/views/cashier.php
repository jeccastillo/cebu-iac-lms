
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app" :href="base_url + 'admissionsV1/view_all_leads'" ><i class="ion ion-arrow-left-a"></i>All Students Applicants</a>                                                                                                                     
                </small>
            </h1>
        </section>
        <hr />
        <div class="content">
            <div class="row">       
                <div class="col-sm-12">
                    <div class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">New Application Transaction</h4>
                        </div>
                        <div class="box-body">
                            <div class="row">                                
                                <form @submit.prevent="submitManualPayment" method="post">                                                
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select @change="selectDescription" class="form-control" v-model="request.description">
                                                <option value="Reservation Payment">Reservation</option>
                                                <option value="Application Payment">Application</option>                                
                                            </select>
                                        </div>                                                
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>OR Number:</label>
                                            <input type="text" class="form-control" v-model="request.or_number" />
                                        </div>
                                    </div>                                    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Contact Number:</label>
                                            <input type="text" required class="form-control" v-model="request.contact_number" />
                                        </div>
                                    </div>
                                    
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Remarks:</label>
                                            <textarea type="text" required class="form-control" v-model="request.remarks"></textarea>
                                        </div>                                    
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Amount to Pay:</label>
                                            {{ request.subtotal_order }}
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Email: {{ request.email_address }}</label>                                                    
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button class="btn btn-primary btn-lg" type="submit">Submit Payment</button>
                                    </div>
                                </form>                                
                            </div>                            
                        </div>
                    </div>
                </div>                            
                <div class="col-sm-12">
                    <div class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">Cashiers</h4>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Cashier #</th>
                                    <th>Name</th>
                                    <th>OR Start</th>
                                    <th>OR End</th>
                                    <th>Current OR</th>                                                                        
                                </tr>    
                                <tr v-for="cashier in cashiers">
                                    <td>Cashier {{ cashier.id }}</td>
                                    <td>{{ cashier.strFirstname + " " + cashier.strLastname }}</td>                                    
                                    <td>{{ cashier.or_start }}</td>
                                    <td>{{ cashier.or_end }}</td>
                                    <td>{{ cashier.or_current }}</td>                                                                        
                                </tr>                                 
                            </table>
                            <hr />                                    
                        </div><!---box body--->
                        <div class="box-footer">
                            <button data-toggle="modal" data-target="#myModal" class="btn btn-primary">
                                    Add Cashier
                            </button>
                        </div>
                    </div><!---box--->                      
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->
        <div class="modal fade" id="myModal" role="dialog">
            <form @submit.prevent="updateOR" class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add OR Number</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>OR Number <span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" v-model="or_update.or_number" required></textarea>                        
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
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {        
        
        base_url: "<?php echo base_url(); ?>",   
        cashiers: [],
        request:{
            user_id: undefined,            
        },        
             
    },

    mounted() {

    },

    methods: {                


    }

})
</script>