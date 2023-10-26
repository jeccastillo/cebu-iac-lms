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
                            </tr>
                        </thead>
                        <tbody>                                                         
                            <tr v-for="item in ledger">
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
            .get(base_url + 'portal/student_ledger_data/' + this.id + '/' + this.sem, {
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
                                  

                for(i in ledger_temp){
                    if(ledger_temp[i].is_disabled == 0){
                        this.running_balance += Number(ledger_temp[i].amount);                         
                        ledger_temp[i].muted = "";
                    }
                    else{
                        ledger_temp[i].muted = "text-muted";                        
                    }                    
                                                                                     
                    ledger_temp[i]['balance'] =  this.running_balance.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    
                    this.ledger.push(ledger_temp[i]);
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
        
        filterByTerm: function(event){
            document.location = this.base_url + 'finance/student_ledger/' + this.id + '/' + event.target.value;
        }
        
    }

})
</script>