<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Assign Scholarship/Discount                                 
        </h1>
        <ol class="breadcrumb">
            <li><a :href="base_url + 'scholarship/select_student'"><i class="fa fa-dashboard"></i>Select Student</a></li>
            <li class="active">Assign Scholarship</li>
        </ol>     
    </section>
        <hr />
    <div class="content"> 
        <div class="pull-right">
            <select @change="changeTerm($event)" class="form-control" v-model="current_sem">
                <option v-for="term in terms" :value="term.intID">{{ term.enumSem }} Term SY {{ term.strYearStart }} - {{ term.strYearEnd }}</option>
            </select>
        </div>
        <hr />
        <div class="box box-default">
            <div class="box-header">
                <h3>Scholarship</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    <div v-if="student" class="col-md-6">
                        Name: {{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}
                    </div>
                </div>
                <hr />
                <h4>Assign Scholarship</h4>
                <div class="row">                    
                    <form method="post" @click.prevent.stop="submitDeduction('scholarship')">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Scholarship</label>
                                <select required class="form-control" v-model="request_scholarship.discount_id">
                                    <option v-for="scholarship in scholarships" :value="scholarship.intID">{{ scholarship.name }}</option>
                                </select>                            
                            </div>
                            <hr />
                            <button class="btn btn-primary" type="submit">Add</button>
                        </div>
                        <div class="col-md-6">
                            <h4>Assigned Scholarships for this Term</h4>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Scholarship</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in student_scholarships">
                                        <td>{{ item.name }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>                    
                </div>
                <hr />
                
                <hr />
                <h4>Assign Discount</h4>
                <div class="row">                    
                    <form method="post" @click.prevent.stop="submitDeduction('discount')">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Discount</label>
                                <select required class="form-control" v-model="request_discount.discount_id">
                                    <option v-for="discount in discounts" :value="discount.intID">{{ discount.name }}</option>
                                </select>                            
                            </div>                        
                            <div class="form-group">
                                <label>Referrer Name</label>
                                <input type="text" class="form-control" v-model="request_discount.referrer" />                                
                            </div>                                                
                            <hr />
                            <button class="btn btn-primary" type="submit">Add</button>
                        </div>
                        <div class="col-md-6">
                            <h4>Assigned Discounts for this Term</h4>
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Discount</th>
                                        <th>Referrer</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in student_discounts">
                                        <td>{{ item.name }}</td>
                                        <td>{{ item.referrer }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>                    
                </div>
            </div>
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
        current_sem: '<?php echo $sem; ?>',
        student_id: <?php echo $student; ?>,
        scholarships:[],
        discounts:[],
        terms:[],
        student_scholarships:[],    
        student_discounts:[],    
        student: undefined,    
        request_scholarship:{
            discount_id: undefined,
            student_id: <?php echo $student; ?>,
            syid: this.current_sem,
            referrer: 'none',
        },
        request_discount:{
            discount_id: undefined,
            student_id: <?php echo $student; ?>,
            syid: this.current_sem,
            referrer: undefined,
        }
                      
    },

    mounted() {

        axios.get(this.base_url + 'scholarship/assign_scholarship_data/'+this.student_id+'/'+this.current_sem)
                .then((data) => {        
                    this.scholarships = data.data.scholarships;
                    this.discounts = data.data.discounts;
                    this.terms = data.data.terms;
                    this.student_scholarships = data.data.student_scholarships;
                    this.student_discounts = data.data.student_discounts;
                    this.student = data.data.student;
               
            })
            .catch((error) => {
                console.log(error);
            });
                        

    },

    methods: {      
       changeTerm: function(event){
         document.location = base_url + 'scholarship/assign_scholarship/' + event.target.value;
       },
       submitDeduction: function(type){
            var formdata= new FormData();
            if(type == "scholarship")
                for (const [key, value] of Object.entries(this.request_scholarship)) {
                    formdata.append(key,value);
                }
            else
                for (const [key, value] of Object.entries(this.request_discount)) {
                    formdata.append(key,value);
                }                                                    

            this.loader_spinner = true;
            axios.post(base_url + 'scholarship/add_scholarship', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then(data => {
                this.loader_spinner = false;
                Swal.fire({
                    title: data.data.success,
                    text: data.data.message,
                    icon: data.data.success,
                }).then(function() {
                    location.reload();
                });
            });
       }                                       
    }

})

$(document).ready(function(){     
});
</script>

