
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                Classlist
                <small>                                                                        
                </small>
            </h1>            
        </section>
        <hr />
        <div class="content">            
            <div class="box">                                
                <div class="box-header">
                    <h3 class="box-title">
                            {{ classlist.strCode + ' - ' + classlist.strClassName + ' ' + classlist.year + classlist.strSection + ' ' }}
                            <span v-if="classlist.sub_section">{{ classlist.sub_section }}</span>
                        <small>
                            {{ classlist.enumSem + ' ' + classlist.term_label + ' ' + classlist.strYearStart + '-' + classlist.strYearEnd }}
                        </small>
                    </h3>                    
                </div>
                <div class="box-body">
                    <table class="table table-striped">                        
                        <thead>
                            <tr>
                                <th v-if="is_super_admin"></th>                        
                                <th></th>
                                <th>Name</th>
                                <th>Program</th>                                
                                <th>MIDTERM GRADE</th>
                                <th>FINAL GRADE</th>                                                                                        
                                <th>Remarks</th>
                                <th>Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>                        
                            <tr v-for="(student,index) in students">                                    
                                <td v-if="is_super_admin"><input type="checkbox" class="student-select minimal" :value="student.intID" /></td>                                                                                    
                                <td>{{ index + 1 }}</td>
                                <td><a :href="base_url + 'unity/student_viewer/' + student.intID">{{ student.strLastname +' '+student.strFirstname+' '+student.strMiddlename }}</a></td>
                                <td>{{ student.strProgramCode }}</td>
                                <td v-if="student.registered">        
                                    <span v-if="student.floatMidtermGrade == 'OW' || student.floatFinalGrade == 'OW' || classlist.intFinalized >= 1 || ((cdate < classlist.midterm_start && cdate < classlist.midterm_end ) || (cdate > classlist.midterm_start && cdate > classlist.midterm_end )) && !is_super_admin">
                                        {{ (student.floatMidtermGrade && student.floatMidtermGrade != 50)?student.floatMidtermGrade:"NGS" }}
                                    </span>                                                                                                                 
                                    <select v-else @change="updateGrade($event,'midterm')"class="form-control">                              
                                        <option :selected="(!student.floatMidtermGrade || student.floatMidtermGrade == 50)? true : false"  value="NGS">NGS</option>                                        
                                        <option v-for="grading_item in grading_items_midterm" :selected="student.floatMidtermGrade === 'grading_item.value'? true : false"  :value="grading_item.value+'-'+grading_item.remarks">
                                            {{ grading_item.value }}
                                        </option>                                        
                                    </select>                                    
                                </td>                             
                                <td v-else></td>
                                <td v-if="student.registered">        
                                    <span v-if="student.floatMidtermGrade == 'OW' || student.floatFinalGrade == 'OW' || classlist.intFinalized >= 2 || ((cdate < classlist.final_start && cdate < classlist.final_end ) || (cdate > classlist.final_start && cdate > classlist.final_end )) && !is_super_admin">
                                        {{ (student.floatFinalGrade)?student.floatFinalGrade:"NGS" }}
                                    </span>                                                                                                                 
                                    <select v-else @change="updateGrade($event,'final')"class="form-control">                              
                                        <option :selected="(!student.floatFinalGrade)? true : false" value="NGS">NGS</option>                                        
                                        <option v-for="grading_item in grading_items" :selected="student.floatFinalGrade === 'grading_item.value'? true : false"  :value="grading_item.value+'-'+grading_item.remarks">
                                            {{ grading_item.value }}
                                        </option>                                        
                                    </select>                                    
                                </td>                             
                                <td v-else></td>                                   
                                <td>{{ student.strRemarks }}</td>
                                <td style="text-align:center;">
                                    {{ student.registered?'yes':'no' }}
                                </td>
                            </tr>
                        </tbody>                        
                    </table>
                </div>
            </div>
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
        base_url: '<?php echo base_url(); ?>',
        active_sem: undefined,
        cl: [],
        classlist:undefined,
        grading_items: [],
        grading_items_midterm:[],
        is_admin: false,
        is_registrar: false,
        is_super_admin: false,
        subject: undefined,        
        cdate: undefined,

    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        const current = new Date();         
        const date = current.getFullYear() + '-'
             + ('0' + (current.getMonth()+1)).slice(-2) + '-'
             + ('0' + current.getDate()).slice(-2);

        this.cdate = date;

        this.loader_spinner = true;

        axios.get(base_url + 'unity/classlist_viewer_data/'+this.id+'/'+this.show_all)
        .then((data) => {
            this.active_sem = data.data.active_sem;
            this.cl = data.data.cl;
            this.classlist = data.data.classlist;
            this.grading_items =  data.data.grading_items;
            this.grading_items_midterm =  data.data.grading_items_midterm;
            this.is_admin = data.data.is_admin;
            this.is_registrar = data.data.is_registrar;
            this.is_super_admin = data.data.is_super_admin;
            this.show_all = data.data.showall;
            this.students = data.data.students;
            this.subject = data.data.subject;
            
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {                
        updateGrade: function(event,period){

        }

    }

})
</script>