
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
            <form @submit.prevent="submitNewCashier" class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add New Cashier</h4>
                    </div>
                    <div class="modal-body">
                        <select v-model="request.user_id" class="select2">                            
                            <option v-for="user in finance_users" :value="user.intID">{{ user.strFirstname + " " + user.strLastname }}</option>                            
                        </select>
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
        finance_users: [],
        request:{
            user_id: undefined,            
        },        
             
    },

    mounted() {
        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(base_url + 'finance/cashier_data/')
        .then((data) => {
            this.cashiers = data.cashiers;
            this.finance_users = data.finance_users;
            console.log(this.finance_users);
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {                
        submitNewCashier: function(){

        }

    }

})
</script>