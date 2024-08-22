<aside class="right-side" id="vue-container">
    <section class="content-header">
        <h1>            
            <small>
                <a v-if="finance.intUserLevel == 6" style="margin-right:1rem;" class="btn btn-app" :href="base_url + 'finance/view_all_students'"><i class="ion ion-arrow-left-a"></i>All Students</a>
                <div class="pull-right">
                    <!-- <p>Select Term Filter</p>
                    <select  @change="filterByTerm($event)" class="form-control" required v-model="sem">
                        <option value=0>All</option>
                        <option v-for="sy_select in sy" :value="sy_select.intID">{{ sy_select.enumSem + " Term " + sy_select.strYearStart + " - " + sy_select.strYearEnd }}</option>
                    </select> -->
                </div>
            </small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
            <li class="active">Student Ledger</li>
        </ol>
    </section>
    <div class="content">
        <section class="section section_port relative">                 
        
            <div class="box box-widget widget-user-2">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-red">
                    <!-- /.widget-user-image -->
                    <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}</h3>                    
                    <h4 class="widget-user-desc" style="margin-left:0;">
                        {{ student.strStudentNumber }}<br />
                        {{ student.strProgramDescription }}<br />
                        {{ student_type.toUpperCase() }}
                    </h4>                     
                </div>                
            </div>                            
            <!-- <div class="box box-primary">
                <div class="box-header">Add Ledger Item</div>
                <div class="box-body">
                    <form @submit.prevent="submitLedgerItem" method="post">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>                                    
                                    <th>Particulars</th>
                                    <th>Sem/Term</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Remarks</th>
                                    <th>Submit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-if="finance && finance.special_role != 0">                                
                                    <td><input class="form-control" type="datetime-local" required v-model="request.date"></td>                                   
                                    <td><input type="text" class="form-control" required v-model="request.name"></td>
                                    <td>
                                        <select class="form-control" required v-model="request.syid">
                                            <option v-for="opt_sy in sy" :value="opt_sy.intID">{{ opt_sy.term_student_type + " " + opt_sy.enumSem + " " + opt_sy.term_label + " " + opt_sy.strYearStart + " - " + opt_sy.strYearEnd }}</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control" required v-model="request.type">
                                            <option value="tuition">Tuition</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </td>
                                    <td><input type="number" required step=".01" v-model="request.amount" class="form-control"></td>
                                    <td><input type="text" v-model="request.remarks" class="form-control"></td>
                                    <td><input type="submit" class="btn btn-primary" value="Add to Ledger"></td>           
                                    <td></td>             
                                </tr>
                            </tbody>
                        </table>
                    </form>  
                </div>
            </div> -->
            <div class="box box-primary">
                <div class="box-header">Ledger</div>
                <div class="box-body">
                    <table v-for="(term,i) in ledger" v-if="term.ledger_items.length > 0" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th colspan="13">Tuition</th>
                            </tr> 
                            <tr>
                                <th>School Year</th>
                                <th>Term/Semester</th>
                                <th>Scholarship</th>
                                <th>Payment Description</th>
                                <th>O.R. Date</th>
                                <th>O.R. Number</th>
                                <th>Remarks</th>
                                <th>Assessment</th>
                                <th>Payment</th>
                                <th>Balance</th>
                                <th>Added/Changed By</th>   
                                <th>Cashier/Appointer</th> 
                                <th>Actions</th>                                                                              
                            </tr>
                        </thead>
                        <tbody>                                                         
                            <tr v-for="(item,j) in term.ledger_items">                                
                                <td :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>
                                <td :class="item.muted">{{ item.scholarship_name }}</td>
                                <td :class="item.muted" v-if="(item.name == 'Application Payment' || item.name == 'Reservation Payment' || item.name == 'Tuition Fee') && finance.intUserLevel == 6 && finance.special_role >= 1">
                                    <select @change="updateDescription(item.payment_id,$event)" class="form-control" v-model="ledger[i].ledger_items[j].name">
                                        <option value="Application Payment">Application Payment</option>
                                        <option value="Tuition Fee">Tuition Fee</option>                                        
                                        <option value="Reservation Payment">Reservation Payment</option>                                        
                                    </select>
                                </td>
                                <td :class="item.muted" v-else>{{ item.name }}</td>
                                <td :class="item.muted">{{  item.date }}</td>
                                <td :class="item.muted">{{  item.or_number }}</td>
                                <td :class="item.muted">{{  item.remarks }}</td>
                                <td :class="item.muted">{{ (item.type != 'payment')?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ (item.type == 'payment')?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ item.balance < 0 ?"(" + numberWithCommas(item.balance * -1) + ")": numberWithCommas(item.balance) }}</td>
                                <td :class="item.muted">{{ (item.added_by != 0) ? 'Manually Generated': 'System Generated' }}</td>   
                                <td :class="item.muted" v-if="item.added_by == 0"><a @click="cashierDetails(item.cashier)" href="#">{{ item.cashier }}</a></td>
                                <td :class="item.muted" v-else><a @click="cashierDetails(item.added_by)" href="#">{{ item.added_by }}</a></td>
                                <td :class="item.muted" v-if="item.id && finance.intUserLevel == 6 && finance.special_role > 1">
                                    <button class="btn btn-danger" @click="deleteLedgerItem(item.id)">Delete</button><br />                                
                                </td>
                                    
                            </tr>
                            <tr>                                                                
                                <td colspan="11" class="text-right">Term Balance/Refund:{{ term.balance }}</td>                                
                                <td>    
                                <button data-toggle="modal" v-if="finance.intUserLevel == 6 && finance.special_role >= 1 && term.balance < 0"  @click="applyToTermUpdate(term)" 
                                                data-target="#applyToTermModal" class="btn btn-primary">Apply To Term</button>                                                                    
                                </td>                                
                            </tr>                                                                  
                        </tbody>                
                    </table>
                    <table class="table table-bordered table-striped">
                        <tr>                                
                            <td colspan="11" class="text-right"><strong>Grand Total Balance/Refund:{{ running_balance.toFixed(2) }}</strong></td>                            
                        </tr>
                    </table>
                    <table v-for="(term,i) in other" v-if="term.ledger_items.length > 0" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th colspan="11">Other</th>
                            </tr> 
                            <tr>
                                <th>School Year</th>
                                <th>Term/Semester</th>                                
                                <th>Payment Description</th>
                                <th>O.R. Date</th>
                                <th>O.R. Number</th>
                                <th>Remarks</th>
                                <th>Assessment</th>
                                <th>Payment</th>                                
                                <th>Added/Changed By</th>                                                                                                 
                                <th>Cashier</th> 
                                <th>Delete</th>                                
                            </tr>
                        </thead>
                        <tbody>                            
                            <tr v-for="(item,j) in term.ledger_items">
                                <td :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>                                
                                <td :class="item.muted" v-if="(item.name == 'Application Payment' || item.name == 'Reservation Payment' || item.name == 'Tuition Fee' || item.name == 'LATE ENROLLMENT FEE') && finance.intUserLevel == 6 && finance.special_role >= 1">
                                    <select @change="updateDescription(item.payment_id,$event)" class="form-control" v-model="other[i].ledger_items[j].name">
                                        <option value="Application Payment">Application Payment</option>
                                        <option value="Tuition Fee">Tuition Fee</option>                                        
                                        <option value="Reservation Payment">Reservation Payment</option>
                                        <option value="LATE ENROLLMENT FEE">LATE ENROLLMENT FEE</option>                                        
                                    </select>
                                </td>
                                <td :class="item.muted" v-else>{{ item.name }}</td>
                                <td :class="item.muted">{{  item.date }}</td>
                                <td :class="item.muted">{{  item.or_number }}</td>
                                <td :class="item.muted">{{  item.remarks }}</td>
                                <td :class="item.muted">{{ (item.type!= 'payment')?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ (item.type == 'payment')?numberWithCommas(item.amount):'-' }}</td>                               
                                <td :class="item.muted">{{ (item.added_by != 0) ? item.strLastname + " " + item.strFirstname : 'System Generated' }}</td>                                
                                <td :class="item.muted"><a @click="cashierDetails(item.cashier)" href="#">{{ item.cashier }}</a></td>
                                <td :class="item.muted" v-if="item.id && finance.intUserLevel == 6 && finance.special_role > 1"><button class="btn btn-danger" @click="deleteLedgerItem(item.id)">Delete</button></td>
                                <td :class="item.muted" v-else></td>                                                                                             
                                <td v-else></td>
                            </tr>
                            <!-- <tr>                                
                                <td colspan="11" class="text-right">Balance: {{ running_balance_other }}</td>                                
                            </tr>   -->
                        </tbody>
                    </table>                     
                </div>
            </div>
            
        </section>
        <div class="modal fade" id="applyToTermModal" role="dialog">
            <div class="modal-dialog modal-xl">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Apply To Term</h4>
                    </div>
                    <div class="modal-body">
                        <table class="table table-striped">
                            <tr>
                                <td>Excess Amount: </td>
                                <td v-if="balance_change">{{ balance_change }}</td>
                            </tr>
                        </table>
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group">    
                                    <label>Description</label>                                
                                    <select class="form-control" v-model="apply_description">
                                        <option value="Tuition Fee">Tuition Payment</option>
                                        <option value="Application Payment">Application Payment</option>
                                        <option value="Reservation Payment">Reservation Payment</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div v-if="apply_to_term.length == 0" class="row">
                            <div class="col-sm-3">
                                <div class="form-group">  
                                    <label>Amount</label>                                                             
                                    <input type="number" @keyup="changeBalance($event)" class="form-control" v-model="apply_term_amount" />
                                </div>
                            </div>                            
                            <div class="col-sm-3">
                                <div class="form-group">    
                                    <label>Type</label>                                
                                    <select class="form-control" required v-model="apply_term_type">                                
                                        <option value="tuition">Tuition</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-4">
                                <div class="form-group">     
                                    <label>Term to Apply</label>                               
                                    <select class="form-control" required v-model="apply_term">                                
                                        <option v-for="sy_select in sy" :value="sy_select.intID">{{ sy_select.enumSem + " Term " + sy_select.strYearStart + " - " + sy_select.strYearEnd }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-1">
                                <div class="form-group">    
                                    <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>                                  
                                    <button class="btn btn-success" @click="addTermBalance">+</button>
                                </div>
                            </div>                            
                        </div>
                        <table v-else class="table table-bordered table-striped">
                            <tr>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Term To</th>
                                <th>Remove</th>
                            </tr>
                            <tr v-for="(item,i) in apply_to_term">
                                <th>{{ item.amount }}</th>
                                <th>{{ item.description }}</th>
                                <th>{{ item.type }}</th>
                                <th>{{ item.term_to }}</th>
                                <th><button @click="removeApply(i)" class="btn btn-danger">-</button></th>
                            </tr>
                        </table>
                    </div>
                    <div class=" modal-footer">
                        <!-- modal footer  -->
                        <button v-if="apply_to_term.length > 0" @click="applyToTerm" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>            
        </div>
    </div>
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<style scoped="">

</style>


<script>
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}    
new Vue({
    el: "#vue-container",
    data: {
        id: '<?php echo $id; ?>',
        sem: '<?php echo $sem; ?>',
        base_url: '<?php echo base_url(); ?>',
        ledger: [],      
        ledger_term: [],    
        other_term: [],           
        student_type: undefined,  
        term_balance: 0,
        apply_to_term: [],
        term_balance_other: 0,
        apply_term_balance: 0,
        apply_term_type: 'tuition',
        balance_change: 0,
        tuition: [],
        apply_term: undefined,
        apply_description: 'Tuition Fee',
        update_id: undefined,
        sy_from: undefined,
        apply_term_amount: undefined,
        apply_term_description: undefined,
        other: [],
        finance: undefined, 
        student: {
            strFirstname:'',
            strLastname:'',
            strMiddlename:'',
            strProgramDescription: '',
            strMajor:'',

        },
        sync_data:{
            updated_at:'<?php echo $max_id; ?>',
            campus:'<?php echo $campus; ?>',
        },
        running_balance: 0,
        running_balance_other: 0,
        sy: undefined,               
        request:{
            student_id: '<?php echo $id; ?>',
            date: undefined,
            name: undefined,
            syid: 0,
            amount: undefined, 
            type: 'tuition',   
            remarks: "",        
        }
    },
    mounted() {        
        var now = new Date();
        var year = now.getFullYear();
        var month = now.getMonth() + 1;
        var day = now.getDate();
        var hour = now.getHours();
        var minute = now.getMinutes();
        var amount = 0;
        var localDatetime =
        year +
        '-' +
        (month < 10 ? '0' + month.toString() : month) +
        '-' +
        (day < 10 ? '0' + day.toString() : day) +
        'T' +
        (hour < 10 ? '0' + hour.toString() : hour) +
        ':' +
        (minute < 10 ? '0' + minute.toString() : minute);
        this.request.date = localDatetime;
        
        axios
            .get(base_url + 'scholarship/student_ledger_data/' + this.id + '/' + this.sem, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {                                                  
                this.finance = data.data.user;
                this.tuition = data.data.tuition;
                this.student = data.data.student;
                this.student_type = data.data.current_type;
                this.sy = data.data.sy;
                this.request.syid = data.data.active_sem;  
                var current_sy_id = 0;                                               

                for(i in this.tuition){                                        
                        this.getPayments(this.tuition[i]);                                                          
                }
                                    
                // console.log(data);
            });
       

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
        getPayments: function(tuition){
            
            this.term_balance = 0;
            this.term_balance_other = 0;
            this.ledger_term = [];
            this.other_term = [];
            if(tuition.term.paymentType == 'partial')
                amount = tuition.ti_before_deductions;
            else
                amount = tuition.total_before_deductions;

            this.term_balance += amount;

            this.ledger_term.push({                
                'strYearStart':tuition.term.strYearStart,
                'strYearEnd':tuition.term.strYearEnd,
                'enumSem':tuition.term.enumSem,
                'term_label':tuition.term.term_label,
                'syid':tuition.term.intID,
                'scholarship_name':'',
                'name':'Tuition',
                'or_number':'',
                'remarks':'',
                'amount': amount.toFixed(2),
                'added_by': 0,
                'is_disabled':0,
                'balance': this.term_balance.toFixed(2),
            });

            var payments = tuition.payments_tuition;                
            
            for(i in payments){                       
                var paid = payments[i].subtotal_order * -1;
                this.term_balance += paid;
                this.ledger_term.push({
                    'payment_id':payments[i].id,
                    'type':'payment',
                    'strYearStart':tuition.term.strYearStart,
                    'strYearEnd':tuition.term.strYearEnd,
                    'enumSem':tuition.term.enumSem,
                    'term_label':tuition.term.term_label,
                    'syid':tuition.term.intID,
                    'scholarship_name':'',
                    'date': payments[i].created_at,
                    'name': payments[i].description,
                    'or_number':payments[i].or_number,
                    'remarks': payments[i].remarks,
                    'amount': parseFloat(payments[i].subtotal_order).toFixed(2),
                    'added_by': 0, 
                    'cashier': payments[i].cashier_id,
                    'is_disabled':0,
                    'balance': this.term_balance.toFixed(2),
                });                
            }            

            for(i in tuition.scholarship){
                var scholarship_amount = 0;
                var sa = 0;
                if(tuition.term.paymentType == 'partial'){
                    scholarship_amount = tuition.scholarship_deductions_installment_array[i] * -1;
                    sa = tuition.scholarship_deductions_installment_array[i];
                }
                else{
                    scholarship_amount = tuition.scholarship_deductions_array[i] * -1;
                    sa = tuition.scholarship_deductions_array[i];
                }
                                        
                this.term_balance += scholarship_amount;
                this.ledger_term.push({
                    'type': 'payment',
                    'strYearStart':tuition.term.strYearStart,
                    'strYearEnd':tuition.term.strYearEnd,
                    'enumSem':tuition.term.enumSem,
                    'term_label':tuition.term.term_label,
                    'syid':tuition.term.intID,
                    'scholarship_name': tuition.scholarship[i].name,
                    'name':'Scholarship',
                    'or_number':'',
                    'date': tuition.scholarship[i].date_applied,
                    'remarks':'',
                    'amount': sa.toFixed(2),
                    'added_by': 0,
                    'cashier': tuition.scholarship[i].created_by_id,
                    'is_disabled':0,
                    'balance': this.term_balance.toFixed(2),
                }); 
            
            }            

            for(i in tuition.other){                                        
                this.term_balance_other += parseFloat(tuition.other[i].amount);

                tuition.other[i]['balance'] = this.term_balance_other.toFixed(2);
                if(tuition.other[i].amount < 0){
                    tuition.other[i].type = "payment";
                    tuition.other[i].amount = tuition.other[i].amount * -1;
                    tuition.other[i].amount = tuition.other[i].amount.toFixed(2);
                }
                else{
                    tuition.other[i].amount = parseFloat(tuition.other[i].amount).toFixed(2)
                }
                this.other_term.push(tuition.other[i]); 
            
            }

            

            for(i in tuition.discount){
                var discount_amount = 0;
                var dc = 0;
                if(tuition.term.paymentType == 'partial'){
                    discount_amount = tuition.scholarship_deductions_installment_dc_array[i] * -1;
                    dc = tuition.scholarship_deductions_installment_dc_array[i];
                }
                else{
                    discount_amount = tuition.scholarship_deductions_dc_array[i] * -1;
                    dc = tuition.scholarship_deductions_dc_array[i];
                }
                                        
                this.term_balance += discount_amount;
                this.ledger_term.push({
                    'type': 'payment',
                    'strYearStart':tuition.term.strYearStart,
                    'strYearEnd':tuition.term.strYearEnd,
                    'enumSem':tuition.term.enumSem,
                    'term_label':tuition.term.term_label,
                    'syid':tuition.term.intID,
                    'scholarship_name': tuition.discount[i].name,
                    'name':'Discount',
                    'or_number':'',
                    'date': tuition.discount[i].date_applied,
                    'remarks':'',
                    'amount': dc.toFixed(2),
                    'added_by': 0,
                    'is_disabled':0,
                    'cashier': tuition.discount[i].created_by_id,
                    'balance': this.term_balance.toFixed(2),
                }); 
            
            }

            for(i in tuition.ledger){
                                        
                this.term_balance += parseFloat(tuition.ledger[i].amount);
                tuition.ledger[i]['balance'] = this.term_balance.toFixed(2);
                if(tuition.ledger[i].amount < 0){
                    tuition.ledger[i].type = "payment";
                    tuition.ledger[i].amount = tuition.ledger[i].amount * -1;
                    tuition.ledger[i].amount = tuition.ledger[i].amount.toFixed(2);
                }                
                else{
                    tuition.ledger[i].amount = parseFloat(tuition.ledger[i].amount).toFixed(2)
                }
                this.ledger_term.push(tuition.ledger[i]);                
            }
            var other = tuition.payments_other;                                              
            
            for(i in other){                                                                                   
                var paid = other[i].subtotal_order * -1;
                this.term_balance_other += paid;                
                this.other_term.push({
                    'payment_id':other[i].id,
                    'type':'payment',
                    'strYearStart':tuition.term.strYearStart,
                    'strYearEnd':tuition.term.strYearEnd,
                    'enumSem':tuition.term.enumSem,
                    'term_label':tuition.term.term_label,
                    'syid':tuition.term.intID,
                    'scholarship_name':'',
                    'date': other[i].created_at,
                    'name': other[i].description,
                    'or_number':other[i].or_number,
                    'remarks': other[i].remarks,
                    'amount': parseFloat(other[i].subtotal_order).toFixed(2),
                    'added_by': 0, 
                    'cashier': other[i].cashier_id,
                    'is_disabled':0,
                    'balance': this.term_balance_other.toFixed(2),
                });
                
            }

            this.ledger.push({
                'ledger_items': this.ledger_term,
                'balance': this.term_balance.toFixed(2)
            });

            this.other.push({
                'ledger_items': this.other_term,
                'balance': this.term_balance_other.toFixed(2)
            });

            this.running_balance += this.term_balance; 
            this.running_balance_other += this.term_balance_other; 
                
        },
        submitLedgerItem: function(){
            let url = this.base_url + 'finance/submit_ledger_item';                        
            
            Swal.fire({
                title: 'Add to Ledger?',
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata = new FormData();                    
                    for(const [key,value] of Object.entries(this.request)){                   
                        formdata.append(key,value);
                    }
                    
                    return axios.post(url, formdata, {
                        headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            
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
        deleteLedgerItem: function(id){
            let url = this.base_url + 'finance/delete_ledger_item';                        
            
            Swal.fire({
                title: 'Delete frome Ledger?',
                text: "Are you sure you want to delete?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata = new FormData();                    
                    formdata.append('id',id);
                    
                    
                    return axios.post(url, formdata, {
                        headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            
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
        switchType: function(id,type){
            let url = this.base_url + 'finance/update_ledger_item';                        
            
            Swal.fire({
                title: 'Switch Type Item?',
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata = new FormData();                                        
                    formdata.append('type',type);
                    formdata.append('id',id);
                    
                    
                    return axios.post(url, formdata, {
                        headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            
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
        updateDescription: function(id,event){
            var desc = event.target.value;
            let url = api_url + 'finance/update_payment_description';
            
            Swal.fire({
                title: 'Change Description?',
                text: "Are you sure you want to change payment description?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata = new FormData();                                        
                    formdata.append('description',desc);
                    formdata.append('id',id);
                    
                    
                    return axios.post(url, formdata, {
                        headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            
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
        applyToTermUpdate(term){            
            this.sy_from = term.ledger_items[0].syid;
            this.apply_term_balance = term.balance;   
            this.balance_change = term.balance;
            this.apply_term_type = 'tuition';
            this.apply_term_amount = 0;
            this.apply_to_term = [];
        },        
        changeBalance(event){
            if(this.apply_term_amount && this.apply_term_amount > 0){
                this.balance_change = parseFloat(this.apply_term_balance) + parseFloat(event.target.value);
                if(this.balance_change > 0){
                    this.balance_change = 0;
                    this.apply_term_amount = Math.abs(parseFloat(this.apply_term_balance));
                }

                this.balance_change.toFixed(2);
            }            
        },
        removeApply: function(index){
            this.apply_to_term.splice(index,1);
        },
        addTermBalance(){
            if(this.apply_term && this.apply_term_amount > 0 && this.apply_description){
                this.apply_to_term.push({
                    'amount': this.apply_term_amount,
                    'term_from': this.sy_from,
                    'term_to': this.apply_term,
                    'type': this.apply_term_type,
                    'description': this.apply_description,
                });
                this.apply_term_amount = 0;                                
                this.description =  '';

                this.apply_term_balance = this.balance_change;

            }
            else            
                Swal.fire({
                    title: "Warning",
                    text: 'Please fill in all fields',
                    icon: "error"
                });            
            console.log(this.apply_to_term);
        },
        applyToTerm: function(){            
            let url = this.base_url + 'finance/apply_to_term';                        
            let sy = this.apply_term;
            let id = this.update_id;
            let sy_from = this.sy_from;
            let apply_term_amount = this.apply_term_amount;
            let apply_term_description = this.apply_term_description;
            
            Swal.fire({
                title: 'Switch Term?',
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata = new FormData();                                        
                    formdata.append('transfer_data',JSON.stringify(this.apply_to_term));
                    formdata.append('sy_from',sy_from);                                        
                    formdata.append('student_id',this.request.student_id);
                                        
                    
                    return axios.post(url, formdata, {
                        headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            
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
        changeLedgerItemStatus: function(type,id){

            let url = this.base_url + 'finance/update_ledger_item_status';                        
            
            Swal.fire({
                title: 'Enable/Disble Ledger Item?',
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata = new FormData();                                        
                    formdata.append('type',type);
                    formdata.append('id',id);
                    
                    
                    return axios.post(url, formdata, {
                        headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            
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
        filterByTerm: function(event){
            document.location = this.base_url + 'finance/student_ledger/' + this.id + '/' + event.target.value;
        }
        
    }

})
</script>