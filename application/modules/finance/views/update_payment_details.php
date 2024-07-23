
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>Payment Update
                <small>

                </small>
            </h1>
        </section>
        <hr />
        <div class="content">                        
                <div class="col-sm-12">                    
                    <label>Search by OR Number</label>
                    <div class="form-group">
                        <input type="text" v-model="or_number" class="form-control" />
                        <button @click="getPaymentDetails" class="btn btn-primary">Search</button>                    
                    </div>
                    <form v-if="payment_detail" @submit.prevent="submitPaymentDetails" class="modal-dialog modal-lg">
                        <div class="box box-solid box-success">
                            <div class="box-header">                            
                                Update Details    
                            </div>
                            <div class="box-body">                                    
                                <!-- modal header  -->                                        
                                <h4 class="modal-title">Payment Details</h4>                                
                                <table class="table">
                                    <tr>
                                        <th>Payee</th>
                                        <td>{{ payment_detail.lastname + " " + payment_detail.firstname }}</td>
                                    </tr>
                                    <tr>
                                        <th>Description</th>
                                        <td>{{ payment_detail.description }}</td>
                                    </tr>
                                    <tr>
                                        <th>Amount</th>
                                        <td>{{ payment_detail.subtotal_order }}</td>
                                    </tr>
                                </table>                                                              
                                <div class="form-group">
                                    <label>OR Date</label>
                                    <input type="date" required v-model="request.or_date" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>Change OR Number</label>
                                    <input type="text" required v-model="request.or_number" class="form-control" />
                                </div>
                            </div><!---box body--->
                            <div class="box-footer">
                                <button :disabled="!payment_detail" type="submit" class="btn btn-primary">Update</button>                                        
                            </div>                        
                        </div><!---box--->                      
                    </form>
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->       
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {        
        
        base_url: "<?php echo base_url(); ?>",   
        or_number: undefined,
        payment_detail: undefined,
        campus: undefined,
        request:{
            id: undefined;
            or_number: undefined,
            or_date: undefined,
        }
             
    },

    mounted() {
        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(base_url + 'finance/override_payment_data')
        .then((data) => {            
            this.user = data.data.user;  
            this.campus = data.data.campus;         
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {                  
        getPaymentDetails: function(){
            axios
            .get(api_url + 'finance/get_payment_detail/'+ this.or_number + '/' + this.campus, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {                
                this.payment_detail = data.data.data;
                this.request.id = this.payment_detail.id;
                this.request.or_number = this.payment_detail.or_number;
                this.request.or_date = this.payment_detail.or_date;                    
            });
        },
        submitPaymentDetails: function(){
            Swal.fire({
                title: 'Continue with the update',
                text: "Are you sure you want to update this payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        //console.log(this.$refs['or_start'+id][0].value);                                                
                       
                        Swal.fire({
                            showCancelButton: false,
                            showCloseButton: false,
                            allowEscapeKey: false,
                            title: 'Updating',
                            text: 'Processing Data do not leave page',
                            icon: 'info',
                        })
                        Swal.showLoading();
                        axios
                        .post(api_url + 'finance/update_payment_details', this.request, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {                
                            
                            Swal.fire(
                                'Done',
                                data.data.message,
                                'success'
                            ).then(function(){
                                if(data.data.reload)
                                    location.reload();
                            });
                            
                            
                        });
                    }
            });

        }
    }

})
</script>