<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            <small>
                <a v-if="cashier" class="btn btn-app" :href="base_url + 'finance/view_all_students'"><i class="ion ion-arrow-left-a"></i>All Students</a>
                <a v-else class="btn btn-app" :href="base_url + 'student/view_all_students'"><i class="ion ion-arrow-left-a"></i>All Students</a>                     
                <a class="btn btn-app" :href="base_url + 'student/edit_student/' + student.intID"><i class="ion ion-edit"></i> Edit</a>                
                <a class="btn btn-app" :href="base_url + 'finance/student_ledger/' + student.intID"><i class="ion ion-edit"></i> Ledger</a>                
                <a v-if="user_level == 2 || user_level == 3" target="_blank" v-if="registration" class="btn btn-app" :href="base_url + 'pdf/student_viewer_registration_print/' + student.intID +'/'+ application_payment.student_information_id">
                    <i class="ion ion-printer"></i>RF Print
                </a>                     
                <a v-if="user_level == 2 || user_level == 3" target="_blank" v-if="registration" class="btn btn-app" :href="base_url + 'pdf/student_viewer_registration_print/' + student.intID +'/'+ application_payment.student_information_id +'/0/35'">
                    <i class="ion ion-printer"></i>RF No Header
                </a>           
            </small>
        </h1>
        <div v-if="registration" class="pull-right">
            
            <label style="font-size:.6em;"> Registration Status</label>
                
            <select v-model="registration_status" @change="changeRegStatus" class="form-control">
                <option value="0">Registered</option>
                <option value="1">Enrolled</option>
                <option value="2">Cleared</option>
            </select>
            
        </div>
        <hr />
    </section>
        <hr />
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box box-widget widget-user-2">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-red">
                        <!-- /.widget-user-image -->
                        <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}</h3>
                        <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }}  {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                    </div>
                    <div class="box-footer no-padding">
                        <ul class="nav nav-stacked">
                        <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber.replace(/-/g, '') }}</span></a></li>
                        <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue">{{ student.strName }}</span></a></li>
                        <li v-if="registration"><a style="font-size:13px;" href="#">Registration Status <span class="pull-right">{{ reg_status }}</span></a></li>
                        <li v-if="registration"><a style="font-size:13px;" href="#">Class Type <span class="pull-right">{{ registration.type_of_class }}</span></a></li>
                        <li>
                            <a style="font-size:13px;" href="#">Date Registered <span class="pull-right">
                                <span style="color:#009000" v-if="registration" >{{ registration.date_enlisted }}</span>
                                <span style="color:#900000;" v-else>N/A</span>                                
                            </a>
                        </li>
                        <li v-if="registration"><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right">{{ registration.scholarshipName }}</span></a></li>
                            
                        </ul>
                    </div>
                </div>                
            </div>
            <div class="col-sm-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_1'">
                                Personal Information
                            </a>
                        </li>
                        
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_2'">                            
                                Report of Grades
                            </a>
                        </li>
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_3'">                            
                                Assessment
                            </a>
                        </li>
                        
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + sem + '/tab_5'">                            
                                Schedule
                            </a>
                        </li>
                        <li v-if="advanced_privilages">
                            <a :href="base_url + 'unity/adjustments/' + student.intID + '/' + sem">                            
                                Adjustments
                            </a>
                        </li>
                        <li class="active"><a href="#tab_1" data-toggle="tab">Finance</a></li>
                        <!-- <li>
                            <a :href="base_url + 'unity/accounting/' + student.intID">                                
                                Accounting Summary
                            </a>
                        </li> -->
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_1">    
                            <div class="box box-solid">
                                <div class="box-header">
                                    <h4 class="box-title">ACCOUNTING</h4>                                    
                                </div>                                    
                                <div class="box-body">
                                <h4 class="box-title">Payments</h4>
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th>OR Number</th>
                                            <th>Payment Type</th>
                                            <th>Check/Credit/Debit #</th>
                                            <th>Amount Paid</th>
                                            <th>Online Payment Charge</th>
                                            <th>Total Due</th>
                                            <th>Status</th>
                                            <th>Date Updated</th>
                                            <th>Actions</th>
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
                                            <td>                                                
                                                <button v-if="!application_payment.or_number && application_payment.status == 'Paid' && cashier" data-toggle="modal"                                                
                                                        @click="prepUpdate(application_payment.id,application_payment.description,application_payment.subtotal_order)" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="application_payment.or_number && cashier"                                             
                                                        @click="printOR(application_payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                                <button v-if="application_payment.status == 'Paid' && application_payment.mode.name == 'Onsite Payment' && cashier"  class="btn btn-primary" @click="setToVoid(application_payment.id)">Void/Cancel</button>
                                            </td>
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
                                            <td>                                                
                                                <button v-if="!reservation_payment.or_number && reservation_payment.status == 'Paid' && cashier" data-toggle="modal"                                                
                                                        @click="prepUpdate(reservation_payment.id,reservation_payment.description,reservation_payment.subtotal_order)" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="reservation_payment.or_number && cashier"                                             
                                                        @click="printOR(reservation_payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                                <button v-if="reservation_payment.status == 'Paid' && reservation_payment.mode.name == 'Onsite Payment' && cashier"  class="btn btn-primary" @click="setToVoid(reservation_payment.id)">Void/Cancel</button>
                                            </td>
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
                                            <td>
                                                <button v-if="!payment.or_number && payment.status == 'Paid' && cashier" data-toggle="modal"                                                
                                                        @click="prepUpdate(payment.id,payment.description,payment.subtotal_order)" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="payment.or_number && cashier"                                             
                                                        @click="printOR(payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                                <button v-if="payment.status == 'Paid' && payment.mode.name == 'Onsite Payment' && cashier"  class="btn btn-primary" @click="setToVoid(payment.id)">Void/Cancel</button>
                                                <button v-if="payment.status == 'Pending' && payment.mode.name == 'Onsite Payment' && cashier"  class="btn btn-primary" @click="setToPaid(payment.id)">Set to paid</button>
                                                <button v-if="payment.mode.name == 'Onsite Payment' && cashier && finance_manager_privilages && payment.status == 'Paid'"  class="btn btn-danger" @click="deletePayment(payment.id)">Retract Payment</button>
                                            </td>
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
                                            <td>
                                                <button v-if="(!payment.or_number && payment.status == 'Paid') && cashier" data-toggle="modal"                                                
                                                        @click="prepUpdate(payment.id,payment.description,payment.subtotal_order)" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="payment.or_number && cashier"                                             
                                                        @click="printOR(payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                                <button v-if="payment.status == 'Paid' && payment.mode.name == 'Onsite Payment' && cashier"  class="btn btn-primary" @click="setToVoid(payment.id)">Void/Cancel</button>
                                                <button v-if="(payment.status == 'Pending' && payment.mode.name == 'Onsite Payment') && cashier" class="btn btn-primary" @click="setToPaid(payment.id)">Set to paid</button>
                                                <button v-if="(payment.mode.name == 'Onsite Payment')  && cashier && finance_manager_privilages"  class="btn btn-danger" @click="deletePayment(payment.id)">Retract Payment</button>
                                            </td>
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
                                    <hr />                                    
                                    <div v-if="cashier && cashier.or_current" class="row">
                                        <div v-html="tuition" class="col-sm-6"></div>   
                                        <div class="col-sm-6" v-if="cashier">
                                            <h3>Cashier {{ cashier.intID }}</h3>
                                            <form @submit.prevent="submitManualPayment" method="post">                                                
                                                <div class="form-group">
                                                    <label>Payment Type</label>
                                                    <select @change="selectDescription" class="form-control" v-model="description">
                                                        <option value="Tuition Full">Tuition Full</option>
                                                        <option value="Tuition Down Payment">Tuition Down Payment</option>
                                                        <option value="Tuition Partial">Tuition Partial</option>
                                                        <option value="Tuition Specific">Tuition Specific</option>
                                                        <option value="Other">Other</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label>Enter type if other is selected:</label>
                                                    <input type="text" :disabled="description != 'Other'" required class="form-control" v-model="description_other" />
                                                </div>
                                                <input type="hidden" v-model="request.status" value="Paid" />                                                
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
                                                <div class="form-group">
                                                    <label>Check/Credit/Debit Number:</label>
                                                    <input type="text" :disabled="request.is_cash == 1" required class="form-control" v-model="request.check_number" />
                                                </div>
                                                <div class="form-group">                                                                                                        
                                                    <label>Enter amount to pay:</label>
                                                    <input type="text" :disabled="description != 'Other' && description != 'Tuition Specific' && description != 'Tuition Down Payment'" required class="form-control" v-model="amount_to_pay" />
                                                </div>
                                                <div class="form-group">
                                                    <label>OR Number:</label>                                                    
                                                    <select class="form-control" v-model="request.or_number" required>
                                                        <option v-for="i in (parseInt(cashier_start), parseInt(cashier_end))" :value="i">{{ i }}</option>
                                                    </select>                                                    
                                                </div>
                                                <div class="form-group">
                                                    <label>Contact Number:</label>
                                                    {{ request.contact_number }}
                                                    <input type="hidden" required class="form-control" v-model="request.contact_number" />
                                                </div>
                                                <div class="form-group">
                                                    <label>Email: {{ request.email_address }}</label>                                                    
                                                </div>
                                                <div class="form-group">
                                                    <label>Remarks:</label>
                                                    <textarea type="text" required class="form-control" v-model="request.remarks"></textarea>
                                                </div>
                                                <button class="btn btn-primary btn-lg" :disabled="!request.or_number" type="submit">Submit Payment</button>
                                            </form>
                                            <hr />                                            
                                        </div>                                 
                                    </div>                                                                       
                                </div>
                            </div>              
                        </div>        
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form ref="print_or" method="post" :action="base_url + 'pdf/print_or'" target="_blank">
        <input type="hidden" name="student_name" v-model="or_print.student_name">
        <input type="hidden" name="cashier_id" v-model="or_print.cashier_id">
        <input type="hidden" name="student_id" v-model="or_print.student_id">
        <input type="hidden" name="student_address" v-model="or_print.student_address">
        <input type="hidden" name="is_cash" v-model="or_print.is_cash">
        <input type="hidden" name="check_number" v-model="or_print.check_number">
        <input type="hidden" name="remarks" v-model="or_print.remarks">
        <input type="hidden" name="or_number" v-model="or_print.or_number" />
        <input type="hidden" name="description" v-model="or_print.description" />
        <input type="hidden" name="total_amount_due" v-model="or_print.total_amount_due" /> 
        <input type="hidden" name="name" v-model="or_print.student_name" />       
        <input type="hidden" name="transaction_date" v-model="or_print.transaction_date" />               
    </form>
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
                        <select class="form-control" v-model="or_update.or_number" required>
                            <option v-for="i in (parseInt(cashier_start), parseInt(cashier_end))" :value="i">{{ i }}</option>
                        </select>                                     
                    </div>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" :disabled="!or_update.or_number" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
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
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',
        base_url: '<?php echo base_url(); ?>',
        slug: undefined,
        student:{},    
        cashier: undefined,     
        user_level: undefined,  
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
        },
        request:{
            first_name: '',
            slug: '',
            middle_name: '',
            last_name: '',
            contact_number: '',
            email_address: '',
            mode_of_payment_id: 26,
            description: undefined, 
            or_number:'',
            remarks:'',
            subtotal_order: 0,
            convenience_fee: 0,
            total_amount_due: 0,            
            charges: 0,
            cashier_id: undefined,
            sy_reference: '<?php echo $selected_ay; ?>',
            status: 'Paid',
            is_cash: 1,
            check_number: '',
            campus: '<?php echo $campus; ?>',
        },
        or_update_description: undefined,
        or_update:{
            id: undefined,
            or_number: undefined,
            cashier_id: undefined,
            sy_reference: undefined,
            total_amount_due: undefined,
        },
        amount_to_pay: 0,       
        cashier_start: 0,
        cashier_end: 0,     
        advanced_privilages: false,     
        finance_manager_privilages: false, 
        description: 'Tuition Full', 
        description_other: '',
        registration: {},
        other_payments:[],
        tuition:'',
        tuition_data: {},
        reservation_payment: undefined,
        application_payment: undefined,
        registration_status: 0,
        remaining_amount: 0,
        amount_paid: 0,
        amount_paid_formatted: 0,
        payments: [],
        remaining_amount_formatted: 0,
        has_partial: false,
        reg_status: undefined,        
        loader_spinner: true,                        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'unity/registration_viewer_data/' + this.id + '/' + this.sem)
                .then((data) => {  
                    if(data.data.success){      
                        this.or_update.sy_reference = this.sem;                                                                                      
                        this.registration = data.data.registration;   
                        this.user_level = data.data.user_level;
                        
                        if(this.registration){         
                            this.registration_status = data.data.registration.intROG;                            
                            this.tuition = data.data.tuition;
                            this.tuition_data = data.data.tuition_data;                                               
                            this.remaining_amount = data.data.tuition_data.total;                            
                        }

                        this.reg_status = data.data.reg_status;                        
                        this.student = data.data.student;         
                        this.or_print.student_name = this.request.strFirstname + ' ' + this.request.strLastname;
                        this.slug = this.student.slug;
                        this.request.slug = this.slug;
                        this.request.first_name = this.student.strFirstname;
                        this.request.middle_name = this.student.strMiddlename;
                        this.request.last_name = this.student.strLastname;    
                        
                        if(this.student.strMobileNumber || this.student.strMobileNumber != "")
                            this.request.contact_number = this.student.strMobileNumber;  
                        else
                            this.request.contact_number = "000000";

                        this.request.email_address = this.student.strEmail;                  
                        this.advanced_privilages = data.data.advanced_privilages; 
                        this.finance_manager_privilages = data.data.finance_manager_privilages;      
                        
                        this.cashier = data.data.cashier;

                        if(this.cashier){
                            this.cashier_start = this.cashier.or_start;
                            this.cashier_end = this.cashier.or_current?this.cashier.or_current:this.cashier.or_end;
                            this.request.or_number = this.cashier.or_current;
                            this.or_update.or_number = this.cashier.or_current;
                            this.request.cashier_id = this.cashier.user_id;
                            this.or_update.cashier_id = this.cashier.user_id;
                        }                        
                        

                        axios.get(api_url + 'finance/transactions/' + this.slug + '/' + this.sem)
                        .then((data) => {
                            this.payments = data.data.data;
                            this.other_payments = data.data.other;
                            
                            for(i in this.payments){
                                if(this.payments[i].status == "Paid"){
                                    if(this.payments[i].description == "Tuition Partial" || this.payments[i].description == "Tuition Down Payment")
                                        this.has_partial = true;
                                }
                            }

                            if(this.has_partial)
                                this.remaining_amount = this.tuition_data.total_installment;                            

                            for(i in this.payments){
                                if(this.payments[i].status == "Paid"){                              
                                    this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
                                    this.amount_paid = this.amount_paid + this.payments[i].subtotal_order;
                                }
                            }                        

                            axios.get(api_url + 'finance/reservation/' + this.slug)
                            .then((data) => {
                                this.reservation_payment = data.data.data;    
                                this.application_payment = data.data.application;
                                
                                if(this.reservation_payment.status == "Paid" && data.data.student_sy == this.sem){
                                        this.remaining_amount = this.remaining_amount - this.reservation_payment.subtotal_order;                                                                                            
                                        this.amount_paid = this.amount_paid + this.reservation_payment.subtotal_order;                                        
                                }
                                
                                this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;                                
                                this.remaining_amount_formatted = this.remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                this.amount_paid_formatted = this.amount_paid.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                                
                                this.amount_to_pay = this.remaining_amount;
                                this.loader_spinner = false;

                                
                            })
                            .catch((error) => {
                                console.log(error);
                            })
                        })
                        .catch((error) => {
                            console.log(error);
                        })      
                    }
                    else{
                        //document.location = this.base_url + 'users/login';
                    }
                                  
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {      
        prepUpdate: function(id,desc,amount){
            this.or_update.id = id;
            this.or_update_description = desc;
            this.or_update.total_amount_due = amount;
        },        
        updateOR: function(){
            let url = api_url + 'finance/update_or';
            let slug = this.slug;      
            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Continue with the update',
                text: "Are you sure you want to update the payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {                                                

                        return axios.post(url, this.or_update, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;                                    
                                    if(data.data.success){
                                        const pay_length = this.payments.length - 1;
                                        var formdata= new FormData();
                                        formdata.append('payments',this.payments.length);                                        
                                        //formdata.append('tuition_total',this.tuition_data.total_before_deductions);
                                        formdata.append('student_id',this.student.intID);                                                                                
                                        formdata.append('installment',this.tuition_data.total_installment);
                                        formdata.append('intID',this.cashier.intID);
                                        formdata.append('or_current',this.cashier.or_current);
                                        formdata.append('or_used',this.or_update.or_number);
                                        formdata.append('payments',pay_length);
                                        formdata.append('description',this.or_update_description);
                                        formdata.append('total_amount',this.or_update.total_amount_due);
                                        formdata.append('registration_id',this.registration.intRegistrationID);
                                        axios.post(base_url + 'finance/next_or', formdata, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                        })
                                        .then(function(data){
                                                if (data.data.send_notif) {                            
                                                    let url = api_url + 'registrar/send_notif_enrolled/' + slug;                                                
                                                    let payload = {'message': "This message serves as a notification that you have been officially enrolled."}
                                                    
                                                    Swal.fire({
                                                        showCancelButton: false,
                                                        showCloseButton: false,
                                                        allowEscapeKey: false,
                                                        title: 'Loading',
                                                        text: 'Updating Data do not leave page',
                                                        icon: 'info',
                                                    })
                                                    Swal.showLoading();
                                                    axios.post(url, payload, {
                                                        headers: {
                                                            Authorization: `Bearer ${window.token}`
                                                        }
                                                    })
                                                    .then(data => {
                                                        this.loader_spinner = false;                                                                                                                            
                                                        Swal.fire({
                                                            title: "Success",
                                                            text: "Update Success",
                                                            icon: "success"
                                                        }).then(function() {
                                                            location.reload();
                                                        });  
                                                    });                                
                                                }
                                                else{
                                                    Swal.fire({
                                                            title: "Success",
                                                            text: data.data.message,
                                                            icon: "success"
                                                        }).then(function() {
                                                            location.reload();
                                                        });                                                                                                                              

                                                }  
                                                    
                                            })
                                        }                                        
                                        else
                                            Swal.fire({
                                                title: "Failed",
                                                text: data.data.message,
                                                icon: "error"
                                            }).then(function() {
                                                //location.reload();
                                            });                                        
                                    });                                        
                                                                   
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                
                })

        },  
        printOR: function(payment){            
            this.or_print.or_number = payment.or_number;
            this.or_print.description = payment.description;
            this.or_print.total_amount_due = payment.subtotal_order;
            this.or_print.transaction_date = payment.updated_at;
            this.or_print.remarks = payment.remarks;
            this.or_print.student_name =  this.request.last_name+", "+this.request.first_name+", "+this.request.middle_name;    
            this.or_print.student_address = this.student.strAddress;
            this.or_print.student_id = this.student.strStudentNumber;
            this.or_print.is_cash = payment.is_cash;
            this.or_print.check_number = payment.check_number;
            this.or_print.cashier_id = payment.cashier_id;
            this.$nextTick(() => {
                this.$refs.print_or.submit();
            });             
        },
        deletePayment: function(payment_id){
            let url = api_url + 'finance/delete_payment';

            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Continue with deleting Payment',
                text: "Are you sure you want to delete payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        
                        let payload = {'id':payment_id}

                        return axios.post(url, payload, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;                                    
                                    if(data.data.success){
                                        
                                        Swal.fire({
                                            showCancelButton: false,
                                            showCloseButton: false,
                                            allowEscapeKey: false,
                                            title: 'Loading',
                                            text: 'Updating Data do not leave page',
                                            icon: 'info',
                                        })
                                        Swal.showLoading();

                                        var formdata = new FormData();
                                        formdata.append('description',data.data.description);                                        
                                        formdata.append('total_amount_due',data.data.total_amount_due);
                                        formdata.append('sy_reference',data.data.sy_reference);
                                        formdata.append('student_id',this.student.intID);
                                        formdata.append('or_number',data.data.or_number);
                                        axios.post(base_url + 'finance/remove_from_ledger', formdata, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                        })
                                        .then(function(data){                                              
                                            Swal.fire({
                                                title: "Success",
                                                text: data.data.message,
                                                icon: "success"
                                            }).then(function() {
                                                location.reload();
                                            }); 
                                        })
                                    }
                                    else
                                        Swal.fire({
                                            title: "Failed",
                                            text: data.data.message,
                                            icon: "error"
                                        }).then(function() {
                                            //location.reload();
                                        });
                                });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                
                })

        },
        setToVoid: function(payment_id){    
            let url = api_url + 'finance/set_void';

            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Continue with processing Payment',
                text: "Are you sure you want to process payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        
                        let payload = {'id':payment_id}

                        return axios.post(url, payload, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;
                                    if(data.data.success)
                                        Swal.fire({
                                            title: "Success",
                                            text: data.data.message,
                                            icon: "success"
                                        }).then(function() {
                                            location.reload();
                                        });
                                    else
                                        Swal.fire({
                                            title: "Failed",
                                            text: data.data.message,
                                            icon: "error"
                                        }).then(function() {
                                            //location.reload();
                                        });
                                });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                
                })

        },
        setToPaid: function(payment_id){    
            let url = api_url + 'finance/set_paid';

            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Continue with processing Payment',
                text: "Are you sure you want to process payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        
                        let payload = {'id':payment_id}

                        return axios.post(url, payload, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;
                                    if(data.data.success)
                                        Swal.fire({
                                            title: "Success",
                                            text: data.data.message,
                                            icon: "success"
                                        }).then(function() {
                                            location.reload();
                                        });
                                    else
                                        Swal.fire({
                                            title: "Failed",
                                            text: data.data.message,
                                            icon: "error"
                                        }).then(function() {
                                            //location.reload();
                                        });
                                });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                
                })

        },
        submitManualPayment: function(){
            let url = api_url + 'finance/manual_payment';  
            let slug = this.slug;          
            this.loader_spinner = true;
            
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
                        
                        if(this.description == 'Other')
                            this.request.description = this.description_other;
                        else
                            this.request.description = this.description;

                        this.request.subtotal_order = this.amount_to_pay;
                        this.request.total_amount_due = this.amount_to_pay;
                        console.log(this.request);
                        
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
                                        formdata.append('or_current',this.cashier.or_current);  
                                        formdata.append('or_used',this.request.or_number);                                      
                                        formdata.append('payments',this.payments.length);
                                        formdata.append('total_amount',this.request.total_amount_due);
                                        //formdata.append('tuition_total',this.tuition_data.total_before_deductions);
                                        formdata.append('student_id',this.student.intID);
                                        formdata.append('description',this.request.description);
                                        formdata.append('registration_id',this.registration.intRegistrationID);
                                        formdata.append('installment',this.tuition_data.total_installment);
                                        axios.post(base_url + 'finance/next_or', formdata, {
                                        headers: {
                                            Authorization: `Bearer ${window.token}`
                                        }
                                        })
                                        .then(function(data){
                                                if (data.data.send_notif) {                            
                                                    let url = api_url + 'registrar/send_notif_enrolled/' + slug;                                                
                                                    let payload = {'message': "This message serves as a notification that you have been officially enrolled."}
                                                    
                                                    Swal.fire({
                                                        showCancelButton: false,
                                                        showCloseButton: false,
                                                        allowEscapeKey: false,
                                                        title: 'Loading',
                                                        text: 'Processing Data do not leave page',
                                                        icon: 'info',
                                                    })
                                                    Swal.showLoading();
                                                    axios.post(url, payload, {
                                                        headers: {
                                                            Authorization: `Bearer ${window.token}`
                                                        }
                                                    })
                                                    .then(data => {
                                                        this.loader_spinner = false;                                                                                                                            
                                                        Swal.fire({
                                                            title: "Success",
                                                            text: data.data.message,
                                                            icon: "success"
                                                        }).then(function() {
                                                            location.reload();
                                                        });  
                                                    });                                
                                                }
                                                else{
                                                    Swal.fire({
                                                            title: "Success",
                                                            text: data.data.message,
                                                            icon: "success"
                                                        }).then(function() {
                                                            location.reload();
                                                        });                                                                                                                              

                                                }  
                                                    
                                            })
                                        }                                        
                                        else
                                            Swal.fire({
                                                title: "Failed",
                                                text: data.data.message,
                                                icon: "error"
                                            }).then(function() {
                                                //location.reload();
                                            });                                        
                                    });                             
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                
                })
            
        },
        selectDescription: function(){
            if(this.description != 'Other'){                
                this.request.description = this.description;
                switch(this.description){
                    case 'Tuition Full':
                        this.amount_to_pay = this.remaining_amount;
                    break;
                    case 'Tuition Partial':
                        this.amount_to_pay = (this.tuition_data.installment_fee > this.remaining_amount) ? this.remaining_amount : this.tuition_data.installment_fee;
                    break;
                    case 'Tuition Down Payment':                        
                        this.amount_to_pay = (this.tuition_data.down_payment <= this.amount_paid) ? 0 : ( this.tuition_data.down_payment - this.amount_paid );
                    break;                    
                }
            }
            else{                                
                this.amount_to_pay = 0;
            }
        },
        changeRegStatus: function(){
            let url = this.base_url + 'unity/update_rog_status';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("intROG",this.registration_status);
            var missing_fields = false;
            this.loader_spinner = true;
            
            //validate description
                      
            axios.post(url, formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                if(data.data.success)
                    Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                        location.reload();
                    });
                else
                    Swal.fire({
                        title: "Failed",
                        text: data.data.message,
                        icon: "error"
                    }).then(function() {
                        //location.reload();
                    });
            });
           
            
            
        }
    }

})
</script>

