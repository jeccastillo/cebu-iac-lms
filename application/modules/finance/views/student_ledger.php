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
                    <table v-for="term in ledger" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th colspan="11">Tuition</th>
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
                                <td :class="item.muted">{{ (item.amount >= 0)?item.amount:'-' }}</td>
                                <td :class="item.muted">{{ (item.amount < 0)?item.amount:'-' }}</td>
                                <td :class="item.muted">{{ item.balance }}</td>
                                <td :class="item.muted">{{ (item.added_by != 0) ? item.strLastname + " " + item.strFirstname : 'System Generated' }}</td>                                
                            </tr>
                            <tr>                                
                                <td colspan="11" class="text-right">Term Balance/Refund:{{ term.balance }}</td>                                
                            </tr>                                      
                        </tbody>                
                    </table>
                    <table class="table table-bordered table-striped">
                        <tr>                                
                            <td class="text-right">Grand Total Balance/Refund:{{ running_balance }}</td>                            
                        </tr>
                    </table>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th colspan="12">Other</th>
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
                                <th>Balance</th>
                                <th>Added/Changed By</th>                                
                                <th>Switch to Tuition</th>
                                <th>Disable</th>
                            </tr>
                        </thead>
                        <tbody>                            
                            <tr v-for="item in other">
                                <td :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>                                
                                <td :class="item.muted">{{ item.name }}</td>
                                <td :class="item.muted">{{  item.date }}</td>
                                <td :class="item.muted">{{  item.or_number }}</td>
                                <td :class="item.muted">{{  item.remarks }}</td>
                                <td :class="item.muted">{{ (item.amount >= 0)?item.amount:'-' }}</td>
                                <td :class="item.muted">{{ (item.amount < 0)?item.amount:'-' }}</td>
                                <td :class="item.muted">{{ item.balance }}</td>
                                <td :class="item.muted">{{ (item.added_by != 0) ? item.strLastname + " " + item.strFirstname : 'System Generated' }}</td>
                                <td><button v-if="finance && finance.special_role != 0" @click="switchType(item.id,'other')" class="btn btn-default">Switch</button></td>
                                <td v-if="finance && finance.special_role != 0">
                                    <button class="btn btn-success" v-if="item.is_disabled != 0" @click="changeLedgerItemStatus(0,item.id)">Enable</button>
                                    <button v-else class="btn btn-danger" @click="changeLedgerItemStatus(1,item.id)">Disable</button>
                                </td>
                                <td v-else></td>
                            </tr>
                            <tr>                                
                                <td colspan="11" class="text-right">Balance: {{ running_balance_other }}</td>                                
                            </tr>
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
new Vue({
    el: "#vue-container",
    data: {
        id: '<?php echo $id; ?>',
        sem: '<?php echo $sem; ?>',
        base_url: '<?php echo base_url(); ?>',
        ledger: [],               
        term_balances: [], 
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
        running_balance: 0,
        running_balance_other: 0,
        sy: undefined,               
        request:{
            student_id: '<?php echo $id; ?>',
            date: undefined,
            name: undefined,
            syid: 0,
            amount: undefined, 
            type: 'other',   
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
            .get(base_url + 'finance/student_ledger_data/' + this.id + '/' + this.sem, {
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
                this.request.syid = data.data.active_sem;  
                var current_sy_id = 0;
                var term_balance = 0;
                

                for(i in this.tuition){
                    term_balance = 0;
                    var ledger_term = [];
                    if(this.tuition[i].term.paymentType == 'partial')
                        amount = this.tuition[i].ti_before_deductions;
                    else
                        amount = this.tuition[i].total_before_deductions;

                    term_balance += amount;

                    ledger_term.push({
                        'strYearStart':this.tuition[i].term.strYearStart,
                        'strYearEnd':this.tuition[i].term.strYearEnd,
                        'enumSem':this.tuition[i].term.enumSem,
                        'term_label':this.tuition[i].term.term_label,
                        'syid':this.tuition[i].term.intID,
                        'scholarship_name':'',
                        'name':'Tuition',
                        'or_number':'',
                        'remarks':'',
                        'amount': amount,
                        'added_by': 0,
                        'is_disabled':0,
                        'balance': term_balance,
                    });

                    for(i in this.tuition[i].scholarship){
                        var scholarship_amount = 0;
                        if(this.tuition[i].term.paymentType == 'partial')
                            scholarship_amount = this.tuition[i].scholarship_deductions_installment_array[i] * -1;
                        else
                            scholarship_amount = this.tuition[i].scholarship_deductions_array[i] * -1;
                        
                        scholarship_amount = scholarship_amount.toFixed(2);
                        term_balance += scholarship_amount;
                        ledger_term.push({
                            'strYearStart':this.tuition[i].term.strYearStart,
                            'strYearEnd':this.tuition[i].term.strYearEnd,
                            'enumSem':this.tuition[i].term.enumSem,
                            'term_label':this.tuition[i].term.term_label,
                            'syid':this.tuition[i].term.intID,
                            'scholarship_name': this.tuition[i].scholarship[i].name,
                            'name':'Scholarship',
                            'or_number':'',
                            'remarks':'',
                            'amount': scholarship_amount.toFixed(2),
                            'added_by': 0,
                            'is_disabled':0,
                            'balance': term_balance,
                        }); 
                    
                    }

                    for(i in this.tuition[i].discount){
                        var discount_amount = 0;
                        if(this.tuition[i].term.paymentType == 'partial')
                            discount_amount = this.tuition[i].scholarship_deductions_installment_dc_array[i] * -1;
                        else
                            discount_amount = this.tuition[i].scholarship_deductions_dc_array[i] * -1;
                        
                        discount_amount = discount_amount.toFixed(2);
                        term_balance += discount_amount;
                        ledger_term.push({
                            'strYearStart':this.tuition[i].term.strYearStart,
                            'strYearEnd':this.tuition[i].term.strYearEnd,
                            'enumSem':this.tuition[i].term.enumSem,
                            'term_label':this.tuition[i].term.term_label,
                            'syid':this.tuition[i].term.intID,
                            'scholarship_name': this.tuition[i].discount[i].name,
                            'name':'Discount',
                            'or_number':'',
                            'remarks':'',
                            'amount': discount_amount,
                            'added_by': 0,
                            'is_disabled':0,
                            'balance': term_balance,
                        }); 
                    
                    }
                    
                    

                    axios.get(api_url + 'finance/transactions/' + this.student.slug + '/' + this.tuition[i].term.intID)
                        .then((data) => {
                            var payments = data.data.data;                                                 
                            for(i in payments){                                
                                if(payments[i].status == "Paid"){                                    
                                    var paid = payments[i].subtotal_order * -1;
                                    term_balance += paid;
                                    ledger_term.push({
                                        'strYearStart':this.tuition[i].term.strYearStart,
                                        'strYearEnd':this.tuition[i].term.strYearEnd,
                                        'enumSem':this.tuition[i].term.enumSem,
                                        'term_label':this.tuition[i].term.term_label,
                                        'syid':this.tuition[i].term.intID,
                                        'scholarship_name':'',
                                        'name': payments[i].description,
                                        'or_number':payments[i].or_number,
                                        'remarks': payments[i].remarks,
                                        'amount': paid,
                                        'added_by': 0,
                                        'is_disabled':0,
                                        'balance': term_balance,
                                    });
                                }
                            }

                            this.ledger.push({
                                'ledger_items': ledger_term,
                                'balance': term_balance.toFixed(2)
                            });

                            this.running_balance += term_balance;                            
                            this.running_balance.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                
                            this.running_balance = this.running_balance.toFixed(2);
                    });                    
                }



                for(i in other_temp){
                    if(other_temp[i].is_disabled == 0){
                        this.running_balance_other += Number(other_temp[i].amount);                         
                        other_temp[i].muted = "";
                    }
                    else{
                        other_temp[i].muted = "text-muted";                        
                    }                    
                                                                                     
                    other_temp[i]['balance'] =  this.running_balance_other.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    
                    this.other.push(other_temp[i]);
                }
                this.running_balance_other = this.running_balance_other.toFixed(2);
                // console.log(data);
            })
            .catch((e) => {
                console.log("error");
            });

   


    },

    methods: {        
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