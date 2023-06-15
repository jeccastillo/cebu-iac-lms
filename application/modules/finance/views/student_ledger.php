<aside class="right-side" id="vue-container">
    <section class="content-header">
        <h1>
            Student Ledger
            <small>
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
                    <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }}  {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                </div>                
            </div>                            
            <form @submit.prevent="submitLedgerItem" method="post">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Detail</th>
                            <th>Sem/Term</th>
                            <th>Amount</th>
                            <th>Added/Changed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>                    
                        <tr>
                            
                            <td><input class="form-control" type="datetime-local" required v-model="request.date"></td>
                            <td><input type="text" class="form-control" required v-model="request.name"></td>
                            <td>
                                <select class="form-control" required v-model="request.syid">
                                    <option v-for="opt_sy in sy" :value="opt_sy.intID">{{ opt_sy.enumSem + " Term " + opt_sy.strYearStart + " - " + opt_sy.strYearEnd }}</option>
                                </select>
                            </td>
                            <td><input type="number" required v-model="request.amount" class="form-control"></td>
                            <td><input type="submit" class="btn btn-primary" value="Add to Ledger"></td>           
                            <td></td>             
                        </tr>            
                        <tr v-for="item in ledger">
                            <td :class="item.muted">{{ item.date }}</td>
                            <td :class="item.muted">{{ item.name }}</td>
                            <td :class="item.muted">{{ item.enumSem + " Term " + item.strYearStart + " - " + item.strYearEnd }}</td>
                            <td :class="item.muted">{{ item.amount }}</td>
                            <td :class="item.muted">{{ (item.added_by != 0) ? item.strLastname + " " + item.strFirstname : 'System Generated' }}</td>
                            <td>
                                <button class="btn btn-success" v-if="item.is_disabled != 0" @click="changeLedgerItemStatus(0,item.id)">Enable</button>
                                <button v-else class="btn btn-danger" @click="changeLedgerItemStatus(1,item.id)">Disable</button>
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>Running Balance</td>
                            <td>{{ running_balance }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>     
            </form>  
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
        student: {
            strFirstname:'',
            strLastname:'',
            strMiddlename:'',
            strProgramDescription: '',
            strMajor:'',

        },
        running_balance: 0,
        sy: undefined,        
        request:{
            student_id: '<?php echo $id; ?>',
            date: undefined,
            name: undefined,
            syid: 0,
            amount: undefined,            
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
                this.ledger = data.data.ledger;
                this.student = data.data.student;
                this.sy = data.data.sy;
                this.request.syid = data.data.active_sem;  
                                  

                for(i in this.ledger){
                    if(this.ledger[i].is_disabled == 0){
                        this.running_balance += Number(this.ledger[i].amount);                                            
                        this.ledger[i].muted = "";
                    }
                    else{
                        this.ledger[i].muted = "text-muted";                        
                    }
                }
                this.running_balance = this.running_balance.toFixed(2);
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