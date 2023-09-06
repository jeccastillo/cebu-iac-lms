
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>
                    <!-- <a class="btn btn-app" :href="base_url + 'unity/logs/0/0/Cashier'" ><i class="fa fa-file" aria-hidden="true"></i>View Cashier Logs</a>                                                                                                                               -->
                </small>
            </h1>
        </section>
        <hr />
        <div class="content">                        
                <div class="col-sm-12">
                    <form @submit.prevent="submitNewPayee" class="modal-dialog modal-lg">
                        <div class="box box-solid box-success">
                            <div class="box-header">                            
                                <h4 class="box-title">Payee</h4>                            
                            </div>
                            <div class="box-body">                                    
                                <!-- modal header  -->                                        
                                <h4 class="modal-title">Payee Details</h4>                                
                                <div class="form-group">
                                    <label>ID Number</label>
                                    <input type="text" required v-model="request.id_number" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" required v-model="request.firstname" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" required v-model="request.lastname" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>Middle Name</label>
                                    <input type="text" v-model="request.middlename" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>TIN</label>
                                    <input type="text" v-model="request.tin" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>Address</label>
                                    <textarea type="text" v-model="request.address" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Contact Number</label>
                                    <input type="text" v-model="request.contact_number" class="form-control" />
                                </div>
                            </div><!---box body--->
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">Submit</button>                                        
                            </div>                        
                        </div><!---box--->                      
                    </form>
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->       
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
        edit_value: "",
        not_edit_mode: true,
        edit_text:"Turn on Edit Mode",
        edit_class:"btn-primary",
        request:{
            id: undefined,
            id_number: undefined,
            firstname: undefined,            
            lastname: undefined,
            middlename: undefined,
            tin: undefined,
            address: undefined,
            contact_number: undefined,
        },        
             
    },

    mounted() {
        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(base_url + 'finance/payee_data/<?php echo $id; ?>')
        .then((data) => {
            if(data.data.payee)
                this.request = data.data.payee;           
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {                       
        submitNewPayee: function(){
            
            var formdata = new FormData();                    
            for(const [key,value] of Object.entries(this.request)){                   
                formdata.append(key,value);
            }

            axios
            .post(base_url + 'finance/submit_payee', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {                

                if (data.data.success) {
                    //document.location = base_url+"finance/payee/"+data.data.id;
                } else {
                    Swal.fire(
                        'Failed!',
                        data.data.message,
                        'error'
                    )
                }
            });
        },
        enableField: function(id){
            document.getElementById(id).disabled = false;
            document.getElementById(id).focus();
        },
        changeValue: function(id){
            
            //console.log(this.$refs['or_start'+id][0].value);
            var formdata = new FormData();
            formdata.append('intID',id);            
            formdata.append('start',this.$refs['or_start'+id][0].value);
            formdata.append('end',this.$refs['or_end'+id][0].value);                       
            axios
            .post(base_url + 'finance/update_cashier', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {                
                if (data.data.success) {
                    Swal.fire(
                        'Updated',
                        data.data.message,
                        'success'
                    ).then(function(){
                        if(data.data.reload)
                            location.reload();
                    });
                } else {
                    Swal.fire(
                        'Failed!',
                        data.data.message,
                        'error'
                    ).then(function() {
                        location.reload();
                    });
                }
                
            });
        }


    }

})
</script>