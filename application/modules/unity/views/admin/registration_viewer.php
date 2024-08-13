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
    </section>
        <hr />
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box box-widget widget-user-2">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-red">                        
                        <div class="pull-right" style="margin-left:1rem;">
                            Tuition Year
                            <select class="form-control" @change="selectTuitionYear($event)" v-model="tuition_year">
                                <option v-for="ty in tuition_years" :value="ty.intID">{{ ty.year}}</option>
                            </select>
                        </div>
                        <div class="pull-right" style="margin-left:1rem;">
                            School Year & Term
                            <select class="form-control" @change="selectTerm($event)" v-model="sem">
                                <option v-for="s in sy" :value="s.intID">{{ s.term_student_type}} {{ s.enumSem }} {{ s.term_label }} {{ s.strYearStart }} - {{ s.strYearEnd }}</option>
                            </select>
                        </div>
                        <!-- /.widget-user-image -->
                        
                        <div v-if="registration && user.special_role >= 1" style="margin-right:1rem;" class="pull-right">                                                                         
                            Payment Type
                            <select v-model="change_payment_type" @change="changeType($event)" class="form-control">                                
                                <option value="full">Full Payment</option>
                                <option value="partial">Installment</option>                                
                            </select>
                            
                        </div>
                        <div v-if="registration && user.special_role >= 1" style="margin-right:1rem;" class="pull-right">                                                                         
                            Enrollment Status
                            <select v-if="registration_status!=1" v-model="registration_status" @change="changeRegStatus" class="form-control">
                                <option value="0">Enlisted</option>
                                <option value="1">Enrolled</option>                                
                            </select>
                            <div v-else>
                                Enrolled
                            </div>
                        </div>
                        <div v-if="registration && user.special_role > 1" style="margin-right:1rem;" class="pull-right">                                                                         
                            Allow To Print RF
                            <select v-model="allow_enroll" @change="changeAllowEnroll" class="form-control">
                                <option value="0">No</option>
                                <option value="1">Yes</option>                                
                            </select>
                            
                        </div>
                        <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}
                            &nbsp;<button class="btn btn-default" data-toggle="collapse" data-target="#student-info">More Info</button>                        
                        </h3>
                        <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }}  {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                    </div>
                    <div class="collapse" class="box-footer no-padding" id="student-info">
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
                <div class=""
                    v-if="show_alert">
                    <div class="alert alert-danger col-sm-6"
                    role="alert">
                    <h4 class="alert-heading">Alert!</h4>
                    <p>This Student still has remaining balances:</p>
                    </div>
                    <div class="col-sm-6">
                    <table class="table table-bordered thead-dark table-striped">
                        <thead>
                        <tr>
                            <th>Term</th>
                            <th>Payment Type</th>
                            <th>Balance</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-for="item in term_balances"
                            v-if="item.balance > 0">
                            <td>{{ item.term }}</td>
                            <td>{{ item.payment_type }}</td>
                            <td><strong>P{{ item.formatted_balance }}</strong></td>
                        </tr>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>            
            <div class="col-sm-12">
                <div v-if="applicant_data.reserve_enroll" class="alert alert-success" role="alert">                    
                    <h4 class="alert-heading">Reserve Enroll</h4>
                    <p>This student has been tagged for reserve enrollment promo please update tuition year if it hasn't been updated</p>                                
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
                        <li v-if="cashier" :class="cashier?'active':''"><a href="#tab_1" data-toggle="tab">Payment</a></li>
                        <li :class="!cashier?'active':''"><a href="#tab_2" data-toggle="tab">Details</a></li>
                        <li :class="!cashier?'active':''"><a href="#tab_3" data-toggle="tab">SOA</a></li>
                        <!-- <li>
                            <a :href="base_url + 'unity/accounting/' + student.intID">                                
                                Accounting Summary
                            </a>
                        </li> -->
                    </ul>                                        
                    <div class="tab-content">
                        <div :class="cashier?'active tab-pane':'tab-pane'" id="tab_1">    
                            <div v-if="registration_status" class="box box-solid">
                                <div class="box-header">
                                    <h4 class="box-title">Payment</h4>                                    
                                </div>                                    
                                <div class="box-body">
                                <h4 class="box-title">Cashier {{ cashier.intID }}</h4>                                   
                                    <form @submit.prevent="submitManualPayment" method="post">                                    
                                        <div v-if="cashier && cashier.or_current" class="row">                                                                                   
                                            <div class="col-sm-4" v-if="cashier">                                                                                                                                        
                                                    <div class="form-group">
                                                        <label>Payment For</label>
                                                        <select class="form-control" v-model="description">
                                                            <option value="Tuition Fee">Tuition Fee</option>                                                            
                                                            <option value="Other">Other</option>
                                                        </select>
                                                    </div>
                                                    <div v-if="description == 'Tuition Fee'" class="form-group">
                                                        <label>Particulars:</label>
                                                        <select required class="form-control" v-model="description_other">
                                                            <option value="full">Full Tuition</option>                                                            
                                                            <option value="down">Down Payment</option>
                                                            <option value="installment">Installment</option>                                                            
                                                        </select>
                                                    </div>
                                                    <div v-else class="form-group">
                                                        <label>Particulars:</label>
                                                        <select class="form-control" v-model="description_other">
                                                            <option v-for="p in particulars" :value="p.name">{{p.name}}</option>
                                                        </select>
                                                        <!-- <input type="text" required class="form-control" v-model="description_other" /> -->
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
                                                        <label>Reference No.:</label>
                                                        <input type="text" :disabled="request.is_cash == 1" required class="form-control" v-model="request.check_number" />
                                                    </div>
                                                    <div class="form-group">                                                                                                        
                                                        <label>Enter amount to pay:</label>
                                                        <input type="number" step="0.01" required class="form-control" v-model="amount_to_pay" />
                                                    </div>
                                                </div>
                                            <div class="col-sm-4" v-if="cashier">
                                                <div class="form-group">
                                                    <label>OR Number:</label>                                                    
                                                    <input type="hidden" class="form-control" v-model="request.or_number">
                                                    {{ request.or_number }}
                                                    <!-- <select class="form-control" v-model="request.or_number" required>
                                                        <option v-for="i in (parseInt(cashier_start), parseInt(cashier_end))" :value="i">{{ i }}</option>
                                                    </select>                                                     -->
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
                                            </div>
                                            <div v-if="description == 'Tuition Fee'" class="col-sm-4" v-if="cashier">
                                                <label>Select Type:</label>  
                                                <select v-if="registration.downpayment == 0 && registration.fullpayment == 0" @change="description_other = ''; amount_to_pay = 0 " v-model="payment_type" class="form-control">
                                                    <option value="full">Full Payment</option>
                                                    <option value="partial">Installment</option>
                                                </select>
                                                <div v-else>
                                                    {{ payment_type }}
                                                </div>
                                                <hr />
                                                <table class="table table-striped" v-if="payment_type == 'full'">
                                                    <tr>
                                                        <td>Full Tuition</td>
                                                        <td><a href="#" @click="setValue(remaining_amount,'full')">{{ remaining_amount }}</a></td>
                                                    </tr> 
                                                </table>
                                                <table class="table table-striped" v-else>
                                                    <tr>
                                                        <td>Down Payment</td>
                                                        <td v-if="registration.downpayment == 0"><a href="#" @click="setValue(tuition_data.down_payment,'down',0)">{{ tuition_data.down_payment }}</a></td>                                                        
                                                    </tr> 
                                                    <tr v-for="(inst,ctr) in installments">
                                                        <td>Installment{{ '(' + installment_dates[ctr]+ ')' }}</td>
                                                        <td><a href="#" @click="setValue(inst,'installment',ctr)">{{ inst }}</a></td>
                                                    </tr>
                                                </table>                                                
                                            </div>                                                                             
                                        </div> 
                                        <hr />
                                        
                                        <button class="btn btn-primary btn-lg pull-right" :disabled="!request.or_number" type="submit">Submit Payment</button>                                                
                                                                                                                      
                                    </form>
                                </div>
                            </div>              
                        </div>   
                        <div :class="!cashier?'active tab-pane':'tab-pane'" id="tab_2">    
                            <div class="box box-solid">
                                <div class="box-header">
                                    <h4 class="box-title">DETAILS</h4>                                    
                                </div>                                    
                                <div class="box-body">   
                                    <table v-if="ledger_items.length > 0" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th colspan="9">Manually Added Ledger Items</th>
                                            </tr> 
                                            <tr>                                                                                              
                                                <th>Payment Description</th>
                                                <th>Date Added</th>                                                
                                                <th>Remarks</th>
                                                <th>Assessment</th>
                                                <th>Payment</th>                                                                                                 
                                                <th>Added By</th>                                                 
                                            </tr>
                                        </thead>
                                        <tbody>                                                         
                                            <tr v-for="item in ledger_items">                                                                                
                                                <td :class="item.muted">{{ item.name }}</td>
                                                <td :class="item.muted">{{  item.date }}</td>                                                
                                                <td :class="item.muted">{{  item.remarks }}</td>
                                                <td :class="item.muted">{{ (item.type != 'payment')?numberWithCommas(item.amount):'-' }}</td>
                                                <td :class="item.muted">{{ (item.type == 'payment')?numberWithCommas(item.amount):'-' }}</td>                                                                                                                                                
                                                <td :class="item.muted"><a @click="cashierDetails(item.added_by)" href="#">{{ item.added_by }}</a></td>                                                                                                
                                            </tr>                                                                                                           
                                        </tbody>                
                                    </table>                                 
                                    <table class="table table-bordered table-striped">
                                        <tr>
                                            <th></th>
                                            <th>OR Number</th>                                            
                                            <th>Payment Type</th>
                                            <th>Reference No.</th>
                                            <th>Amount Paid</th>
                                            <th>Online Payment Charge</th>
                                            <th>Total Due</th>
                                            <th>Status</th>
                                            <th>Date Updated</th>
                                            <th>Info</th>
                                            <th>Actions</th>
                                        </tr>                                                                                    
                                        <tr>
                                            <th colspan="11">
                                            Other Payments:
                                            </th>
                                        </tr>  
                                        <tr v-if="application_payment">
                                            <td></td>
                                            <td>{{ application_payment.or_number }}</td>
                                            <td>{{ application_payment.description }}</td>
                                            <td>{{ application_payment.check_number }}</td>
                                            <td>{{ application_payment.subtotal_order }}</td>
                                            <td>{{ application_payment.charges }}</td>
                                            <td>{{ application_payment.total_amount_due }}</td>
                                            <td>{{ application_payment.status }}</td>                                            
                                            <td>{{ application_payment.or_date }}</td>
                                            <td>{{ application_payment.void_reason }}</td>
                                            <td>                                                
                                                <button v-if="!application_payment.or_number && application_payment.status == 'Paid' && cashier && application_payment.remarks != 'Voided'" data-toggle="modal"                                                
                                                        @click="prepUpdate(application_payment.id,application_payment.description,application_payment.subtotal_order)" 
                                                        data-target="#myModal" class="btn btn-primary">
                                                        Update OR
                                                </button>
                                                <button v-if="application_payment.or_number && cashier"                                             
                                                        @click="printOR(application_payment)" 
                                                        class="btn btn-primary">
                                                        Print OR
                                                </button>
                                                <button v-if="application_payment.status == 'Paid' && application_payment.remarks != 'Voided' && cashier && finance_manager_privilages" data-toggle="modal" data-target="#voidPaymentModal" class="btn btn-primary" @click="setToVoid(application_payment.id)">Void/Cancel</button>
                                            </td>
                                        </tr>                                        
                                        <tr v-for="payment in other_payments">
                                            <td><input v-if="user.special_role > 1" type="checkbox" :value="payment.or_number" v-model="selected_items" /></td>
                                            <td>{{ payment.or_number }}</td>
                                            <td>{{ payment.description }}</td>
                                            <td>{{ payment.check_number }}</td>
                                            <td>{{ payment.subtotal_order }}</td>
                                            <td>{{ payment.charges }}</td>
                                            <td>{{ payment.total_amount_due }}</td>
                                            <td>{{ payment.status }}</td>                                            
                                            <td>{{ payment.or_date }}</td>
                                            <td>{{ payment.void_reason }}</td>
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
                                                <button v-if="payment.status == 'Paid' && payment.remarks != 'Voided' && cashier && finance_manager_privilages"  class="btn btn-primary" data-toggle="modal" data-target="#voidPaymentModal" @click="setToVoid(payment.id)">Void/Cancel</button>
                                                <button v-if="payment.status == 'Pending' && payment.mode.name == 'Onsite Payment' && cashier"  class="btn btn-primary" @click="setToPaid(payment.id)">Set to paid</button>
                                                <button v-if="cashier && finance_manager_privilages && payment.status == 'Paid' &&  payment.mode.name == 'Onsite Payment' "  class="btn btn-danger" @click="deletePayment(payment.id)">Retract Payment</button>
                                            </td>
                                        </tr>    
                                        <tr>
                                            <th colspan="11">
                                            Tuition Payments:
                                            </th>
                                        </tr>
                                        <tr v-if="reservation_payments" v-for="reservation_payment in reservation_payments">
                                            <td></td>
                                            <td>{{ reservation_payment.or_number }}</td>
                                            <td>{{ reservation_payment.description }}</td>
                                            <td>{{ reservation_payment.check_number }}</td>
                                            <td>{{ reservation_payment.subtotal_order }}</td>
                                            <td>{{ reservation_payment.charges }}</td>
                                            <td>{{ reservation_payment.total_amount_due }}</td>
                                            <td>{{ reservation_payment.status }}</td>                                            
                                            <td>{{ reservation_payment.or_date }}</td>
                                            <td>{{ reservation_payment.void_reason }}</td>
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
                                                <button v-if="reservation_payment.status == 'Paid' && reservation_payment.remarks != 'Voided' && cashier && finance_manager_privilages" data-toggle="modal" data-target="#voidPaymentModal"  class="btn btn-primary" @click="setToVoid(reservation_payment.id)">Void/Cancel</button>
                                            </td>
                                        </tr> 
                                        <tr v-for="payment in payments">                                            
                                            <td><input v-if="user.special_role > 1" type="checkbox" :value="payment.or_number" v-model="selected_items" /></td>
                                            <td>{{ payment.or_number }}</td>
                                            <td>{{ payment.description }}</td>
                                            <td>{{ payment.check_number }}</td>
                                            <td>{{ payment.subtotal_order }}</td>
                                            <td>{{ payment.charges }}</td>
                                            <td>{{ payment.total_amount_due }}</td>
                                            <td>{{ payment.status }}</td>                                            
                                            <td>{{ payment.or_date }}</td>
                                            <td>{{ payment.void_reason }}</td>
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
                                                <button v-if="payment.mode && payment.status == 'Paid' && payment.remarks != 'Voided' && cashier && finance_manager_privilages" data-toggle="modal" data-target="#voidPaymentModal"  class="btn btn-primary" @click="setToVoid(payment.id)">Void/Cancel</button>
                                                <button v-if="(payment.mode && payment.status == 'Pending' && payment.mode.name == 'Onsite Payment') && cashier" class="btn btn-primary" @click="setToPaid(payment.id)">Set to paid</button>
                                                <button v-if="(payment.mode && payment.mode.name == 'Onsite Payment')  && cashier && finance_manager_privilages"  class="btn btn-danger" @click="deletePayment(payment.id)">Retract Payment</button>
                                            </td>
                                        </tr>  
                                        <tr v-if="user.special_role > 1">
                                            <td class="text-right" colspan="2">
                                                Do with selected: 
                                            </td>
                                            <td colspan="3">
                                                <select class="form-control"  v-model="switch_term">
                                                    <option v-for="s in sy" :value="s.intID">{{ s.term_student_type}} {{ s.enumSem }} {{ s.term_label }} {{ s.strYearStart }} - {{ s.strYearEnd }}</option>
                                                </select>
                                            </td>
                                            <td colspan="6">
                                                <button @click="forwardSelected" class="btn btn-primary">
                                                            Forward Selected
                                                </button>
                                            </td>
                                        </tr>                                                                         
                                        <tr>
                                            <td class="text-green" colspan="10">
                                            amount paid: P{{ amount_paid_formatted }}                                           
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-green" colspan="10">                                            
                                            remaining balance: P{{ remaining_amount_formatted }}
                                            </td>
                                        </tr>
                                    </table>  
                                    <div v-html="tuition" class="col-sm-6"></div>                                                                                                                                        
                                </div>
                            </div>              
                        </div>  
                        <div class="tab-pane" id="tab_3">
                            <h3>Statment of Account</h3>
                            <!-- <img :src="soa.logo" height="300px" width="300px"/> -->
                            <div class="text-center">
                                <h3>Information & Communications Technology Academy</h3>
                                <h4>{{ soa.address }}</h4>
                                <h4>AY {{ current_term.strYearStart }} - {{ current_term.strYearEnd }} {{ current_term.enumSem }} {{ current_term_full_label }}</h4>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <table class="table table-bordered">
                                        <tr v-if="registration.downpayment == 0">
                                            <td>Down Payment</td>
                                            <td>{{ tuition_data.down_payment }}</td>                                                        
                                        </tr> 
                                        <tr v-for="(inst,ctr) in installments" v-if="inst > 0">
                                            <td>{{ addSuffix(ctr + 1) + ' installment due ' + installment_dates[ctr]+ ' ' }}</td>
                                            <td>P{{ inst }}</td>
                                        </tr>
                                        <tr>
                                            <td>TOTAL BALANCE</td>
                                            <td>P{{ remaining_amount_formatted }}</td>
                                        </tr>
                                    </table> 
                                </div>                                 
                            </div>                            
                        </div>    
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form ref="print_or" method="post" :action="base_url + 'pdf/print_or_new'" target="_blank">
        <input type="hidden" name="student_name" v-model="or_print.student_name">
        <input type="hidden" name="campus" :value="request.student_campus">
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
        <input type="hidden" name="sem" v-model="or_print.sem" />       
        <input type="hidden" name="transaction_date" v-model="or_print.transaction_date" /> 
        <input type="hidden" name="type" v-model="or_print.type" />              
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
    <div class="modal fade" id="voidPaymentModal" role="dialog">
        <form @submit.prevent="voidPayment" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Set Payment to Void</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">                        
                        <textarea class="form-control" v-model="void_reason" required></textarea>                                     
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
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}        
new Vue({
    el: '#registration-container',
    data: {
        id: '<?php echo $id; ?>',    
        sem: '<?php echo $selected_ay; ?>',
        base_url: '<?php echo base_url(); ?>',
        selected_items: [],
        applicant_data: {
            reserve_enroll: 0,
        },
        slug: undefined,
        switch_term: undefined,
        student:{
            strStudentNumber: '000',
        },    
        cashier: undefined,     
        user_level: undefined, 
        soa:{
            logo: undefined,
            address: undefined,
            total: 0,
            downpayment:0,
            installments: [],
        },
        user: undefined,
        term_balances: [],
        show_alert: false,
        change_payment_type: undefined,
        payment_type: 'full', 
        tuition_year: undefined,
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
            type: undefined,
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
            student_campus: '<?php echo $campus; ?>',            
        },
        or_update_description: undefined,
        or_update:{
            id: undefined,
            or_number: undefined,
            cashier_id: undefined,
            sy_reference: undefined,
            total_amount_due: undefined,
            student_campus: undefined,
        },
        amount_to_pay: 0,       
        cashier_start: 0,
        cashier_end: 0,     
        sy: [],
        advanced_privilages: false,     
        finance_manager_privilages: false, 
        description: 'Tuition Fee', 
        description_other: '',
        registration: {
            downpayment:0,
        },
        other_payments:[],
        tuition:'',
        tuition_data: {},
        tuition_years: [],
        reservation_payments: [],
        particulars: [],
        application_payment: undefined,
        registration_status: 0,
        allow_enroll: 0,
        remaining_amount: 0,
        amount_paid: 0,
        amount_paid_formatted: 0,
        payments: [],
        payments_paid: [],
        ledger_items: [],
        remaining_amount_formatted: 0,
        has_partial: false,
        reg_status: undefined,        
        loader_spinner: true, 
        installments: [],    
        installment_dates:[],  
        applicant_id: undefined,
        current_term: undefined,
        void_reason: undefined,
        void_id: undefined,
        user:{
            special_role: 0,
        },                 
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;            
            axios.get(this.base_url + 'unity/registration_viewer_data/' + this.id + '/' + this.sem)
                .then((data) => {  
                    if(data.data.success){      
                        this.or_update.sy_reference = this.sem;                                                                                                                 
                        this.user_level = data.data.user_level;
                        this.sy = data.data.sy;
                        this.ledger_items = data.data.ledger;
                        this.term_balances = data.data.term_balances;
                        this.particulars = data.data.particulars;
                        for (i in this.term_balances)
                        if (this.term_balances[i].balance > 0)
                            this.show_alert = true;
                        this.current_term = data.data.active_sem;
                        this.current_term_full_label = this.current_term.term_label == "Term" ? "Trimester" : "Semester";
                        this.installment_dates.push(data.data.active_sem.installment1_formatted);
                        this.installment_dates.push(data.data.active_sem.installment2_formatted);
                        this.installment_dates.push(data.data.active_sem.installment3_formatted);
                        this.installment_dates.push(data.data.active_sem.installment4_formatted);
                        this.installment_dates.push(data.data.active_sem.installment5_formatted);                                                
                        
                        if(data.data.registration){         
                            this.registration = data.data.registration;
                            this.registration_status = data.data.registration.intROG;  
                            this.allow_enroll = data.data.registration.allow_enroll;                          
                            this.tuition = data.data.tuition;
                            this.tuition_data = data.data.tuition_data;                                               
                            this.payment_type = this.registration.paymentType;
                            this.remaining_amount = data.data.tuition_data.total; 
                            this.change_payment_type = this.payment_type;
                            this.tuition_years = data.data.tuition_years;
                            this.tuition_year = this.registration.tuition_year;
                        }
                        this.user = data.data.user;
                        this.reg_status = data.data.reg_status;                        
                        this.student = data.data.student;  
                        this.or_print.type = this.student.type;       
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
                            this.or_update.student_campus = this.request.student_campus;
                            this.soa.logo = (this.or_update.student_campus == "Cebu")?"https://i.ibb.co/9hgbYNB/seal.png":"https://i.ibb.co/kcYVsS7/i-ACADEMY-Seal-Makati.png";            
                            this.soa.address = (this.or_update.student_campus == "Cebu")?"5F Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug, Cebu City, Philippines":"iACADEMY Nexus Campus, 7434 Yakal, Makati, 1203 Metro Manila, Philippines";
                        }                        
                        

                        axios.get(api_url + 'finance/transactions/' + this.slug + '/' + this.sem)
                        .then((data) => {
                            this.payments = data.data.data;
                            this.other_payments = data.data.other;   
                            this.applicant_data = data.data.student;                         
                            this.applicant_id = "A"+this.current_term.strYearStart+"-"+String(this.applicant_data.id).padStart(4, '0');                                     
                            if(this.registration && this.registration.paymentType == 'partial')
                                this.has_partial = true;
                                                                                    

                            if(this.has_partial)
                                this.remaining_amount = this.tuition_data.total_installment;                            

                            for(i in this.payments){
                                if(this.payments[i].status == "Paid" || this.payments[i].status == "Void"){     
                                    if(!this.payments[i].mode)
                                    this.payments[i].mode = {
                                        name: 'Other'
                                    };
                                    this.payments_paid.push(this.payments[i]);                         
                                    this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
                                    this.amount_paid = this.amount_paid + this.payments[i].subtotal_order;
                                }                                
                            }         
                            for(i in this.ledger_items){                                
                                this.remaining_amount += parseFloat(this.ledger_items[i].amount);                                
                                if(this.ledger_items[i].amount < 0){
                                    this.ledger_items[i].type = "payment";
                                    this.ledger_items[i].amount = this.ledger_items[i].amount * -1;
                                    this.ledger_items[i].amount = this.ledger_items[i].amount.toFixed(2);
                                }                
                                else{
                                    this.ledger_items[i].amount = parseFloat(this.ledger_items[i].amount).toFixed(2)
                                }                                
                            }                          
                            if(this.registration.enumStudentType == "new"){
                                axios.get(api_url + 'finance/reservation/' + this.slug + '/' + this.sem)
                                .then((data) => {
                                    this.reservation_payments = data.data.data;    
                                    this.application_payment = data.data.application;
                                    
                                    for(i in this.reservation_payments){
                                        if(!this.reservation_payments[i].mode)
                                            this.reservation_payments[i].mode = {
                                                name: 'Other'
                                            };

                                        if(this.reservation_payments[i].status == "Paid" && data.data.student_sy == this.sem){
                                            this.remaining_amount = this.remaining_amount - this.reservation_payments[i].subtotal_order;                                                                                                                                    
                                            this.amount_paid = this.amount_paid + this.reservation_payments[i].subtotal_order;      
                                            this.tuition_data.down_payment =  this.tuition_data.down_payment - this.reservation_payments[i].subtotal_order;
                                            }
                                    }

                                    if(this.application_payment && !this.application_payment.mode)
                                        this.application_payment.mode = {
                                            name: 'Other'
                                        };
                                    
                                    

                                    
                                    
                                    this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;                                
                                    this.remaining_amount_formatted = this.remaining_amount.replace(/\d(?=(\d{3})+\.)/g, '$&,');
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
                            }
                            else{
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
                            }

                            this.soa.installments = this.installments;
                            
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
        cashierDetails: function(id){
            axios.get(base_url + 'finance/cashier_details/' + id)
            .then((data) => {            
                var cashier_details = data.data.cashier_data;
                Swal.fire({
                    title: "Cashier/Appointer",
                    text: cashier_details.strFirstname+" "+cashier_details.strLastname,
                    icon: "info"
                })
            })

        },  
        addSuffix: function(i){
            let j = i % 10,
                k = i % 100;
            if (j === 1 && k !== 11) {
                return i + "st";
            }
            if (j === 2 && k !== 12) {
                return i + "nd";
            }
            if (j === 3 && k !== 13) {
                return i + "rd";
            }
            return i + "th";
        },
        forwardSelected: function(){
            if(this.switch_term && this.selected_items.length > 0){
                var data = {
                        'selected': this.selected_items,
                        'sy_reference': this.switch_term,
                    };
                let url = api_url + 'finance/transfer_payment';                    
                Swal.fire({
                title: 'Continue with the transfer',
                text: "Are you sure you want to transfer the payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                    preConfirm: (login) => {                                                

                        return axios.post(url, data, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                                .then(data => {
                                    this.loader_spinner = false;                                    
                                    var formdata= new FormData();
                                    formdata.append('payments',this.selected_items);                                        
                                    formdata.append('sy_reference',this.switch_term);                                                                                                                        
                                    axios.post(base_url + 'finance/transfer_ledger_update', formdata, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                    })
                                    .then(function(data){
                                        location.reload();
                                    })
                                    
                                })
                            }
                 });
                    
            }else{
                if(this.selected_items.length == 0)
                    Swal.fire({
                        title: "Warning",
                        text: "Please check at least one item",
                        icon: "success"
                    });  
                else
                    Swal.fire({
                        title: "Warning",
                        text: "Select a term to for transfer",
                        icon: "success"
                    });                            
            }
                
            
        },
        prepUpdate: function(id,desc,amount){
            this.or_update.id = id;
            this.or_update_description = desc;
            this.or_update.total_amount_due = amount;
        },        
        setValue: function(value,type,ctr){            
            if(ctr == 0){
                if(this.installments[ctr] != 0){
                    this.amount_to_pay = value;
                    this.description_other = type;
                }
            }
            else if(this.installments[ctr - 1] == 0 && this.installments[ctr] != 0){
                this.amount_to_pay = value;
                this.description_other = type;
            }
            else if(type == "full"){
                this.amount_to_pay = value;
            }
        },
        selectTerm: function(event){
            document.location = base_url + "unity/registration_viewer/" + this.id + "/" + event.target.value;
        },
        selectTuitionYear: function(event){
            let url = this.base_url + 'unity/update_tuition_year';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("tuition_year",event.target.value);                        
                        
            this.loader_spinner = true;
            
            //validate description
                      
            axios.post(url, formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                if (data.data.success) {
                        Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                       location.reload();
                    });
                }
            });

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
                                        const pay_length = this.payments_paid.length - 1;
                                        var formdata= new FormData();
                                        formdata.append('payments',this.payments.length);                                        
                                        //formdata.append('tuition_total',this.tuition_data.total_before_deductions);
                                        formdata.append('student_id',this.student.intID);                                                                                
                                        formdata.append('installment',this.tuition_data.total_installment);
                                        formdata.append('intID',this.cashier.intID);
                                        formdata.append('or_current',this.cashier.or_current);
                                        formdata.append('or_used',this.or_update.or_number);
                                        formdata.append('payments',pay_length);
                                        formdata.append('sy',this.sem);
                                        formdata.append('description',this.or_update_description);
                                        formdata.append('description_other',this.description_other);
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
                        this.or_print.transaction_date = payment.or_date;
                        this.or_print.remarks = payment.remarks;
                        this.or_print.student_name =  this.request.last_name+", "+this.request.first_name+", "+this.request.middle_name;    
                        this.or_print.student_address = this.student.strAddress;
                        if(this.student.strStudentNumber.charAt(0) != "T")
                            this.or_print.student_id = this.student.strStudentNumber;
                        else
                            this.or_print.student_id = this.applicant_id;
                        this.or_print.is_cash = payment.is_cash;
                        this.or_print.check_number = payment.check_number;
                        this.or_print.sem = payment.sy_reference;
                        this.or_print.cashier_id = payment.cashier_id;                                                                                                
                    }
            }).then((result) => {
                var delayInMilliseconds = 1000; //1 second
                var or_send = this.$refs.print_or;
                setTimeout(function() {
                    or_send.submit();
                }, delayInMilliseconds);
                            
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
            this.void_id = payment_id;        
            this.void_reason =  undefined;    
        },        
        voidPayment: function(){                
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
                        
                        let payload = {'id':this.void_id,'void_reason': this.void_reason}

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
                                        formdata.append('payments',this.payments_paid.length);
                                        formdata.append('total_amount',this.request.total_amount_due);
                                        formdata.append('sy',this.sem);
                                        //formdata.append('tuition_total',this.tuition_data.total_before_deductions);
                                        formdata.append('student_id',this.student.intID);
                                        formdata.append('description',this.request.description);
                                        formdata.append('description_other',this.description_other);
                                        formdata.append('registration_id',this.registration.intRegistrationID);
                                        formdata.append('installment',this.tuition_data.total_installment);
                                        formdata.append('payment_type',this.payment_type);
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
        changeType: function(event){
            let url = this.base_url + 'unity/update_registration_payment_type';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("paymentType",event.target.value);                        
                        
            this.loader_spinner = true;
            
            //validate description
                      
            axios.post(url, formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                if (data.data.success) {
                        Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                       location.reload();
                    });
                }
            });
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
                if(data.data.success){
                    var st = "Enrolled";
                    var date_enrolled = null;
                    if(this.registration_status == 0){
                        st = "Enlisted";
                    }
                    

                    return axios
                        .post(api_url + 'admissions/student-info/' + this.slug +
                            '/update-status', {
                                status: st,                                
                                remarks: "Finance Admin Update",
                                admissions_officer: this.user.strFirstname+" "+this.user.strLastname,
                            }, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
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
        changeAllowEnroll: function(){
            let url = this.base_url + 'unity/update_allow_enroll';
            var formdata= new FormData();
            formdata.append("intRegistrationID",this.registration.intRegistrationID);
            formdata.append("allow_enroll",this.allow_enroll);
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
                if(data.data.success){
                    Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                        //location.reload();
                    });

                    
                   
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
           
            
            
        }

    }

})
</script>

