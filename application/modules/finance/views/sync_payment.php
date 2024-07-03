
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>Sync Payment Details
                <small>                    
                </small>
            </h1>
        </section>
        <hr />
        <div class="content">                                    
            <button class="btn btn-lg" @click="syncFromApi">Sync Payment Details</button>
        </div><!---content container--->       
    </div><!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {        
        
        base_url: "<?php echo base_url(); ?>",  
        request: { 
                    last_id: undefined,   
                 }
    },

    mounted() {
        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(base_url + 'finance/sync_payment_details_data')
        .then((data) => {
            if(data.data.payee)
                this.request.last_id = data.data.last_id;           
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {                       
        syncFromApi: function(){                       

            axios
            .post(api_url + 'finance/get_payment_details', this.request, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {                                
                console.log(data);                
            });
        },
        


    }

})
</script>