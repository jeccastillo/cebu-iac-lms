<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Assign Scholarship/Discount                                 
        </h1>
        <ol class="breadcrumb">
            <li><a :href="base_url + 'scholarship/select_student'"><i class="fa fa-dashboard"></i>Select Student</a></li>
            <li class="active">Assign Scholarship</li>
        </ol>     
    </section>
        <hr />
    <div class="content"> 
        <div class="pull-right">
            <select @change="changeTerm($event)" class="form-control" v-model="current_sem">
                <option v-for="term in terms" :value="term.intID">{{ term.enumSem }} Term SY {{ term.strYearStart }} - {{ term.strYearEnd }}</option>
            </select>
        </div>
    </div>
        
    </div>
  
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>

new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',
        current_sem: '<?php echo $sem; ?>',
        student_id: '<?php echo $student; ?>'
        scholarships:[],
        discounts:[],
        terms:[],
        student_deductions:[],        
                      
    },

    mounted() {

        axios.get(this.base_url + 'scholarship/assign_scholarship_data/'+this.student_id+'/'+this.current_sem)
                .then((data) => {        
                    this.scholarships = data.data.scholarships;
                    this.discounts = data.data.discounts;
                    this.terms = data.data.terms;
                    this.student_deductions = data.data.student_deductions;
               
            })
            .catch((error) => {
                console.log(error);
            });
                        

    },

    methods: {      
       changeTerm: function(event){
         document.location = base_url + 'scholarship/assign_scholarship/' + event.target.value;
       }                                       
    }

})

$(document).ready(function(){     
});
</script>

