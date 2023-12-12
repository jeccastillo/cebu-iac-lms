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
                                    <th>Type</th>
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
                                    <td>
                                        <select class="form-control" required v-model="request.type">
                                            <option value="tuition">tuition</option>
                                            <option value="other">other</option>
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control" required v-model="request.name"></td>
                                    <td>
                                        <select class="form-control" required v-model="request.syid">
                                            <option v-for="opt_sy in sy" :value="opt_sy.intID">{{ opt_sy.term_student_type + " " + opt_sy.enumSem + " " + opt_sy.term_label + opt_sy.strYearStart + " - " + opt_sy.strYearEnd }}</option>
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
                <div class="box-header">Tuition</div>
                <div class="box-body">
                    <table class="table table-bordered table-striped">
                        <thead>
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
                                <th>Switch to Other</th>
                                <th>Disable</th>
                            </tr>
                        </thead>
                        <tbody>                                                         
                            <tr v-for="item in ledger">
                                <td colspan="2" v-if="finance.special_role != 0">
                                    <select @change="switchTerm(item.id,$event)" class="form-control" v-model="item.syid">
                                        <option v-for="opt_sy in sy" :value="opt_sy.intID">{{ opt_sy.term_student_type + " " + opt_sy.enumSem + " " + opt_sy.term_label + opt_sy.strYearStart + " - " + opt_sy.strYearEnd }}</option>
                                    </select>
                                </td>
                                <td v-if="finance.special_role == 0" :class="item.muted">{{ item.strYearStart + " - " + item.strYearEnd }}</td>
                                <td v-if="finance.special_role == 0" :class="item.muted">{{ item.enumSem +" "+ item.term_label }}</td>
                                <td :class="item.muted">{{ item.scholarship_name }}</td>
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
                                <td colspan="12" class="text-right">Grand Total Balance/Refund:{{ running_balance }}</td>
                                <td></td>
                            </tr>
                            <tr>
                                <th colspan="10">Other</th>
                            </tr>           
                        </tbody>                
                    </table>
                    <table class="table table-bordered table-striped">
                        <thead>
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
                ledger_temp = data.data.ledger;
                other_temp = data.data.other;
                this.finance = data.data.user;
                this.student = data.data.student;
                this.sy = data.data.sy;
                this.request.syid = data.data.active_sem;  
                var current_sy_id = 0;

                if(ledger_temp.length > 0)
                    current_sy_id = ledger_temp[0].syid;

                var term_balance = 0;
                for(i in ledger_temp){
                    if(ledger_temp[i].syid != current_sy_id){
                        term_balance = 0;
                    }
                    if(ledger_temp[i].is_disabled == 0){
                        this.running_balance += Number(ledger_temp[i].amount);                         
                        ledger_temp[i].muted = "";
                    }
                    else{
                        ledger_temp[i].muted = "text-muted";                        
                    }                    
                                                                                     
                    ledger_temp[i]['balance'] =  this.running_balance.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    
                    this.ledger.push(ledger_temp[i]);
                    current_sy_id = ledger_temp[i].syid;
                }
                this.running_balance = this.running_balance.toFixed(2);

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