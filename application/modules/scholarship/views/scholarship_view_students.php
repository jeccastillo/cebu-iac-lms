<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Scholarships
        </h1>
        <ol class="breadcrumb">
            <li><a :href="base_url + 'scholarship/select_student'"><i class="fa fa-dashboard"></i>Select Student</a></li>
            <li class="active">Students With Scholarships</li>
        </ol>     
    </section>
        <hr />
    <div class="content"> 
        <div class="pull-right">
            <select @change="changeTerm($event)" class="form-control" v-model="current_sem">
                <option v-for="term in terms" :value="term.intID">{{ term.enumSem }} Term SY {{ term.strYearStart }} - {{ term.strYearEnd }}</option>
            </select>
        </div>
        <hr />
        <div class="box box-default">
            <div class="box-header">
                <h3>Scholarship</h3>
            </div>
            <div class="box-body">                
               <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Last Name</th><th>First Name</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="student in students">
                            <td>{{ student.strLastname }}</td>
                            <td>{{ student.strFirstname }}</td>
                            <td>
                                <a target="_blank" :href="'<?php echo base_url(); ?>/scholarship/assign_scholarship/'+current_sem+'/'+student.intID">
                                    View Scholarships/Discounts
                                </a>
                            </td>
                        </tr>
                    </tbody>
               </table>
            </div>
        </div>
    </div>
        
    </div>
  
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>

new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',
        current_sem: '<?php echo $sem; ?>',       
        students: [],      
        terms: [],
                      
    },

    mounted() {
        // this.enrolledStudents();
        axios.get(this.base_url + 'scholarship/scholarship_view_data/'+this.current_sem)
            .then((data) => {                        
                this.students = data.data.students;
                this.terms = data.data.terms;                                                
        })
        .catch((error) => {
            console.log(error);
        });
    },

    methods: {     
       
        changeTerm: function(event){
             document.location = base_url + 'scholarship/scholarship_view/' + event.target.value ;
        },
                                   
    }

})

$(document).ready(function(){     
});
</script>

