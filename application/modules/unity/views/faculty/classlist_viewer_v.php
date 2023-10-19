
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                Classlist
                <small>                     
                    <a class="btn btn-app" v-if="(is_super_admin || is_registrar) && show_all"  :href="base_url + 'unity/classlist_viewer/' + classlist.intID +'/0/'+sid"><i class="fa fa-times"></i> Hide Enlisted</a></li>                    
                    <a class="btn btn-app" v-else-if="(is_super_admin || is_registrar)" :href="base_url + 'unity/classlist_viewer/' + classlist.intID +'/1/'+sid"><i class="fa fa-check"></i> Show Enlisted</a>                                            
                    <a class="btn btn-app" v-if="is_super_admin || is_registrar" :href="base_url + 'unity/edit_classlist/'+ classlist.intID"><i class="fa fa-gear"></i> Edit</a>                    
                    <a class="btn btn-app" v-if="is_super_admin || is_registrar" :href="base_url + 'excel/download_classlist/'+ classlist.intID + '/' + show_all"><i class="fa fa-table"></i> Download Spreadsheet</a>                
                    <a target="_blank" class="btn btn-app" :href="base_url + 'pdf/print_classlist_registrar/' + classlist.intID +'/front'"><i class="fa fa-print"></i>Print Classlist</a>
                    <a class="btn btn-app" v-if="classlist.intFinalized > 0" target="_blank" :href="base_url + 'pdf/grading_sheet/' + classlist.intID"><i class="fa fa-print"></i> Print Grading Sheet</a>                                            
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
                            {{ classlist.enumSem + ' ' + classlist.term_label + ' ' + classlist.strYearStart + '-' + classlist.strYearEnd }} <br />
                            <strong>{{ classlist.strFirstname+" "+classlist.strLastname}}</strong>
                        </small>
                    </h3>                    
                </div>
                <div class="box-body">
                    <table class="table table-bordered">                        
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
                                <th v-if="pre_req.length > 0">Passed Pre-requisite(s)</th>
                            </tr>
                        </thead>
                        <tbody>                        
                            <tr :style="sid == student.intID?'background-color:#ccc;':''" v-for="(student,index) in students" v-if="show_all || student.registered">                                    
                                <td v-if="is_super_admin"><input type="checkbox" v-model="checked" :value="student.intID" /></td>                                                                                    
                                <td >{{ index + 1 }}</td>
                                <td ><a :href="base_url + 'unity/student_viewer/' + student.intID">{{ student.strLastname +' '+student.strFirstname+' '+student.strMiddlename }}</a></td>
                                <td >{{ student.strProgramCode }}</td>
                                <td  v-if="student.registered">        
                                    <span v-if="(student.floatMidtermGrade == 'OW' || student.floatFinalGrade == 'OW' || classlist.intFinalized != 0 || ((cdate < classlist.midterm_start && cdate < classlist.midterm_end ) || (cdate > classlist.midterm_start && cdate > classlist.midterm_end ))) && !is_super_admin || classlist.intFinalized == 2">
                                        {{ (student.floatMidtermGrade && student.floatMidtermGrade != 50)?student.floatMidtermGrade:"NGS" }}
                                    </span>                                                                                                                 
                                    <select v-else @change="updateGrade($event,'midterm',student.intCSID)" class="form-control" >                              
                                        <option :selected="(!student.floatMidtermGrade || student.floatMidtermGrade == 50)? true : false"  value="NGS">NGS</option>                                        
                                        <option v-for="grading_item in grading_items_midterm" :selected="student.floatMidtermGrade == grading_item.value"  :value="grading_item.value+'-'+grading_item.remarks">
                                            {{ grading_item.value }}
                                        </option>                                        
                                    </select>                                    
                                </td>                             
                                <td  v-else></td>
                                <td  v-if="student.registered">        
                                    <span v-if="(student.floatMidtermGrade == 'OW' || student.floatFinalGrade == 'OW' || classlist.intFinalized != 1 || ((cdate < classlist.final_start && cdate < classlist.final_end ) || (cdate > classlist.final_start && cdate > classlist.final_end ))) && !is_super_admin">
                                        {{ (student.floatFinalGrade)?student.floatFinalGrade:"NGS" }}
                                    </span>                                                                                                                 
                                    <select v-else @change="updateGrade($event,'final',student.intCSID)"class="form-control">                              
                                        <option :selected="(!student.floatFinalGrade)? true : false" value="NGS">NGS</option>                                        
                                        <option v-for="grading_item in grading_items" :selected="student.floatFinalGrade == grading_item.value"  :value="grading_item.value+'-'+grading_item.remarks">
                                            {{ grading_item.value }}
                                        </option>                                        
                                    </select>                                    
                                </td>                             
                                <td  v-else></td>                                   
                                <td >{{ student.strRemarks }}</td>
                                <td  class="text-left">
                                    {{ student.registered?'yes':'no' }}
                                </td>
                                <td :style="student.pre_req_passed?'color:#009900':'color:#990000'" v-if="pre_req.length > 0">{{ student.pre_req_passed?'yes':'no' }}</td>
                            </tr>
                        </tbody>                        
                    </table>
                    <div class="box-footer">                        
                        <div class="row">
                            <form v-if="classlist.intFinalized == 0 && (is_super_admin || is_registrar)" method="post" @submit.prevent="transferToClasslist">
                                <div class="col-sm-2">
                                    <button type="submit" class="btn btn-warning btn-block">Transfer to <i class="fa fa-arrow-right"></i></button>
                                </div>
                                <div class="col-sm-4">
                                    <select required v-model="transfer_to" class="form-control">
                                        <option v-for="c in cl" :value="c.intID">{{ c.strClassName + " " + c.year + c.strSection + " " + (c.sub_section?c.sub_section:'') }}</option>                                
                                    </select>
                                </div>                                
                            </form>
                            <div class="col-sm-4">
                                <a v-if="classlist.intFinalized < 2 && !disable_submit" href="#" data-target="#myModal" data-toggle="modal" class="btn btn-success">
                                    {{ label }}
                                </a>
                                <a v-if="classlist.intFinalized > 0 && (is_super_admin || is_registrar)" @click.prevent="unfinalize" href="#" class="btn btn-danger">
                                    Unfinalize
                                </a>
                            </div>
                        </div>  
                        <hr />
                        <div class="row">
                            <div v-html="legend" class="col-md-4">
                                          
                            </div>
                        </div>    
                    </div>
                </div>
            </div>
        </div><!---content container--->
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Review Grades</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">                        
                        <thead>
                            <tr>                                                    
                                <th></th>
                                <th>Name</th>
                                <th>Program</th>                                
                                <th v-if="classlist.intFinalized == 0">MIDTERM GRADES</th>
                                <th v-else>FINAL GRADES</th>  
                                <th>Remarks</th>                                                                                                                 
                            </tr>
                        </thead>
                        <tbody>                        
                            <tr v-for="(student,index) in students">                                                                
                                <td>{{ index + 1 }}</td>
                                <td>{{ student.strLastname +' '+student.strFirstname+' '+student.strMiddlename }}</td>
                                <td>{{ student.strProgramCode }}</td>
                                <td v-if="classlist.intFinalized == 0">                                        
                                    {{ (student.floatMidtermGrade && student.floatMidtermGrade != 50)?student.floatMidtermGrade:"NGS" }}                                                                           
                                </td>                             
                                <td v-else> {{ (student.floatFinalGrade)?student.floatFinalGrade:"NGS" }}</td>                                                                                  
                                <td>{{ student.strRemarks }}</td>                                
                            </tr>
                        </tbody>                        
                    </table>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="button" :disabled="disable_submit" @click="finalizePeriod" class="btn btn-primary">Finalize</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
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
        sid: <?php echo $sid; ?>,
        base_url: '<?php echo base_url(); ?>',
        active_sem: undefined,
        cl: [],
        checked: [],
        transfer_to: undefined,
        classlist:undefined,
        grading_items: [],
        grading_items_midterm:[],
        pre_req: undefined,
        is_admin: false,
        is_registrar: false,
        is_super_admin: false,
        subject: undefined,        
        cdate: undefined,
        label: 'Submit',
        disable_submit: true,
        legend: undefined,

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

        axios.get(base_url + 'unity/classlist_viewer_data/'+this.id+'/'+this.show_all+'/'+this.sid)
        .then((data) => {
            this.active_sem = data.data.active_sem;
            this.legend = data.data.legend;
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
            this.label = data.data.label;
            this.pre_req =  data.data.pre_req;
            this.disable_submit = data.data.disable_submit;
            
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {                
        updateGrade: function(event,period,csid){            

            
            var type = 3;
            var formdata= new FormData();
            formdata.append("intCSID",csid);
            var values = event.target.value.split("-");
            formdata.append("strRemarks",values[1]);
            if(period == 'midterm'){
                formdata.append("floatMidtermGrade",values[0]);
                type = 2;
            }
            else
                formdata.append("floatFinalGrade",values[0]);
            

            this.loader_spinner = true;
            if(this.classlist.intFinalized == 2){
                Swal.fire({
                    title: 'Change Grade?',
                    text: "Are you sure you want to change student grade?",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    imageWidth: 100,
                    icon: "question",
                    cancelButtonText: "No, cancel!",
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        axios.post(base_url + 'unity/update_grade/'+type, formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.loader_spinner = false;
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        });
                    
                    }
                    
                });                
            }
            else{
                axios.post(base_url + 'unity/update_grade/'+type, formdata, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {
                    this.loader_spinner = false;
                    Swal.fire({
                        title: "Success",
                        text: data.data.message,
                        icon: "success"
                    }).then(function() {
                        location.reload();
                    });
                });
            }
        },
        finalizePeriod: function(){
            var complete_grades = true;
            for(i in this.students){
                if(this.classlist.intFinalized == 0){
                    if(!this.students[i].floatMidtermGrade || this.students[i].floatMidtermGrade == 50 || this.students[i].floatMidtermGrade == "NGS")
                        complete_grades = false;
                }
                else
                    if(!this.students[i].floatFinalGrade || this.students[i].floatFinalGrade == 50 || this.students[i].floatFinalGrade == "NGS")
                        complete_grades = false;
            }
            if(complete_grades)
                Swal.fire({
                    title: 'Submit Grades?',
                    text: "Are you sure you want to submit?",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    imageWidth: 100,
                    icon: "question",
                    cancelButtonText: "No, cancel!",
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {
                        var formdata= new FormData();
                        formdata.append("intID",this.classlist.intID);
                        formdata.append("intFinalized",this.classlist.intFinalized);
                        return axios.post(base_url + 'unity/finalize_term', formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.loader_spinner = false;
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        });                    
                    }
                });
            else
                Swal.fire({
                    title: "Warning",
                    text: "Complete grades before submitting",
                    icon: "warning"
                }).then(function() {                    
                });
        },
        unfinalize: function(){
            Swal.fire({
                title: 'Unfinalize Period?',
                text: "Are you sure you want to unfinalize?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata= new FormData();
                    formdata.append("intID",this.classlist.intID);
                    formdata.append("intFinalized",this.classlist.intFinalized);
                    return axios.post(base_url + 'unity/unfinalize_term', formdata, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    })
                    .then(data => {
                        this.loader_spinner = false;
                        Swal.fire({
                            title: "Success",
                            text: data.data.message,
                            icon: "success"
                        }).then(function() {
                            location.reload();
                        });
                    });                    
                }
            });
        },
        transferToClasslist: function(){
            if(this.checked.length == 0)
            {
                Swal.fire({
                    title: "Warning",
                    text: "Please check at least one student",
                    icon: "warning"
                }).then(function() {                    
                });                
            }
            else{                                           
                Swal.fire({
                        title: 'Transfer Students?',
                        text: "Are you sure you want to transfer? Warning: Transferring students will reset their grade and remarks.",
                        showCancelButton: true,
                        confirmButtonText: "Yes",
                        imageWidth: 100,
                        icon: "question",
                        cancelButtonText: "No, cancel!",
                        showCloseButton: true,
                        showLoaderOnConfirm: true,
                        preConfirm: (login) => {
                            console.log(this.checked);
                            var formdata= new FormData();
                            formdata.append("transferTo",this.transfer_to);  
                            
                            for(i in this.checked)                          
                                formdata.append("students[]",this.checked[i]);

                            formdata.append("classlistFrom",this.classlist.intID);
                            
                            return axios.post(base_url + 'unity/transfer_classlist', formdata, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                            .then(data => {
                                this.loader_spinner = false;
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            });                    
                        }
                    });
            }
        },

    }

})
</script>