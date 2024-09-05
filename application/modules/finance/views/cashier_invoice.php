
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app" :href="base_url + 'unity/logs/0/0/Cashier'" ><i class="fa fa-file" aria-hidden="true"></i>View Cashier Logs</a>                                                                                                                              
                </small>
            </h1>
        </section>
        <hr />
        <div class="content">                        
                <div class="col-sm-12">
                    <div class="box box-solid box-success">
                        <div class="box-header">                            
                            <h4 class="box-title">Cashiers</h4>                            
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Cashier #</th>
                                    <th>Name</th>
                                    <th>Invoice Start</th>
                                    <th>Invoice End</th>
                                    <th>Update</th>
                                    <th>Current Invoice</th>          
                                </tr>    
                                <tr v-for="(cashier,index) in cashiers">
                                    <td>Cashier {{ cashier.intID }}</td>
                                    <td>{{ cashier.strFirstname + " " + cashier.strLastname }}</td>                                    
                                    <td>
                                        <input type="number" :ref="'invoice_start'+cashier.intID" :value="cashier.invoice_start" />                                                                                        
                                    </td>
                                    <td>
                                        <input type="number" :ref="'invoice_end'+cashier.intID" :value="cashier.invoice_end" />
                                        
                                    </td>
                                    <td><a href="#" @click.prevent.stop="changeValue(cashier.intID)">change</a></td>
                                    <td>
                                        {{ cashier.invoice_current }}
                                    </td>                                               
                                </tr>                                 
                            </table>
                            <hr />                                    
                        </div><!---box body--->
                    </div><!---box--->                      
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
        cashiers: [],
        finance_users: [],
        edit_value: "",
        not_edit_mode: true,
        edit_text:"Turn on Edit Mode",
        edit_class:"btn-primary",
        request:{
            user_id: undefined,            
        },        
             
    },

    mounted() {
        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;
        axios.get(base_url + 'finance/cashier_data/')
        .then((data) => {
            this.cashiers = data.data.cashiers;
            this.finance_users = data.data.finance_users;              
        })
        .catch((error) => {
            console.log(error);
        })
    },

    methods: {
        enableField: function(id){
            document.getElementById(id).disabled = false;
            document.getElementById(id).focus();
        },
        changeValue: function(id){
            
            var formdata = new FormData();
            formdata.append('intID',id);            
            formdata.append('start',this.$refs['invoice_start'+id][0].value);
            formdata.append('end',this.$refs['invoice_end'+id][0].value);                       
            axios
            .post(base_url + 'finance/update_cashier_invoice', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {                
                if (data.data.success) {
                    Swal.fire(
                        'Updated',
                        data.data.message,
                        'success'
                    ).then(function(){
                        if(data.data.reload)
                            location.reload();
                    });
                } else {
                    Swal.fire(
                        'Failed!',
                        data.data.message,
                        'error'
                    ).then(function() {
                        location.reload();
                    });
                }
                
            });
        }


    }

})
</script>