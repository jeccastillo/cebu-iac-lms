
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>Block Section</h1>
        </section>
        <hr />
        <div class="content">
            <div class="row">                                        
                <div class="col-sm-12">                    
                </div><!---column--->
            </div><!---row--->
        </div><!---content container--->
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
        base_url: "<?php echo base_url(); ?>",
        id: <?php echo $id; ?>,
        payments:[],               
        data: {},
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get(this.base_url + 'blocksection/block_section_data/' + this.id)
        .then((data) => {           
            this.data = data.data;
            console.log(data.data);
            
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        


    }

})
</script>