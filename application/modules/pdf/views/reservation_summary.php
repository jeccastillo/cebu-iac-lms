<aside class="right-side" id="registration-container">    
    
    <table ref="ret" v-if="reserved" class="table table-bordered table-striped">
        <tr>
            <th>Program</th>
            <th>Freshman</th>
            <th>Transferee</th>
            <th>Foreign</th>
            <th>Second Degree</th>
            <th>Total</th>
        </tr>
        <tr v-for="prog in reserved">
            <td>{{ prog[0].program }}</td>
            <td v-for="type in prog" v-if="type.student_type == 'freshman'">
                {{ type.reserved_count }}
            </td>
            <td v-if="r_fresh[prog[0].type_id] == false">
                0
            </td>
            <td v-for="type in prog" v-if="type.student_type == 'transferee'">
                {{ type.reserved_count }}
            </td>
            <td v-if="r_trans[prog[0].type_id] == false">
                0
            </td>
            <td v-for="type in prog" v-if="type.student_type == 'foreign'">
                {{ type.reserved_count }}
            </td>
            <td v-if="r_foreign[prog[0].type_id] == false">
                0
            </td>
            <td v-for="type in prog" v-if="type.student_type == 'second degree'">
                {{ type.reserved_count }}
            </td>
            <td v-if="r_sd[prog[0].type_id] == false">
                0
            </td>
            <td>
                {{ totals[prog[0].type_id] }}
            </td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td><strong>{{ all_reserved }}</strong></td>
        </tr>
    </table>            

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
        current_sem: '<?php echo $sem; ?>',
        reserved: undefined,
        enrolled: undefined,
        totals: [],
        r_fresh: [],
        r_trans: [],
        r_foreign: [],
        r_sd: [],
        programs: undefined,
        all_reserved: 0,
        all_enrolled: 0,
                      
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'registrar/enrollment_summary_data/')
                .then((data) => {  
                   this.enrolled = data.data.data;
                   for(i in this.enrolled){
                        this.all_enrolled +=  this.enrolled[i].enrolled_freshman + this.enrolled[i].enrolled_foreign + this.enrolled[i].enrolled_second + this.enrolled[i].enrolled_transferee;
                   }                   
                   this.programs = data.data.programs;
                   axios.get(api_url + 'admissions/applications/stats?current_sem='+this.current_sem)
                    .then((data) => {  
                        this.reserved = data.data;                         
                        for(i in this.reserved){       
                            this.r_fresh[i] = false;
                            this.r_trans[i] = false;
                            this.r_foreign[i] = false;
                            this.r_sd[i] = false;
                            this.totals[this.reserved[i][0].type_id] = 0;                                         
                            for(j in this.reserved[i]){     
                                if(this.reserved[i][j].student_type == "freshman")
                                    this.r_fresh[i] = true;
                                if(this.reserved[i][j].student_type == "transferee")
                                    this.r_trans[i] = true;
                                if(this.reserved[i][j].student_type == "foreign")
                                    this.r_foreign[i] = true;
                                if(this.reserved[i][j].student_type == "second degree")
                                    this.r_sd[i] = true;

                                this.totals[this.reserved[i][j].type_id] += parseInt(this.reserved[i][j].reserved_count);
                                this.all_reserved += parseInt(this.reserved[i][j].reserved_count);
                            }
                        }

                        console.log(this.totals);                   
                    })
                    .catch((error) => {
                        console.log(error);
                    });
                })
            .catch((error) => {
                console.log(error);
                
            });
        }

    },

    methods: {      
       
                                       
    }

})
</script>

