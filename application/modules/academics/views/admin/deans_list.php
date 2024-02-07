<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
           Dean's Listers            
        </h1>
        <div class="box-tools pull-right">
            <label>Term</label>
            <select v-model="term" @change="changeTermSelected" class="form-control" >
                <option v-for="s in sy" :value="s.intID">{{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
            </select>   
        </div>
        <div class="box-tools pull-right">
            <label>Period</label>
            <select v-model="period" @change="changeTermSelected" class="form-control" >
                <option value="0">Midterm</option>                      
                <option value="1">Final</option>                      
            </select>                
        </div>
        <hr />
    </section>
        <hr />
    <div v-if="!loading" class="content">
        <div v-if="list1.length > 0 && list2.length > 0">
            <h4>1st Honors</h4>
            <table v-if="list1.length > 0" class="table table-striped">
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>GWA</th>
                    </tr>                
                </thead>
                <tbody>
                    <tr v-for="st in list1">
                        <td>{{ st.strStudentNumber }}</td>
                        <td>{{ st.strLastname }}</td>
                        <td>{{ st.strFirstname }}</td>
                        <td>{{ st.gwa }}</td>                    
                    </tr>
                </tbody>
                
            </table>
            <h4>2nd Honors</h4>
            <table v-if="list2.length > 0" class="table table-striped">
                <thead>
                    <tr>
                        <th>Student Number</th>
                        <th>Last Name</th>
                        <th>First Name</th>
                        <th>GWA</th>
                    </tr>                
                </thead>
                <tbody>
                    <tr v-for="st in list2">
                        <td>{{ st.strStudentNumber }}</td>
                        <td>{{ st.strLastname }}</td>
                        <td>{{ st.strFirstname }}</td>
                        <td>{{ st.gwa }}</td>                    
                    </tr>
                </tbody>
                
            </table>
        </div>
        <div v-else>
            <h3>Nothing to Display</h3>
        </div>
    </div>    
    <div v-else class="content">             
        <h4>Loading Data Please Wait...</h4>
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
var special = ['0th','1st', '2nd', '3rd', '4th', '5th'];

function stringifyNumber(n) {
  return special[n];
  
  
}
function inArray(needle, haystack) {
    var length = haystack.length;
    for(var i = 0; i < length; i++) {
        if(haystack[i] == needle) return true;
    }
    return false;
}
new Vue({
    el: '#registration-container',
    data: {        
        base_url: '<?php echo base_url(); ?>',
        list1: [],
        list2: [],
        term: '<?php echo $term; ?>',
        period: '<?php echo $period; ?>', 
        sy: [],
        loading: true,
    },
    computed: {
        sortedData: function() {
            return this.list1.sort(function(a, b) {
                return a.gwa - b.gwa;
            });
        },
        sortedData2: function() {
            return this.list2.sort(function(a, b) {
                return a.gwa - b.gwa;
            });
        }
    },
    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'academics/deans_listers_data/' + this.term + '/' + this.period)
                .then((data) => {                                          
                    this.loading = false; 
                    this.list1 = data.data.list_1st_honor;
                    this.list2 = data.data.list_2nd_honor;
                    this.sy = data.data.sy;         
                         
                })
                .catch((error) => {
                    console.log(error);
                })
        }

    },

    methods: {   
        changeTermSelected: function(){
            document.location = this.base_url + "academics/deans_listers/" + 
            this.term + '/' + this.period;
        }, 
    }

})
</script>

