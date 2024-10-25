
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <small>
                <a class="btn btn-app" :href="base_url + 'finance/payments/0/1'" ><i class="ion ion-arrow-left-a"></i>Non-student Payments</a>                                                                                                                     
            </small>            
            <h1>                                
                Add Non-Student Payment
            </h1>
        </section>
        <hr />
        <div class="content">            
            <div class="row">       
                <div class="col-sm-12">
                    <div v-if="cashier" class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">New Application Transaction - Cashier {{ cashier.intID }}</h4>
                            
                        </div>
                        <div class="box-body">
                            <div class="row">   
                                <div class="form-group col-sm-6">
                                    <label>Select From List</label>
                                    <select ref="payee" v-model="selected_payee" class="form-control" @change="selectPayee($event)">
                                        <option v-for="(item,index) in payees" :value="index">{{ item.lastname + " " + item.firstname}}</option>
                                    </select>                        
                                </div>     
                            </div>                                
                            <div class="row">
                                <div class="col-sm-6">
                                    <div style="margin-bottom:1rem">
                                        <label class="radio-inline">
                                            <input type="radio"
                                                v-model="windowPayment"
                                                value="invoice"> Invoice
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio"
                                                v-model="windowPayment"
                                                value="official receipt"> Official Receipt
                                        </label>

                                    </div>
                                </div>
                                <form @submit.prevent="submitManualPayment" method="post">                                                                                                                                
                                    <input type="hidden" required  class="form-control" v-model="request.description">                                                                                        
                                    <div class="col-sm-12">
                                        <label>Name: {{ request.last_name+" "+request.first_name+" "+request.middle_name}}</label>
                                    </div>                                                                        
                                    <input type="hidden" required class="form-control" placeholder="First Name" v-model="request.first_name" />                                                        
                                    <input type="hidden" required class="form-control" placeholder="Last Name" v-model="request.last_name" />                            
                                    <input type="hidden" class="form-control" placeholder="Middle Name" v-model="request.middle_name" />
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Select Term</label>
                                            <select ref="payee" v-model="request.sy_reference" class="form-control">
                                                <option v-for="(item,index) in sy" :value="item.intID">{{ item.enumSem + " " + item.term_label + " SY "+item.strYearStart+"-"+item.strYearEnd }}</option>
                                            </select>               
                                        </div>         
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Particulars:</label>
                                            <label>Particulars:</label>
                                            <select class="form-control"
                                                v-model="description_other">
                                                <option v-for="p in particulars"
                                                    :value="p.name">{{p.name}}</option>
                                            </select>                                            
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Enter amount to pay/refund:</label>
                                            <input type="number" step=".01" required class="form-control" v-model="amount_to_pay" />
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
                                            <label>Payment Type</label>
                                            <select class="form-control" v-model="request.is_cash">
                                                <option value="1">Cash</option>
                                                <option value="0">Check</option>                                                        
                                                <option value="2">Credit Card</option>
                                                <option value="3">Debit Card</option>
                                                <option value="4">Online Payment</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Payment Status</label>
                                            <select class="form-control" v-model="request.status">
                                                <option value="Paid">Paid</option>
                                                <option value="Pending">Pending</option>                                                        
                                                <option value="Refunded">Refunded</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Check/Credit/Debit Number:</label>
                                            <input type="text" :disabled="request.is_cash == 1 || request.is_cash == 4" required class="form-control" v-model="request.check_number" />
                                        </div>
                                    </div>                                   
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Remarks:</label>
                                            <textarea type="text" required class="form-control" v-model="request.remarks"></textarea>
                                        </div>                                    
                                    </div>                                    
                                    <div v-if="windowPayment == 'invoice'" class="col-sm-6">
                                        <div class="form-group">
                                            <label>Invoice Number:</label>
                                            <div>{{ request.invoice_number }}</div>
                                            <input type="hidden" class="form-control" v-model="request.invoice_number" />
                                        </div>
                                    </div>  
                                    <div v-else class="col-sm-6">
                                        <div class="form-group">
                                            <label>OR Number:</label>
                                            <div>{{ request.or_number }}</div>
                                            <input type="hidden" class="form-control" v-model="request.or_number" />
                                        </div>
                                    </div>
                                    <!-- <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Amount to Pay:</label>
                                            {{ request.subtotal_order }}
                                        </div>
                                    </div> -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Email:</label>
                                            <input type="email" required class="form-control" v-model="request.email_address" />                                                    
                                        </div>
                                    </div>
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Vatable Amount :</label>    
                                        <input @change="computeVat" step=".01" type="number"
                                                class="form-control"
                                                v-model="request.invoice_amount">                                                
                                    </div>
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Vat Exempt Tax :</label>    
                                        <input @change="computeVat" step=".01" type="number"
                                                class="form-control"
                                                v-model="request.invoice_amount_ves">                                                
                                    </div>
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Vat Zero Rated Sales :</label>    
                                        <input @change="computeVat" step=".01" type="number"
                                                class="form-control"
                                                v-model="request.invoice_amount_vzrs">                                                
                                    </div>
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Less EWT:</label>    
                                        <select @change="computeVat"
                                                class="form-control"
                                                v-model="request.withholding_tax_percentage">                                                
                                                <option value="0">None</option>
                                                <option value="1">1%</option>
                                                <option value="2">2%</option>
                                                <option value="5">5%</option>
                                                <option value="10">10%</option>
                                                <option value="15">15%</option>
                                        </select>
                                    </div>
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Total Sales:</label>    
                                        {{ total_sales_formatted }}                                               
                                    </div>                                               
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Value Added Tax:</label>    
                                        {{ less_vat_formatted }}                                               
                                    </div>  
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Less EWT:</label>    
                                        {{ less_ewt_formatted }}                                               
                                    </div>                                               
                                    <div
                                        class="form-group col-sm-6">
                                        <label>Total Amount Due:</label>    
                                        {{ total_amount_computed_formatted }}                                               
                                    </div>   
                                    <div class="col-sm-12">
                                        <button :disabled="!request.or_number && !request.invoice_number" class="btn btn-primary btn-lg" type="submit">Submit Payment</button>
                                    </div>
                                </form>                                
                            </div>                            
                        </div>
                    </div>
                </div>                                            
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
        applicant_id: undefined,                
        refunded_payments: [],    
        amount_to_pay: 0,
        description_other: '', 
        cashier: undefined,
        selected_payee: undefined,
        sy: [],
        windowPayment: "invoice",
        payees: [],
        net_vat: 0,
        less_vat: 0,
        less_ewt: 0,
        total_amount_computed: 0,
        total_sales: 0,
        less_vat_formatted: 0,
        less_ewt_formatted: 0,
        particulars:[],
        total_amount_computed_formatted: 0,
        total_sales_formatted: 0,
        request:{
            first_name: '',
            slug: 0,
            middle_name: '',
            last_name: '',
            contact_number: '',
            email_address: '',
            mode_of_payment_id: 26,
            description: undefined, 
            sy_reference: undefined,
            or_number:undefined,
            invoice_number: undefined,
            remarks:'',
            subtotal_order: 0,
            convenience_fee: 0,
            total_amount_due: 0,            
            charges: 0,       
            cashier_id: undefined,     
            status: 'Paid',
            is_cash: 1,            
            check_number: undefined,
            withholding_tax_percentage: 0,
            invoice_amount: 0,
            invoice_amount_ves: 0,
            invoice_amount_vzrs: 0,
            student_campus: '<?php echo $campus; ?>',
        },
       
             
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        
            
            axios.get(base_url + 'finance/other_payment_data/')
            .then((data) => {            
                this.cashier = data.data.cashier;
                this.request.sy_reference = data.data.current_sem;                
                if(this.cashier){
                    this.request.or_number = this.cashier.or_current;                    
                    this.request.invoice_number = this.cashier.invoice_current;
                    this.request.cashier_id = this.cashier.user_id;    
                    this.payees = data.data.payees;   
                    this.particulars = data.data.particulars;
                    this.sy = data.data.sy;             
                }
            })
            .catch((error) => {
                console.log(error);
            })  
            
            

    },

    methods: {              
        selectPayee: function(event){                        
            this.request.first_name = this.payees[event.target.value].firstname;
            this.request.last_name = this.payees[event.target.value].lastname;
            this.request.middle_name = this.payees[event.target.value].middlename;
            this.request.contact_number = this.payees[event.target.value].contact_number;
            this.request.email_address = this.payees[event.target.value].email;
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
        computeVat: function(){
            this.net_vat = this.request.invoice_amount / 1.12;
            this.less_vat = this.net_vat * .12;             
            this.total_sales = parseFloat(this.net_vat) + parseFloat(this.request.invoice_amount_ves) + parseFloat(this.request.invoice_amount_vzrs);            
            this.less_ewt = parseFloat(this.total_sales) * parseFloat(this.request.withholding_tax_percentage / 100);
            this.total_amount_computed = parseFloat(this.total_sales) + parseFloat(this.less_vat) - parseFloat(this.less_ewt);
            //Formatted
            this.net_vat_formatted = this.net_vat.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            this.less_vat_formatted = this.less_vat.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            this.total_sales_formatted = this.total_sales.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            this.less_ewt_formatted = this.less_ewt.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            this.total_amount_computed_formatted = this.total_amount_computed.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

        },  
        submitManualPayment: function(){
            let url = api_url + 'finance/manual_payment';            
            this.loader_spinner = true;

            if(this.selected_payee == undefined)
            {
                Swal.fire({
                    title: "Cashier",
                    text: "Please select Name from List",
                    icon: "warning"
                }).then(function() {                    
                    return;
                });
            }
            else{                            
                Swal.fire({
                    title: 'Continue with Payment',
                    text: "Are you sure you want to add payment?",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    imageWidth: 100,
                    icon: "question",
                    cancelButtonText: "No, cancel!",
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                        preConfirm: (login) => {
                            
                            this.request.description = this.description_other;                                                            
                            this.request.subtotal_order = this.amount_to_pay;
                            this.request.total_amount_due = this.amount_to_pay;    
                            if(this.windowPayment == "invoice"){
                                type =  1;
                                this.request.or_number = null;
                            }
                            else{
                                type = 0;
                                this.request.invoice_number = null;
                            }
                            
                            
                            return axios.post(url, this.request, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                    })
                                    .then(data => {
                                        this.loader_spinner = false;
                                        if(data.data.success){
                                            var formdata= new FormData();
                                            formdata.append('intID',this.cashier.intID);
                                            formdata.append('invoice_current',this.cashier.invoice_current);
                                            formdata.append('or_current',this.cashier.or_current);
                                            axios.post(base_url + 'finance/next_or_other/'+type, formdata, {
                                            headers: {
                                                Authorization: `Bearer ${window.token}`
                                            }
                                            })
                                            .then(function(){  
                                                location.reload();                                             
                                            })                                                     
                                        }                                            
                                        else
                                            Swal.fire({
                                                title: "Failed",
                                                text: data.data.message,
                                                icon: "error"
                                            }).then(function() {
                                                location.reload();
                                            });
                                    });                                
                        },
                        allowOutsideClick: () => !Swal.isLoading()
                    }).then((result) => {
                    
                })
            }
            
         },       


    }

})
</script>