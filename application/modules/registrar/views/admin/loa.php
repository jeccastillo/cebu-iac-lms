<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            LOA Tagging
            <small>                
            </small>
        </h1>     
    </section>
        <hr />
    <div class="content">                
        <h4>Tag Student for LOA</h4>
        <div>
            <div class="form-group pull-right">
                <label>Term Select</label>
                <select v-model="current_sem" @change="changeTermSelected($event)" class="form-control" >
                    <option v-for="s in sy" :value="s.intID">{{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
                </select>   
            </div>            
        </div>
        <!-- <h4>Enrolled</h4>
        <div v-for="prog in reserved" class="row">
            <div class="col-md-6">
                {{ prog.program }}
            </div>
            <div class="col-md-6">
                {{ prog.reserved_count }}
            </div>
        </div> -->
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
        id: '<?php echo $id; ?>',
        student: undefined,                      
    },

    mounted() {
        let url_string = window.location.href;        
        if(this.id != 0){                        
            axios.get(this.base_url + 'registrar/leave_of_abscence_data/' + this.id)
                .then((data) => {                      
                   this.student = data.data.student;
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {              
        changeTermSelected: function(event){
            document.location = this.base_url + "registrar/leave_of_abscence_data/" + this.id + '/' + event.target.value;
        },                        
    }

})
</script>

