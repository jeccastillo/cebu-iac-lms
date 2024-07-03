
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app" :href="base_url + 'scholarship/scholarships'" ><i class="ion ion-arrow-left-a"></i>Back to Scholarships</a>                                                                                                                     
                </small>
            </h1>
        </section>
        <hr />
        <div class="content">
            <div class="box box-primary">
                <div class="box-header">
                    <h3>Scholarship</h3>
                </div>
                <form method="post" @submit.prevent.stop="submitForm" >
                    <div class="box-body">
                        <div class="row">                        
                            <div class="col-md-6">
                                <label>Name:</label>
                                <input type="text" required v-model="scholarship.name" class="form-control">                            
                            </div>                        
                            <div class="col-md-6">
                                <label>Description:</label>
                                <textarea v-model="scholarship.description" required class="form-control"></textarea>                            
                            </div>
                            <div class="col-md-6">
                                <label>Type:</label>
                                <select required type="text" v-model="scholarship.type" class="form-control">
                                    <option v-for="type in type_options" :value="type">{{ type }}</option>                         
                                </select>
                            </div>                        
                            <div class="col-md-6">
                                <label>Status:</label>
                                <select required type="text" v-model="scholarship.status" class="form-control">    
                                    <option v-for="status in status_options" :value="status">{{ status }}</option>                        
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Deduction Type:</label>
                                <select required type="text" v-model="scholarship.deduction_type" class="form-control">    
                                    <option value="scholarship">scholarship</option>                        
                                    <option value="discount">discount</option>                        
                                </select>
                            </div>
                        </div>   
                        <hr />
                        <div>
                            <div class="col-md-4">
                                <div class="box box-solid box-primary">
                                    <div class="box-header">
                                        <strong>Tuition Fee</strong>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">                                                                                        
                                            <div class="col-md-6">
                                                <label>Rate:</label>
                                                <input type="number"  min=0 max=100 step=1 v-model="scholarship.tuition_fee_rate" class="form-control">
                                            </div>                        
                                            <div class="col-md-6">
                                                <label>Fixed:</label>
                                                <input type="number" v-model="scholarship.tuition_fee_fixed" class="form-control">
                                            </div>                        
                                        </div>           
                                    </div>
                                </div>
                            </div>
                            <!-- <div class="col-md-4">
                                <div class="box box-solid box-primary">
                                    <div class="box-header">
                                        <strong>Basic Fee</strong>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">                                                                                        
                                            <div class="col-md-6">
                                                <label>Rate:</label>
                                                <input type="number" min=0 max=100 step=1 v-model="scholarship.basic_fee_rate" class="form-control">
                                            </div>                        
                                            <div class="col-md-6">
                                                <label>Fixed:</label>
                                                <input type="number" v-model="scholarship.basic_fee_fixed" class="form-control">
                                            </div>                        
                                        </div>           
                                    </div>
                                </div>
                            </div> -->
                            <div class="col-md-4">
                                <div class="box box-solid box-primary">
                                    <div class="box-header">
                                        <strong>Miscellaneous Fee</strong>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">                                                                                        
                                            <div class="col-md-6">
                                                <label>Rate:</label>
                                                <input type="number" min=0 max=100 step=1 v-model="scholarship.misc_fee_rate" class="form-control">
                                            </div>                        
                                            <div class="col-md-6">
                                                <label>Fixed:</label>
                                                <input type="number" v-model="scholarship.misc_fee_fixed" class="form-control">
                                            </div>                        
                                        </div>           
                                    </div>           
                                </div>           
                            </div>
                        </div>                        
                        <div>
                            <div class="col-md-4">
                                <div class="box box-solid box-primary">
                                    <div class="box-header">
                                        <strong>Laboratory Fee</strong>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">                                                                                        
                                            <div class="col-md-6">
                                                <label>Rate:</label>
                                                <input type="number"  min=0 max=100 step=1 v-model="scholarship.lab_fee_rate" class="form-control">
                                            </div>                        
                                            <div class="col-md-6">
                                                <label>Fixed:</label>
                                                <input type="number" v-model="scholarship.lab_fee_fixed" class="form-control">
                                            </div>                        
                                        </div>           
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="box box-solid box-primary">
                                    <div class="box-header">
                                        <strong>Penalty Fee</strong>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">                                                
                                            <div class="col-md-6">
                                                <label>Rate:</label>
                                                <input type="number" min=0 max=100 step=1 v-model="scholarship.penalty_fee_rate" class="form-control">
                                            </div>                        
                                            <div class="col-md-6">
                                                <label>Fixed:</label>
                                                <input type="number" v-model="scholarship.penalty_fee_fixed" class="form-control">
                                            </div>                        
                                        </div> 
                                    </div>                                           
                                </div>                                           
                            </div>
                            <div class="col-md-4">
                                <div class="box box-solid box-primary">
                                    <div class="box-header">
                                        <strong>Other Fees</strong>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">                                                                                        
                                            <div class="col-md-6">
                                                <label>Rate:</label>
                                                <input type="number" min=0 max=100 step=1 v-model="scholarship.other_fees_rate" class="form-control">
                                            </div>                        
                                            <div class="col-md-6">
                                                <label>Fixed:</label>
                                                <input type="number" v-model="scholarship.other_fees_fixed" class="form-control">
                                            </div>                        
                                        </div>
                                    </div>
                                </div>           
                            </div>
                        </div> 
                        <hr />
                        <div>
                            <div class="col-md-6">
                                <div class="box box-solid box-primary">
                                    <div class="box-header">
                                        <strong>Total Assessment</strong>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">                                                                                
                                            <div class="col-md-6">
                                                <label>Rate:</label>
                                                <input type="number"  min=0 max=100 step=1 v-model="scholarship.total_assessment_rate" class="form-control">
                                            </div>                        
                                            <div class="col-md-6">
                                                <label>Fixed:</label>
                                                <input type="number" v-model="scholarship.total_assessment_fixed" class="form-control">
                                            </div>                        
                                        </div>           
                                    </div>           
                                </div>           
                            </div>                       
                        </div>                        
                        <div class="row"> 
                            <div class="col-sm-12">                         
                                <hr />                                                
                                <input class="btn btn-primray" type="submit" :value="btn_text" /> 
                            </div>
                        </div>
                    </div>                       
                </form>
            </div>            
        </div>                
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
        id: "<?php echo $id; ?>",
        base_url: "<?php echo base_url(); ?>",   
        btn_text: "Add Scholarship",
        scholarship: {
            intID: "<?php echo $id; ?>",
            name: undefined,
            description: undefined,
            status: undefined,
            deduction_type: undefined,
            type: undefined,
            created_by_id: undefined,
            tuition_fee_rate: undefined,
            tuition_fee_fixed: undefined,            
            misc_fee_rate: undefined,
            misc_fee_fixed: undefined,
            lab_fee_rate: undefined,
            lab_fee_fixed: undefined,
            penalty_fee_rate: undefined,
            penalty_fee_fixed: undefined,
            other_fees_rate: undefined,
            other_fees_fixed: undefined,
            total_assessment_rate: undefined,
            total_assessment_fixed: undefined,
        },
        type_options: [],
        status_options:[],
             
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        if(this.id != 0)
            this.btn_text = "Update Scholarship";
        
        axios.get(base_url + 'scholarship/data/' + this.id)
        .then((data) => {        
            if(data.data.scholarship)
                this.scholarship = data.data.scholarship;
            this.status_options = data.data.status_options;
            this.type_options = data.data.type_options;
        })
        .catch((error) => {
            console.log(error);
        })

    },

    methods: {    
        submitForm: function(){
            var formdata= new FormData();
            for (const [key, value] of Object.entries(this.scholarship)) {
                if(value != undefined)
                    formdata.append(key,value);
            }
            axios.post(base_url + 'scholarship/submit_form', formdata, {
            headers: {
                Authorization: `Bearer ${window.token}`
            }
            })
            .then((data) => {
                Swal.fire({
                    title: "Success",
                    text: data.data.message,
                    icon: "success"
                }).then(function() {                    
                    document.location = base_url + 'scholarship/view/' + data.data.id;
                });       
            })
        }
    }

})
</script>