<aside class="right-side" id="registration-container">
    <section class="content-header">
        <h1>
            Modular Subjects
        </h1>
    </section>
    <hr />
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box box-widget widget-user-2">
                    <!-- Add the bg color to the header using any of the bg-* classes -->
                    <div class="widget-user-header bg-red">                        
                        <div class="pull-right" style="margin-left:1rem;"> School Year & Term
                            <select class="form-control" @change="selectTerm($event)" v-model="sem">
                                <option v-for="s in sy" :value="s.intID">{{ s.term_student_type}}
                                    {{ s.enumSem }}
                                    {{ s.term_label }} {{ s.strYearStart }} - {{ s.strYearEnd }}
                                </option>
                            </select>
                        </div>                        
                    </div>                    
                </div>
            </div>
        </div>
        <div class="box box-solid box-success">
            <div class="box-header">                            
                <h4 class="box-title">Modular Subjects</h4>                            
            </div>
            <div class="box-body">     
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Section</th>
                            <th>Payment Amount</th>                            
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="subject in subjects">
                            <td>{{ subject.strCode }}</td>
                            <td>{{ subject.strClassName + subject.year + subject.strSection + subject.sub_section }}</td>
                            <td><input @blur="updateAmount($event,subject.intID)" type="number" class="form-control" step="0.01" :value="subject.payment_amount" /> </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>    
</aside>
<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js">
</script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.7.16/dist/vue.js"></script>
<script>
new Vue({
    el: '#registration-container',
    data: {        
        sem: <?php echo $term; ?>,
        base_url: '<?php echo base_url(); ?>',        
        subjects: [],
        sy: [],
        user: {
            special_role: 0,
        },
    },
    mounted() {
        let url_string = window.location.href;                    
        axios.get(this.base_url + 'finance/modular_subjects_data/' + this.sem).then((data) => {            
            if (data.data.success) {
                this.user = data.data.user;
                this.sy = data.data.sy;
                this.subjects  = data.data.subjects;
            }
        }).catch((error) => {
            console.log(error);
        })
        
    },
    methods: {
        selectTerm: function(event) {
            document.location = base_url + "finance/modular_subjects/" + event.target.value;
        },
        updateAmount: function(event,id){
            console.log("id",id+" "+event.target.value);
        }
    }
    
})
</script>