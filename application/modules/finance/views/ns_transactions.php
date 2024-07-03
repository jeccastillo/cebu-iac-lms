<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>       
                NS Payments         
                <small>       
                <a class="btn btn-app" :href="base_url + 'finance/view_payees_cashier'" ><i class="ion ion-arrow-left-a"></i>All Payees</a>              
                </small>                
            </h1>
        </section>
        <hr />
        <div class="content">            
            <div class="row">       
                <div class="col-sm-12">
                    <div class="box box-widget widget-user-2">
                        <!-- Add the bg color to the header using any of the bg-* classes -->
                        <div class="widget-user-header bg-red">
                            <div class="pull-right" style="margin-left:1rem;">
                                <select class="form-control" @change="selectTerm($event)" v-model="request.sem">
                                    <option v-for="s in sy" :value="s.intID">{{ s.term_student_type}} {{ s.enumSem }} {{ s.term_label }} {{ s.strYearStart }} - {{ s.strYearEnd }}</option>
                                </select>
                            </div>                        
                            <h3 v-if="payee" class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ payee.firstname+' '+payee.lastname }}</h3>                                            
                        
                        </div>                
                    </div>
                </div>                            
                <div class="col-sm-12">
                    <div class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">Transactions</h4>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>OR Number</th>
                                    <th>Cashier</th>
                                    <th>Payment Type</th>
                                    <th>Reference No.</th>
                                    <th>Amount Paid</th>
                                    <th>Online Payment Charge</th>
                                    <th>Total Due</th>
                                    <th>Status</th>
                                    <th>Online Response Message</th>
                                    <th>Date Updated</th>
                                    <th>Actions</th>
                                </tr>                                                                                                                        
                                <tr v-for="payment in payments">
                                    <td>{{ payment.or_number }}</td>
                                    <td><a href="#" @click.prevent.stop="cashierDetails(payment.cashier_id)">{{ payment.cashier_id }}</a></td>
                                    <td>{{ payment.description }}</td>
                                    <td>{{ payment.check_number }}</td>
                                    <td>{{ payment.subtotal_order }}</td>
                                    <td>{{ payment.charges }}</td>
                                    <td>{{ payment.total_amount_due }}</td>
                                    <td>{{ payment.status }}</td>                                            
                                    <td>{{ payment.response_message }}</td>
                                    <td>{{ payment.updated_at }}</td>            
                                    <td>                                        
                                        <button v-if="payment.or_number"                                             
                                                @click="printOR(payment)" 
                                                class="btn btn-primary">
                                                Print OR
                                        </button>
                                    </td>                                    
                                </tr>                                                                                                                                    
                            </table>                               
                        </div><!---box body--->
                    </div><!---box--->                      
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->
        <form ref="print_or" method="post" :action="base_url + 'pdf/print_or'" target="_blank">
            <input type="hidden" name="campus" :value="request.student_campus">
            <input type="hidden" name="student_name" v-model="or_print.student_name">
            <input type="hidden" name="cashier_id" v-model="or_print.cashier_id">
            <input type="hidden" name="student_id" v-model="or_print.student_id">
            <input type="hidden" name="student_address" v-model="or_print.student_address">
            <input type="hidden" name="is_cash" v-model="or_print.is_cash">
            <input type="hidden" name="check_number" v-model="or_print.check_number">
            <input type="hidden" name="or_number" v-model="or_print.or_number" />
            <input type="hidden" name="remarks" v-model="or_print.remarks">
            <input type="hidden" name="description" v-model="or_print.description" />
            <input type="hidden" name="total_amount_due" v-model="or_print.total_amount_due" /> 
            <input type="hidden" name="name" v-model="or_print.student_name" />       
            <input type="hidden" name="transaction_date" v-model="or_print.transaction_date" />               
            <input type="hidden" name="payee_id" v-model="or_print.payee_id" />   
            <input type="hidden" name="sem" v-model="or_print.sem" />
        </form>        
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
        student: undefined, 
        base_url: "<?php echo base_url(); ?>", 
        payee_id: "<?php echo $payee_id; ?>",  
        payee: undefined,   
        request:{
            first_name: "<?php echo $first_name; ?>",
            last_name: "<?php echo $last_name; ?>",
            sem: "<?php echo $sem; ?>",
        },
        base_url: "<?php echo base_url(); ?>",                   
        user: {
            special_role:0,
        },              
        payments: [],                          
        cashier: undefined, 
        sy: [],      
        or_print: {
            or_number: undefined,
            description: undefined,
            total_amount_due: undefined,
            student_name: undefined,
            transaction_date: undefined,
            student_name: undefined,
            student_address: undefined,
            student_id: undefined,
            remarks: undefined,
            is_cash: undefined,
            cashier_id: undefined,
            check_number: undefined,    
            sem: undefined,
            payee_id: "<?php echo $payee_id; ?>",        
        },
             
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        const d = new Date();
        let year = d.getFullYear();

        this.loader_spinner = true;
        axios.post(api_url + 'finance/ns_transactions',this.request,{
            headers: {
                Authorization: `Bearer ${window.token}`
            }
        })
        .then((data) => {                                            
            this.payments = data.data.data;
            axios.get(base_url + 'finance/ns_transactions_data/' + this.payee_id + '/' + this.request.sem)
            .then((data) => {            
                this.cashier = data.data.cashier;                
                this.user = data.data.user;  
                this.payee = data.data.payee;  
                this.sy = data.data.sy;                                                    
                if(this.cashier){
                    this.request.or_number = this.cashier.or_current;                    
                    this.request.cashier_id = this.cashier.user_id;                    
                }
            })
            .catch((error) => {
                console.log(error);
            })  
                        
        })
        .catch((error) => {
            console.log(error);
        })

    },

    methods: {   
        printOR: function(payment){        
            Swal.fire({
                title: 'Continue with Printing OR',
                text: "Are you sure you want to continue? You can only print the OR once",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (data) => {    
                        this.or_print.or_number = payment.or_number;
                        this.or_print.description = payment.description;
                        this.or_print.total_amount_due = payment.subtotal_order;
                        this.or_print.transaction_date = payment.updated_at;
                        this.or_print.remarks = payment.remarks;
                        this.or_print.student_name =  this.payee.lastname+", "+this.payee.firstname+", "+this.payee.middlename;    
                        this.or_print.student_address = this.payee.address;
                        this.or_print.student_id = '';
                        this.or_print.is_cash = payment.is_cash;
                        this.or_print.check_number = payment.check_number;
                        this.or_print.cashier_id = payment.cashier_id;                        
                        this.or_print.sem = payment.sy_reference;
                        this.$nextTick(() => {
                            this.$refs.print_or.submit();
                        });            
                    }
            });  
        },  
        cashierDetails: function(id){
            axios.get(base_url + 'finance/cashier_details/' + id)
            .then((data) => {            
                var cashier_details = data.data.cashier_data;
                Swal.fire({
                    title: "Cashier",
                    text: cashier_details.strFirstname+" "+cashier_details.strLastname,
                    icon: "info"
                })
            })

        },  
        selectTerm: function(event){
            document.location = base_url + "finance/ns_transactions/" + this.payee_id + "/" + event.target.value;
        },
       


    }

})
</script>