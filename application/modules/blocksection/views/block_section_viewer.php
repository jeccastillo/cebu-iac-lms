<aside id="vue-container" class="right-side">
    <section class="content-header container">
        <small>
            <a class="btn btn-app" :href="base_url + 'blocksection/view_block_sections'" ><i class="ion ion-eye"></i>View All Block Sections</a>                             
        </small>
    </section>
    <div class="content container">               
        <div class="box box-primary">
            <div class="box-header">
                <h4>Schedule for {{ section.name }}</h4>
            </div>
            <div class="box-body">
                <?php echo $sched_table; ?>
            </div>
        </div>
    </div>
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
        id: "<?php echo $id; ?>",
        section: {},
        sched_table: '',
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get(this.base_url + 'blocksection/block_section_viewer_data/' + this.id)
        .then((data) => {                                   
            this.section = data.data.section;
            var sched = data.data.schedule;                        
            load_schedule(sched);
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {     


    }

})
</script>