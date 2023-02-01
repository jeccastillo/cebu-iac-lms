
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
                        <div class="col-sm-6 text-right">
                            Name:
                        </div>
                        <div class="col-sm-6">
                            <input type="text" v-model="scholarship.name" class="form-control">                            
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 text-right">
                            Description:
                        </div>
                        <div class="col-sm-6">
                            {{ scholarship.description }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 text-right">
                            Type:
                        </div>
                        <div class="col-sm-6">
                            {{ scholarship.type }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 text-right">
                            Status:
                        </div>
                        <div class="col-sm-6">
                            {{ scholarship.status }}
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
        },
             
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(base_url + 'scholarship/data/' + this.id)
        .then((data) => {
           console.log(data);
           this.scholarship = data.data.scholarship;
        })
        .catch((error) => {
            console.log(error);
        })

    },

    methods: {        
    }

})
</script>