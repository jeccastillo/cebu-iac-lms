<aside class="right-side">
    <section class="content-header">
        <h1>
            Finance
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
            <li class="active">Student Ledger</li>
        </ol>
    </section>
    <div class="content">
        <section class="section section_port relative" id="vue-container">     
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Date/Time</th>
                        <th>Detail</th>
                        <th>Sem/Term</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in ledger">
                        <td>{{ item.date }}</td>
                        <td>{{ item.name }}</td>
                        <td>{{ item.enumSem + " Term " + item.strYearStart + " - " + item.strYearEnd }}</td>
                        <td>{{ item.amount }}</td>
                    </tr>
                </tbody>
            </table>       
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
                // console.log(data);
            })
            .catch((e) => {
                console.log("error");
            });

   


    },

    methods: {

        
    }

})
</script>