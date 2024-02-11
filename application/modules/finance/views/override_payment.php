
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
                    <form @submit.prevent="submitPaymentDetails" class="modal-dialog modal-lg">
                        <div class="box box-solid box-success">
                            <div class="box-header">                            
                                Manually Accept Payment    
                            </div>
                            <div class="box-body">                                    
                                <!-- modal header  -->                                        
                                <h4 class="modal-title">Payment Details</h4>                                
                                <div class="form-group">
                                    <label>Request ID</label>
                                    <input type="text" required v-model="request_id" class="form-control" />
                                </div>
                                <div class="form-group">
                                    <label>Type</label>
                                    <select required v-model="webhook" class="form-control">
                                        <option value="bdo">BDO Pay</option>
                                        <option value="maya">Maya</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Date Paid</label>
                                    <input type="date" required v-model="date_paid" class="form-control" />
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
        request_id: undefined,
        webhook: 'bdo',
        date_paid: undefined,
             
    },

    mounted() {
        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(base_url + 'finance/override_payment_data')
        .then((data) => {            
            this.user = data.data.user;           
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {                               
        submitPaymentDetails: function(){
            Swal.fire({
                title: 'Continue with the update',
                text: "Are you sure you want to accept this payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        //console.log(this.$refs['or_start'+id][0].value);
                        if(this.webhook == "maya")
                        {
                            var formdata = new FormData();
                            formdata.append('requestReferenceNumber',this.request_id);                                    
                            formdata.append('status','PAYMENT_SUCCESS');
                            formdata.append('date_paid',this.date_paid);
                            
                            
                        }
                        else if(this.webhook == "bdo"){
                            var formdata = new FormData();
                            formdata.append('req_reference_number',this.request_id);                                                                
                            formdata.append('decision','ACCEPT');
                            formdata.append('date_paid',this.date_paid);
                        }
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
                        .post(api_url + 'payments/webhook_' + this.webhook, formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {                
                            if (data == "<h3 style='text-align:center;'>Successful Transaction</h3>" && this.webhook == 'bdo'){
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
                                    Swal.hideLoading();                                 
                                });
                            }
                            
                        });
                    }
            });

        }
    }

})
</script>