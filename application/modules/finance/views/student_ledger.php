<aside class="right-side">
    <section class="content-header">
        <h1>
            Student Ledger
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
            <li class="active">Student Ledger</li>
        </ol>
    </section>
    <div class="content">
        <section class="section section_port relative" id="vue-container">                 
        
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
                            <th>Added By</th>
                        </tr>
                    </thead>
                    <tbody>                    
                        <tr>
                            
                            <td><input class="form-control" type="date" required v-model="request.date_of_birth"></td>
                            <td><input type="text" class="form-control" required v-model="request.name"></td>
                            <td>
                                <select class="form-control" required v-model="request.syid">
                                    <option v-for="opt_sy in sy" value="{{ opt_sy.intID }}">{{ opt_sy.enumSem + " Term " + opt_sy.strYearStart + " - " + opt_sy.strYearEnd }}</option>
                                </select>
                            </td>
                            <td><input type="number" required v-model="request.amount" class="form-control"></td>
                            <td><input type="submit" class="btn btn-primary" value="Add to Ledger"></td>                        
                        </tr>            
                        <tr v-for="item in ledger">
                            <td>{{ item.date }}</td>
                            <td>{{ item.name }}</td>
                            <td>{{ item.enumSem + " Term " + item.strYearStart + " - " + item.strYearEnd }}</td>
                            <td>{{ item.amount }}</td>
                            <td>{{ (item.added_by != 0) ? item.strLastname + " " + item.strFirstname : 'System Generated' }}</td>
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
        base_url: '<?php echo base_url(); ?>',
        ledger: [],
        student: undefined,
        running_balance: 0,
        sy: undefined,
        request:{
            date: undefined,
            name: undefined,
            syid: 0,
            amount: undefined,            
        }
    },
    mounted() {
       

        axios
            .get(base_url + 'finance/student_ledger_data/' + this.id, {
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
                    this.running_balance += Number(this.ledger[i].amount);
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

        },
        
    }

})
</script>