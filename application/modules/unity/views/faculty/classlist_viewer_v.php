
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>                                                                        
                </small>
            </h1>


        </section>
        <hr />
        <div class="content">            
                
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
        id: <?php echo $id; ?>,
        show_all: <?php echo $showAll; ?>,
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get(base_url + 'unity/classlist_viewer_data/'+this.id+'/'+this.show_all)
        .then((data) => {
           

            
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {                


    }

})
</script>