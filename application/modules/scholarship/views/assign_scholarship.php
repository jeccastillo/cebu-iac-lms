<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Assign Scholarship/Discount                                 
        </h1>
        <ol class="breadcrumb">
            <li><a :href="base_url + 'scholarship/select_student'"><i class="fa fa-dashboard"></i>Select Student</a></li>
            <li class="active">Assign Scholarship</li>
        </ol>     
    </section>
        <hr />
    <div class="content"> 
        <div class="pull-right">
            <select @change="changeTerm($event)" class="form-control" v-model="current_sem">
                <option v-for="term in terms" :value="term.intID">{{ term.enumSem }} Term SY {{ term.strYearStart }} - {{ term.strYearEnd }}</option>
            </select>
        </div>
        <hr />
        <div class="box box-default">
            <div class="box-header">
                <h3>Scholarship</h3>
            </div>
            <div class="box-body"> 
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">                       
                        <li class="active"><a href="#tab_1" data-toggle="tab">Scholarship</a></li>
                        <li><a href="#tab_2" data-toggle="tab">Payments</a></li>                        
                    </ul>           
                    <div class="tab-content">            
                        <div class="tab-pane" id="tab_1">
                            <div class="row">
                                <div v-if="student" class="col-md-6">
                                    Name: {{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}
                                </div>
                            </div>
                            <hr />                
                            <div class="row">                                        
                                <div class="col-md-6">                            
                                    <h4>Assign Scholarship</h4>
                                    <form method="post" action="#" @submit.prevent.stop="submitDeduction('scholarship')">
                                        <label>Select Scholarship</label>
                                        <select required class="form-control" v-model="request_scholarship.discount_id">
                                            <option v-for="scholarship in scholarships" :value="scholarship.intID">{{ scholarship.name }}</option>
                                        </select>                            
                                        
                                        <hr />
                                        <input class="btn btn-primary" type="submit" value="Add">
                                    </form> 
                                </div>                                                                               
                                <div class="col-md-6">
                                    <h4>Assign Discount</h4>
                                    <form method="post" action="#" @submit.prevent.stop="submitDeduction('discount')">        
                                        <label>Select Discount</label>
                                        <select required class="form-control" v-model="request_discount.discount_id">
                                            <option v-for="discount in discounts" :value="discount.intID">{{ discount.name }}</option>
                                        </select>                            
                                        <hr />    
                                    
                                        <label>Referree Name</label>
                                        <input type="text" class="form-control" v-model="request_discount.referrer" />                                
                                        
                                        <hr />
                                        <input class="btn btn-primary" type="submit" value="Add">
                                    </form>
                                </div>                                                                
                            </div>
                            <div>
                                <h4>Assigned Scholarships for this Term</h4>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Scholarship</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in student_scholarships">
                                            <td>{{ item.name }}</td>
                                            <td>
                                                <select class="form-control" @change="updateScStatus($event,item.id)">
                                                    <option :selected="item.status == 'applied'" value="applied">applied</option>
                                                    <option :selected="item.status == 'pending'" value="pending">pending</option>
                                                    <option :selected="item.status == 'withdraw'" value="withdraw">withdraw</option>
                                                </select>
                                            </td>
                                            <td><button @click.prevent.stop="deleteScholarship(item.id)" class="btn btn-danger">Delete</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div>
                                <h4>Assigned Discounts for this Term</h4>
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Discount</th>
                                            <th>Referree</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="item in student_discounts">
                                            <td>{{ item.name }}</td>
                                            <td>{{ item.referrer }}</td>
                                            <td>
                                                <select class="form-control" @change="updateScStatus($event,item.id)">
                                                    <option :selected="item.status == 'applied'" value="applied">applied</option>
                                                    <option :selected="item.status == 'pending'" value="pending">pending</option>
                                                    <option :selected="item.status == 'withdraw'" value="withdraw">withdraw</option>
                                                </select>
                                            </td>
                                            <td><button @click.prevent.stop="deleteScholarship(item.id)" class="btn btn-danger">Delete</button></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>  
                        <div class="tab-pane" id="tab_2">  
                            <h4>Details</h4>                                   
                            <table class="table table-bordered table-striped">
                                <tr>
                                    <th>OR Number</th>
                                    <th>Payment Type</th>
                                    <th>Reference No.</th>
                                    <th>Amount Paid</th>
                                    <th>Online Payment Charge</th>
                                    <th>Total Due</th>
                                    <th>Status</th>
                                    <th>Date Updated</th>                                        
                                </tr>     
                                <tr v-if="application_payment">
                                    <td>{{ application_payment.or_number }}</td>
                                    <td>{{ application_payment.description }}</td>
                                    <td>{{ application_payment.check_number }}</td>
                                    <td>{{ application_payment.subtotal_order }}</td>
                                    <td>{{ application_payment.charges }}</td>
                                    <td>{{ application_payment.total_amount_due }}</td>
                                    <td>{{ application_payment.status }}</td>                                            
                                    <td>{{ application_payment.updated_at }}</td>
                                    
                                </tr>
                                <tr v-if="reservation_payment">
                                    <td>{{ reservation_payment.or_number }}</td>
                                    <td>{{ reservation_payment.description }}</td>
                                    <td>{{ reservation_payment.check_number }}</td>
                                    <td>{{ reservation_payment.subtotal_order }}</td>
                                    <td>{{ reservation_payment.charges }}</td>
                                    <td>{{ reservation_payment.total_amount_due }}</td>
                                    <td>{{ reservation_payment.status }}</td>                                            
                                    <td>{{ reservation_payment.updated_at }}</td>
                                    
                                </tr>
                                <tr>
                                    <th colspan="8">
                                    Other Payments:
                                    </th>
                                </tr>  
                                <tr v-for="payment in other_payments">
                                    <td>{{ payment.or_number }}</td>
                                    <td>{{ payment.description }}</td>
                                    <td>{{ payment.check_number }}</td>
                                    <td>{{ payment.subtotal_order }}</td>
                                    <td>{{ payment.charges }}</td>
                                    <td>{{ payment.total_amount_due }}</td>
                                    <td>{{ payment.status }}</td>                                            
                                    <td>{{ payment.updated_at }}</td>
                                    
                                </tr>    
                                <tr>
                                    <th colspan="8">
                                    Tuition Payments:
                                    </th>
                                </tr>
                                <tr v-for="payment in payments">
                                    <td>{{ payment.or_number }}</td>
                                    <td>{{ payment.description }}</td>
                                    <td>{{ payment.check_number }}</td>
                                    <td>{{ payment.subtotal_order }}</td>
                                    <td>{{ payment.charges }}</td>
                                    <td>{{ payment.total_amount_due }}</td>
                                    <td>{{ payment.status }}</td>                                            
                                    <td>{{ payment.updated_at }}</td>                                        
                                </tr>                                                                           
                                <tr>
                                    <td class="text-green" colspan="8">
                                    amount paid: P{{ amount_paid_formatted }}                                           
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-green" colspan="8">                                            
                                    remaining balance: P{{ remaining_amount_formatted }}
                                    </td>
                                </tr>
                            </table>  
                            <div v-html="tuition" class="col-sm-6"></div>                                                                                                                                                                                            
                        </div>  
                    </div>
                </div>
            </div>
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
        current_sem: '<?php echo $sem; ?>',
        student_id: <?php echo $student; ?>,
        scholarships:[],
        discounts:[],
        terms:[],
        student_scholarships:[],   
        registration: undefined, 
        tuition: undefined,
        tuition_data: undefined,
        payment_type: undefined,
        remaining_amount: 0,
        payments: [],
        other_payments: [],
        student_discounts:[],    
        reservation_payment: undefined,
        application_payment: undefined,
        amount_paid_formatted: 0,
        remaining_amount_formatted:0,
        student: undefined,    
        request_scholarship:{
            discount_id: undefined,
            student_id: <?php echo $student; ?>,
            syid: undefined,
            referrer: 'none',
        },
        request_discount:{
            discount_id: undefined,
            student_id: <?php echo $student; ?>,
            syid: undefined,
            referrer: undefined,
        }
                      
    },

    mounted() {

        axios.get(this.base_url + 'scholarship/assign_scholarship_data/'+this.student_id+'/'+this.current_sem)
                .then((data) => {        
                    this.scholarships = data.data.scholarships;
                    this.discounts = data.data.discounts;
                    this.terms = data.data.terms;
                    this.student_scholarships = data.data.student_scholarships;
                    this.student_discounts = data.data.student_discounts;
                    this.student = data.data.student;
                    this.request_discount.syid = this.current_sem;
                    
                    if(data.data.registration){         
                        this.registration = data.data.registration;                                                   
                        this.tuition = data.data.tuition;
                        this.tuition_data = data.data.tuition_data;                                               
                        this.payment_type = this.registration.paymentType;
                        this.remaining_amount = data.data.tuition_data.total;                         
                    }

                    this.request_scholarship.syid = this.current_sem;
                        axios.get(api_url + 'finance/transactions/' + this.student.slug + '/' + this.current_sem)
                        .then((data) => {
                            this.payments = data.data.data;
                            this.other_payments = data.data.other;
                                                                
                            if(this.registration && this.registration.paymentType == 'partial')
                                this.has_partial = true;
                                                                                    

                            if(this.has_partial)
                                this.remaining_amount = this.tuition_data.total_installment;                            

                            for(i in this.payments){
                                if(this.payments[i].status == "Paid"){     
                                    this.payments_paid.push(this.payments[i]);                         
                                    this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
                                    this.amount_paid = this.amount_paid + this.payments[i].subtotal_order;
                                }                                
                            }                        

                            axios.get(api_url + 'finance/reservation/' + this.student.slug)
                            .then((data) => {
                                this.reservation_payment = data.data.data;    
                                this.application_payment = data.data.application;
                                
                                if(this.reservation_payment.status == "Paid" && data.data.student_sy == this.current_sem){
                                        this.remaining_amount = this.remaining_amount - this.reservation_payment.subtotal_order;                                                                                                                                    
                                        this.amount_paid = this.amount_paid + this.reservation_payment.subtotal_order;      
                                        this.tuition_data.down_payment =  this.tuition_data.down_payment - this.reservation_payment.subtotal_order;
                                }

                                
                                
                                this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;                                
                                this.remaining_amount_formatted = this.remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                //installment amounts                                
                                if(this.registration.downpayment == 1){
                                    var temp = (this.tuition_data.installment_fee * 5) - parseFloat(this.remaining_amount);
                                    for(i=0; i < 5; i++){
                                        if(this.tuition_data.installment_fee > temp){
                                            val = this.tuition_data.installment_fee - temp;
                                            val = val.toFixed(2);
                                            this.installments.push(val);
                                            temp = 0;
                                        }
                                        else{
                                            this.installments.push(0);
                                            temp = temp - this.tuition_data.installment_fee;
                                        }
                                    
                                    }
                                }
                                else
                                    for(i=0; i < 5; i++)
                                        this.installments.push(this.tuition_data.installment_fee);                                                                                                                  
                                    
                                    
                                
                                
                                
                                var val = 0;                                
                                

                                this.amount_paid_formatted = this.amount_paid.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                                                                
                                this.loader_spinner = false;
                                if(this.remaining_amount <= 0)
                                    this.description = "Other";

                                
                            })
                            .catch((error) => {
                                console.log(error);
                            })
                        })
                        .catch((error) => {
                            console.log(error);
                        })  
               
            })
            .catch((error) => {
                console.log(error);
            });
                        

    },

    methods: {      
        changeTerm: function(event){
             document.location = base_url + 'scholarship/assign_scholarship/' + event.target.value;
        },
        updateScStatus: function(event,id){

            this.loader_spinner = true;
            var formdata= new FormData(); 
            formdata.append('id',id); 
            formdata.append('status',event.target.value); 

            axios.post(base_url + 'scholarship/update_scholarship_status', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                Swal.fire({
                    title: data.data.success,
                    text: data.data.message,
                    icon: data.data.success,
                }).then(function() {
                    this.loader_spinner = false;
                });
            });

        },
        submitDeduction: function(type){
            var req = {};
            var formdata= new FormData();            
            if(type == "scholarship")
                req = this.request_scholarship;
            else
                req = this.request_discount;
            
            for (const [key, value] of Object.entries(req)) {
                formdata.append(key,value);
            }
            
            console.log(req);                
            this.loader_spinner = true;
            axios.post(base_url + 'scholarship/add_scholarship', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                Swal.fire({
                    title: data.data.success,
                    text: data.data.message,
                    icon: data.data.success,
                }).then(function() {
                    location.reload();
                });
            });
        },
        deleteScholarship: function(id){
            
            var formdata= new FormData();  
            formdata.append('id',id);    

            this.loader_spinner = true;
            axios.post(base_url + 'scholarship/delete_scholarship', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                Swal.fire({
                    title: data.data.success,
                    text: data.data.message,
                    icon: data.data.success,
                }).then(function() {
                    location.reload();
                });
            });
        },                                
    }

})

$(document).ready(function(){     
});
</script>

