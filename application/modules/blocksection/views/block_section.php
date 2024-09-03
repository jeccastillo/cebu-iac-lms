
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <small>
                <a class="btn btn-app" href="<?php echo base_url() ?>blocksection/view_block_sections" ><i class="ion ion-eye"></i>View All Block Sections</a>                             
            </small>
        </section>
        <hr />
        <div class="content">
            <div class="row">                                        
                <div class="col-sm-12">  
                    <div class="box box-primary">
                        <div class="box-header">
                            <h3 class="box-title">Block Section</h3>
                        </div>
                        <form  @submit.prevent="submitBlockSection" method="post" role="form">
                            <div class="box-body">
                                <div class="form-group col-xs-6">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" required class="form-control" v-model="request.name" placeholder="Enter Name/Section">
                                </div>

                                <div class="form-group col-xs-6">
                                    <label for="intYearLevel">Program</label>
                                    <select class="form-control" name="intProgramID" v-model="request.intProgramID">                                        
                                        <option v-for="program in programs" :value="program.intProgramID">{{ program.strProgramCode }}</option>                                        
                                    </select>
                                </div>
                                <div class="form-group col-xs-6">
                                    <label for="intYearLevel">Enhanced</label>
                                    <select class="form-control" name="enhanced" v-model="request.enhanced">                                        
                                        <option value="0">No</option>                                        
                                        <option value="1">Yes</option>                                        
                                    </select>
                                </div>
                                <div class="form-group col-xs-6">
                                    <label for="intSYID">Choose Term</label>
                                    <select class="form-control" @change="selected_term_type = $event.target.getAttribute('data-stype')" name="intSYID" v-model="request.intSYID">                                        
                                        <option v-for="s in sy" :data-stype="s.term_student_type" :value="s.intID">{{ s.term_student_type}} {{ s.enumSem }} {{ s.term_label }} {{s.strYearStart }} - {{ s.strYearEnd }}</option>                                        
                                    </select>
                                </div>                                
                                <div class="form-group col-xs-6">
                                    <label for="intYearLevel">Year</label>
                                    <select v-if="selected_term_type == 'college'" class="form-control" name="year" v-model="request.year">                                        
                                        <option value="1">1st</option>                                        
                                        <option value="2">2nd</option>                                        
                                        <option value="3">3rd</option>                                        
                                        <option value="4">4th</option>                                        
                                        <option value="5">5th</option>                                        
                                        <option value="6">6th</option>                                        
                                    </select>
                                    <select v-else class="form-control" name="year" v-model="request.year">                                        
                                        <option value="1">Grade 11</option>                                        
                                        <option value="2">Grade 12</option>                                                                                
                                    </select>
                                </div>                                
                                <div class="form-group col-xs-12">
                                    <input type="submit" value="Submit" class="btn btn-default btn-flat">
                                </div>
                            </div>
                        </form>
                    </div>
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {
        base_url: "<?php echo base_url(); ?>",
        id: <?php echo $id; ?>,        
        request:{
            name: undefined,
            intProgramID: undefined,
            intSYID: undefined,
            year: undefined,
            enhanced: 0,
        },                  
        programs:[],
        sy: [],
        active_sem: {},
        selected_term_type: 'college',
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get(this.base_url + 'blocksection/block_section_data/' + this.id)
        .then((data) => {                       
            this.programs = data.data.data.programs;
            this.active_sem = data.data.data.active_sem;
            this.sy = data.data.sy;
            if(data.data.data.section)
                this.request = data.data.data.section;
            else{                
                this.request.intProgramID = this.programs[0].intProgramID;
            }
            console.log(this.sy);
            
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {     

        submitBlockSection: function(){
            let url = this.base_url + 'blocksection/submit_block_section/' + this.id;            
            this.loader_spinner = true;
            
            Swal.fire({
                title: 'Submit Block Section?',
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
                            this.loader_spinner = false;
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

    }

})
</script>