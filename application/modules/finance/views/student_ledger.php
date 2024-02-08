<aside class="right-side" id="vue-container">
    <section class="content-header">
        <h1>            
            <small>
                <a style="margin-right:1rem;" class="btn btn-app" :href="base_url + 'finance/view_all_students'"><i class="ion ion-arrow-left-a"></i>All Students</a>
                <div class="pull-right">
                    <p>Select Term Filter</p>
                    <select  @change="filterByTerm($event)" class="form-control" required v-model="sem">
                        <option value=0>All</option>
                        <option v-for="sy_select in sy" :value="sy_select.intID">{{ sy_select.enumSem + " Term " + sy_select.strYearStart + " - " + sy_select.strYearEnd }}</option>
                    </select>
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
                    <h4 class="widget-user-desc" style="margin-left:0;">{{ student.strStudentNumber }}</h4>                   
                </div>                
            </div>                            
            <div class="box box-primary">
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
            </div>
            <div class="box box-primary">
                <div class="box-header">Ledger</div>
                <div class="box-body">
                    <table v-for="term in ledger" v-if="term.ledger_items.length > 0" class="table table-bordered table-striped">
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
                            <tr v-for="item in term.ledger_items">                                
                                <td :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>
                                <td :class="item.muted">{{ item.scholarship_name }}</td>
                                <td :class="item.muted">{{ item.name }}</td>
                                <td :class="item.muted">{{  item.date }}</td>
                                <td :class="item.muted">{{  item.or_number }}</td>
                                <td :class="item.muted">{{  item.remarks }}</td>
                                <td :class="item.muted">{{ (!item.type)?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ (item.type == 'payment')?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ item.balance < 0 ?"(" + numberWithCommas(item.balance * -1) + ")": numberWithCommas(item.balance) }}</td>
                                <td :class="item.muted">{{ (item.added_by != 0) ? 'Manually Generated': 'System Generated' }}</td>   
                                <td :class="item.muted" v-if="item.added_by == 0"><a @click="cashierDetails(item.cashier)" href="#">{{ item.cashier }}</a></td>
                                <td :class="item.muted" v-else><a @click="cashierDetails(item.added_by)" href="#">{{ item.added_by }}</a></td>
                                <td :class="item.muted" v-if="item.id && finance && finance.special_role != 0"><button class="btn btn-danger" @click="deleteLedgerItem(item.id)">Delete</button></td>
                                <td :class="item.muted" v-else></td>                                                                                             
                            </tr>
                            <tr>                                
                                <td colspan="11" class="text-right">Term Balance/Refund:{{ term.balance }}</td>                                
                            </tr>                                                                  
                        </tbody>                
                    </table>
                    <table class="table table-bordered table-striped">
                        <tr>                                
                            <td colspan="11" class="text-right"><strong>Grand Total Balance/Refund:{{ running_balance.toFixed(2) }}</strong></td>                            
                        </tr>
                    </table>
                    <table v-for="term in other" v-if="term.ledger_items.length > 0" class="table table-bordered table-striped">
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
                            <tr v-for="item in term.ledger_items">
                                <td :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>                                
                                <td :class="item.muted">{{ item.name }}</td>
                                <td :class="item.muted">{{  item.date }}</td>
                                <td :class="item.muted">{{  item.or_number }}</td>
                                <td :class="item.muted">{{  item.remarks }}</td>
                                <td :class="item.muted">{{ (!item.type)?numberWithCommas(item.amount):'-' }}</td>
                                <td :class="item.muted">{{ (item.type == 'payment')?numberWithCommas(item.amount):'-' }}</td>                               
                                <td :class="item.muted">{{ (item.added_by != 0) ? item.strLastname + " " + item.strFirstname : 'System Generated' }}</td>                                
                                <td :class="item.muted"><a @click="cashierDetails(item.cashier)" href="#">{{ item.cashier }}</a></td>
                                <td :class="item.muted" v-if="item.id && finance && finance.special_role != 0"><button class="btn btn-danger" @click="deleteLedgerItem(item.id)">Delete</button></td>
                                <td :class="item.muted" v-else></td>                                                                                             
                                <td v-else></td>
                            </tr>
                            <!-- <tr>                                
                                <td colspan="11" class="text-right">Balance: {{ running_balance_other }}</td>                                
                            </tr> -->
                        </tbody>
                    </table>                     
                </div>
            </div>
            
        </section>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
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
        other_term: [],             
        term_balance: 0,
        term_balance_other: 0,
        tuition: [],
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
            id:'<?php echo $max_id; ?>',
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
        Swal.fire({
            showCancelButton: false,
            showCloseButton: false,
            allowEscapeKey: false,
            title: 'Syncing',
            text: 'Syncing Data do not leave page',
            icon: 'info',
        })
        Swal.showLoading();
        
        axios
            .post(api_url + 'finance/sync_payments', this.sync_data,{
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {                   
                var formdata = new FormData();                    
                formdata.append('data',JSON.stringify(data.data.data));
            axios
                .post(base_url + 'finance/sync_payment_details_data/',formdata,{
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    },
                })

            .then((data) => {   
                Swal.close();                       
                axios
                    .get(base_url + 'finance/student_ledger_data/' + this.id + '/' + this.sem, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        },
                    })

                    .then((data) => {                                                  
                        this.finance = data.data.user;
                        this.tuition = data.data.tuition;
                        this.student = data.data.student;
                        this.sy = data.data.sy;
                        this.request.syid = data.data.active_sem;  
                        var current_sy_id = 0;                                               

                        for(i in this.tuition){                                        
                             this.getPayments(this.tuition[i]);                                                          
                        }
                                            
                        // console.log(data);
                    });
            });
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

            for(i in tuition.ledger){
                                        
                this.term_balance += parseFloat(tuition.ledger[i].amount);
                tuition.ledger[i]['balance'] = this.term_balance.toFixed(2);
                if(tuition.ledger[i].amount < 0){
                    tuition.ledger[i].type = "payment";
                    tuition.ledger[i].amount = tuition.ledger[i].amount * -1;
                    tuition.ledger[i].amount = tuition.ledger[i].amount.toFixed(2);
                }                
                this.ledger_term.push(tuition.ledger[i]);
                console.log(tuition.ledger[i]);
            }

            for(i in tuition.other){                                        
                this.term_balance_other += parseFloat(tuition.other[i].amount);

                tuition.other[i]['balance'] = this.term_balance_other.toFixed(2);
                if(tuition.other[i].amount < 0){
                    tuition.other[i].type = "payment";
                    tuition.other[i].amount = tuition.other[i].amount * -1;
                    tuition.other[i].amount = tuition.other[i].amount.toFixed(2);
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
            var payments = tuition.payments_tuition;   
            var reservation = tuition.payments_reservation;  
            var other = tuition.payments_other;                                              
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
                    'date': payments[i].updated_at,
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

            for(i in reservation){                                                                                  
                var paid = reservation[i].subtotal_order * -1;
                this.term_balance += paid;
                this.ledger_term.push({
                    'type':'payment',
                    'strYearStart':tuition.term.strYearStart,
                    'strYearEnd':tuition.term.strYearEnd,
                    'enumSem':tuition.term.enumSem,
                    'term_label':tuition.term.term_label,
                    'syid':tuition.term.intID,
                    'scholarship_name':'',
                    'date': reservation[i].updated_at,
                    'name': reservation[i].description,
                    'or_number':reservation[i].or_number,
                    'remarks': reservation[i].remarks,
                    'amount': parseFloat(reservation[i].subtotal_order).toFixed(2),
                    'added_by': 0,
                    'cashier': payments[i].cashier_id,
                    'is_disabled':0,
                    'balance': this.term_balance.toFixed(2),
                });                
            }
            
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
                    'date': other[i].updated_at,
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
        switchTerm: function(id,event){
            var sy = event.target.value;
            let url = this.base_url + 'finance/update_ledger_item';                        
            
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
                    formdata.append('syid',sy);
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