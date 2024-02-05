<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Records
            <small>
                <a class="btn btn-app" :href="base_url + 'academics/student_viewer/' + student.intID"><i class="ion ion-arrow-left-a"></i>All Details</a>                                 
            </small>
            
        </h1>
        <div class="box-tools pull-right">
            <select v-model="term" @change="changeTermSelected" class="form-control" >
                <option v-for="s in sy" :value="s.intID">{{s.term_student_type + ' ' + s.enumSem + ' ' + s.term_label + ' ' + s.strYearStart + '-' + s.strYearEnd}}</option>                      
            </select>                   
        </div>
        <hr />
    </section>
        <hr />
    <div class="content">             
        
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
        id: '<?php echo $id; ?>',    
        base_url: '<?php echo base_url(); ?>',
        list: [],
        term: '<?php echo $term; ?>',
        period: '<?php echo $period; ?>', 
        sy: [],
    },

    mounted() {

        let url_string = window.location.href;        
        if(this.id != 0){            
            //this.loader_spinner = true;
            axios.get(this.base_url + 'academics/deans_listers_data/' + this.term + '/' + this.period)
                .then((data) => {                                          
                    this.list = data.data.list;
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
            this.term;
        },   
    }

})
</script>

