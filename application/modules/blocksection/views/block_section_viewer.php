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
            <div v-html="sched_table" class="box-body">
                
            </div>
        </div>
    </div>
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
            this.sched_table = data.data.sched_table;     
            for(i in sched){
                
                let day = sched[i].strDay;
                let text = sched[i].strCode;
                let hourspan = sched[i].hourdiff * 2;
                let st = sched[i].st;
                
                $("#"+st+" :nth-child("+day+")").addClass("bg-teal");
                $("#"+st+" :nth-child("+day+")").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                $("#"+st+" :nth-child("+day+")").html("<div style='text-align:center;'>"+text+"</div>");
                nxt = $("#"+st);
                nxt.next().children(":nth-child("+day+")").html("<div style='text-align:center;'></div>");
                for(i=1;i<hourspan;i++){                    
                    nxt.next().children(":nth-child("+day+")").addClass("bg-teal");
                    if(i==hourspan-1)
                    nxt.next().children(":nth-child("+day+")").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                    else
                        nxt.next().children(":nth-child("+day+")").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
                    
                    nxt = nxt.next();
                }
                $("#sched-table").val($("#sched-table-container").html());                                                        
            }
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {     


    }

})
</script>