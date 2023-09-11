<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Student Grade Slip
            <small>
                <!-- <a class="btn btn-app" href="<?php echo base_url(); ?>registrar/registrar_reports" >
                    <i class="ion ion-arrow-left-a"></i>
                    All Reports
                </a>  -->
                <!-- <a class="btn btn-app" target="_blank" href="<?php echo $pdf_link; ?>" ><i class="fa fa-book"></i>Generate PDF</a> 
                <a class="btn btn-app" target="_blank" href="<?php echo $excel_link; ?>" ><i class="fa fa-book"></i>Generate Excel</a>  -->
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">                
        <h4>Student Grade Slip</h4>
        <div>
            <div class="row">
                <div class="col-sm-6">
                    {{ student.strStudentNumber }}
                </div>
                <div class="col-sm-6">
                    {{ student.strLastname+", "+student.strFirstname }}
                </div>
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
        sem: '<?php echo $sem; ?>',        
        id: '<?php echo $id; ?>',                                      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'registrar/student_grade_slip_data/'+this.id+'/'+this.sem)
                .then((data) => {  
                   this.enrolled = data.data.data;
                   for(i in this.enrolled){
                        this.all_enrolled +=  this.enrolled[i].enrolled_freshman + this.enrolled[i].enrolled_foreign + this.enrolled[i].enrolled_second + this.enrolled[i].enrolled_transferee;
                   }
                   
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
       
                                       
    }

})
</script>

