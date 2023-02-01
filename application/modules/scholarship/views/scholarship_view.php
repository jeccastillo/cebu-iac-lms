
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
                    <h3>{{ scholarship.name }}</h3>
                </div>
                <div class="box-body">
                    <div class="row">                        
                        <div class="col-md-6">
                            <label>Name:</label>
                            <input type="text" v-model="scholarship.name" class="form-control">                            
                        </div>                        
                        <div class="col-md-6">
                            <label>Description:</label>
                            <textarea v-model="scholarship.description" class="form-control"></textarea>                            
                        </div>
                        <div class="col-md-6">
                            <label>Type:</label>
                            <select type="text" v-model="scholarship.type" class="form-control">
                                <option v-for="type in type_options" :value="type">{{ type }}</option>                         
                            </select>
                        </div>                        
                        <div class="col-md-6">
                            <label>Status:</label>
                            <select type="text" v-model="scholarship.status" class="form-control">    
                                <option v-for="status in status_options" :value="status">{{ status }}</option>                        
                            </select>
                        </div>
                    </div>   
                    <hr />
                    <div class="row">
                        <div class="col-md-4">
                            <div class="row">                                                
                                <div class="col-md-12 text-center"><strong>Tuition Fee</strong></div>
                                <div class="col-md-6">
                                    <label>Rate:</label>
                                    <input type="number" @keypress="checkVal($event)" min=0 max=100 step=1 v-model="scholarship.tuition_fee_rate" class="form-control">
                                </div>                        
                                <div class="col-md-6">
                                    <label>Fixed:</label>
                                    <input type="number" v-model="scholarship.tuition_fee_fixed" class="form-control">
                                </div>                        
                            </div>           
                        </div>
                        <div class="col-md-4">
                            <div class="row">                                                
                                <div class="col-md-12 text-center"><strong>Basic Fee</strong></div>
                                <div class="col-md-6">
                                    <label>Rate:</label>
                                    <input type="number" @keydown="checkVal($event)" min=0 max=100 step=1 v-model="scholarship.basic_fee_rate" class="form-control">
                                </div>                        
                                <div class="col-md-6">
                                    <label>Fixed:</label>
                                    <input type="number" v-model="scholarship.basic_fee_fixed" class="form-control">
                                </div>                        
                            </div>           
                        </div>
                        <div class="col-md-4">
                            <div class="row">                                                
                                <div class="col-md-12 text-center"><strong>Miscellaneous Fee</strong></div>
                                <div class="col-md-6">
                                    <label>Rate:</label>
                                    <input type="number" @keypress="checkVal($event)" min=0 max=100 step=1 v-model="scholarship.misc_fee_rate" class="form-control">
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
        </div>                
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {        
        id: "<?php echo $id; ?>",
        base_url: "<?php echo base_url(); ?>",   
        scholarship: {
            name: undefined,
            description: undefined,
            status: undefined,
            type: undefined,
            created_by_id: undefined,
            tuition_fee_rate: undefined,
            tuition_fee_fixed: undefined,
            basic_fee_rate: undefined,
            basic_fee_fixed: undefined,
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
        axios.get(base_url + 'scholarship/data/' + this.id)
        .then((data) => {
           console.log(data);
           this.scholarship = data.data.scholarship;
           this.status_options = data.data.status_options;
           this.type_options = data.data.type_options;
        })
        .catch((error) => {
            console.log(error);
        })

    },

    methods: {    
        checkVal: function(event){
            if(event.target.value < 0)
                event.target.value = 0;
            if(event.target.value > 100)
                event.target.value = 100;
        }    
    }

})
</script>