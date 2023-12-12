<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            User Group            
            <small>                
                <a class="btn btn-app" :href="base_url+'group/add_group'"><i class="fa fa-plus"></i>Add Group</a>                
            </small>
        </h1>
        <hr />
    </section>
        <hr />
    <div class="content">  
        <div class="box box-primary">
            <div class="box-header">
                <h3>Group</h3>
            </div>
            <div class="box-body">                
                <h4>User Groups</h4>                
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Group Name</th>                            
                            <th>Actions</th>                                                       
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in user_groups">                            
                            <td>{{ item.group_name  }}</td>
                            <td>
                                <a class="btn btn-primary" :href="base_url + 'group/add_group/' + item.id">View</a>
                            </td>
                            <td v-else></td>
                        </tr>
                    </tbody>
                </table>                              
            </div>        
        </div>
        
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {                    
        base_url: '<?php echo base_url(); ?>',               
        user_groups:[],        
        
        
    },

    mounted() {

        let url_string = window.location.href;        
                
            //this.loader_spinner = true;
            axios.get(this.base_url + 'group/group_view_data/')
                .then((data) => {                      
                    this.user_groups = data.data.user_groups;                                                         
                })
            .catch((error) => {
                console.log(error);
                
            });    

    },

    methods: {              
         
        
        
       
                                       
    }

})
</script>

