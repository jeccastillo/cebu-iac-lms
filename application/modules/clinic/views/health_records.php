<aside class="right-side" id="registration-container">    
<section class="content-header">
        <h1>
            Student Health Records
        </h1>
        <hr />
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h4>{{ student.strLastname + " " + student.strFirstname }}</h4>
                <h5>{{ student.strStudentNumber.replace(/-/g, "") }}</h5>
            </div>
            <div class="box-body">
                <h4>Health Records</h4>                                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Consultation Type</th>
                            <th>Chief Complaint/Reason for the Visit</th>
                            <th>History of Present Illness</th>                                                       
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="health_records.length == 0">
                            <td colspan='8'>No Records Found</td>
                        </tr>
                        <tr v-else v-for="item in health_records">
                            <td>{{ item.consultation_type }}</td>
                            <td>{{ item.chief_complaint }}</td>
                            <td>{{ item.history }}</td>
                                                                                    
                        </tr>
                    </tbody>
                </table>                              
            </div>        
        </div>
        
    </div>
    <!-- <div class="modal fade" id="record" role="dialog">        
        <div class="modal-content">
            <div class="modal-header">
        
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Health Record</h4>
            </div>
            <div class="modal-body">
                
            </div>
            <div class=" modal-footer">        
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>        
    </div> -->
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
        id: '<?php echo $id; ?>',
        health_records:[],
        student: undefined,
        current_record: undefined,
    
        
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'clinic/health_records_data/'+this.id)
                .then((data) => {                                      
                    this.student = data.data.student
                    this.health_records = data.data.health_records;
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

