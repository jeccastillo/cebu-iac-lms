<aside class="right-side" id="vue-container">
    <section class="content-header">     
    <h1>
        My Ledger
        <small>view your current balance</small>
    </h1>  
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url() ?>portal/dashboard"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Ledger</li>
        </ol>
    </section>    
    <div class="content">
        <section class="section section_port relative">                 
        
            <div class="box box-widget widget-user-2">
                <!-- Add the bg color to the header using any of the bg-* classes -->
                <div class="widget-user-header bg-red">
                    <!-- /.widget-user-image -->
                    <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}</h3>                    
                    <h4 class="widget-user-desc" style="margin-left:0;">{{ student.strStudentNumber }}</h4>                   
                </div>                
            </div>                                        
            <div class="box box-primary">
                <div class="box-header">Ledger</div>
                <div class="box-body">
                    <table v-for="term in ledger" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th colspan="10">Tuition</th>
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
                            </tr>
                        </thead>
                        <tbody>                                                         
                            <tr v-for="item in term.ledger_items">                                
                                <td :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>
                                <td :class="item.muted">{{ item.scholarship_name }}</td>
                                <td :class="item.muted">{{ item.name }}</td>
                                <td :class="item.muted">{{  item.date }}</td>
                                <td :class="item.muted">{{  item.or_number }}</td>
                                <td :class="item.muted">{{  item.remarks }}</td>
                                <td :class="item.muted">{{ (item.type != 'payment')?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ (item.type == 'payment')?numberWithCommas(item.amount):'-' }}</td>                               
                                <td :class="item.muted">{{ item.balance }}</td>                                
                            </tr>
                            <tr>                                
                                <td colspan="10" class="text-right">Term Balance/Refund:{{ term.balance }}</td>                                
                            </tr>                                      
                        </tbody>                
                    </table>
                    <table class="table table-bordered table-striped">
                        <tr>                                
                            <td class="text-right">Grand Total Balance/Refund:{{ running_balance.toFixed(2) }}</td>                            
                        </tr>
                    </table>
                    <table v-for="term in other" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th colspan="9">Other</th>
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
                            </tr>
                        </thead>
                        <tbody>                            
                            <tr v-for="item in term.ledger_items">
                                <td :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>                                
                                <td :class="item.muted">{{ item.name }}</td>
                                <td :class="item.muted">{{  item.date }}</td>
                                <td :class="item.muted">{{  item.or_number }}</td>
                                <td :class="item.muted">{{  item.remarks }}</td>
                                <td :class="item.muted">{{ (item.type != 'payment')?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ (item.type == 'payment')?numberWithCommas(item.amount):'-' }}</td>                               
                            </tr>
                            <!-- <tr>                                
                                <td colspan="11" class="text-right">Balance: {{ running_balance_other }}</td>                                
                            </tr> -->
                        </tbody>
                    </table> 
                    <hr />                    
                    <div class="row">
                        <div class="col-sm-6">
                            <label>Select Term for Tuition Payment</label>
                            <select class="form-control" v-model="selected_term">
                                <option v-for="term in terms" :value="term.intID">{{ term.enumSem + ' ' + term.term_label + ' ' + term.strYearStart + '-' + term.strYearEnd }}</option>
                            </select>
                            <hr />
                            <a target="_blank" class="btn btn-primary" :href="base_url + 'unity/student_tuition_payment/' + student.slug + '/' + selected_term">Pay Tuition</a>
                        </div>      
                    </div>                        
                </div>
            </div>
            
        </section>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

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
        term_balances: [], 
        term_balance: 0,
        other_term: [],                     
        term_balance_other: 0,
        tuition: [],
        terms: [],
        selected_term: undefined,
        other: [],
        finance: undefined, 
        student: {
            strFirstname:'',
            strLastname:'',
            strMiddlename:'',
            strProgramDescription: '',
            strMajor:'',

        },
        running_balance: 0,
        running_balance_other: 0,
        sy: undefined,  
    },
    mounted() {        
        var amount = 0;

        axios
            .get(base_url + 'portal/student_ledger_data/' + this.id + '/' + this.sem, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {                          
                other_temp = data.data.other;
                this.finance = data.data.user;
                this.tuition = data.data.tuition;
                this.student = data.data.student;
                this.sy = data.data.sy;                
                var current_sy_id = 0;   
                this.terms = data.data.ledger_group_term;
                if(this.terms.length > 0)
                    this.selected_term = this.terms[0].intID;                                            

                for(i in this.tuition){                                        
                    this.getPayments(this.tuition[i]);                                                          
                }
                
                // console.log(data);
            });

   


    },

    methods: {      
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

            var payments = tuition.payments_tuition;   
                                                                 
            for(i in payments){                                                                                
                var paid = payments[i].subtotal_order * -1;
                this.term_balance += paid;
                this.ledger_term.push({
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

            for(i in tuition.ledger){
                                        
                this.term_balance += parseFloat(tuition.ledger[i].amount);
                tuition.ledger[i]['balance'] = this.term_balance;
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

            for(i in tuition.other){                                        
                this.term_balance_other += parseFloat(tuition.other[i].amount);

                tuition.other[i]['balance'] = this.term_balance_other;
                if(tuition.other[i].amount < 0){
                    tuition.other[i].type = "payment";
                    tuition.other[i].amount = tuition.other[i].amount * -1;
                    tuition.other[i].amount = tuition.other[i].amount.toFixed(2);
                }
                else{
                    tuition.other[i].amount = parseFloat(tuition.ledger[i].amount).toFixed(2)
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
           
            var other = tuition.payments_other;   
            for(i in other){                                                                                   
                var paid = other[i].subtotal_order * -1;
                this.term_balance_other += paid;                
                this.other_term.push({
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
        
        filterByTerm: function(event){
            document.location = this.base_url + 'finance/student_ledger/' + this.id + '/' + event.target.value;
        }
        
    }

})
</script>


