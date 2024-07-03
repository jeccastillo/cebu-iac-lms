
<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app" href="<?php echo base_url() ?>student/view_all_students" ><i class="ion ion-arrow-left-a"></i>All Students</a> 
                    <a class="btn btn-app trash-student-record2" rel="<?php echo $student['intID']; ?>" href="#"><i class="ion ion-android-close"></i> Delete</a>   
                    <a class="btn btn-app" href="<?php echo base_url()."student/edit_student/".$student['intID']; ?>"><i class="ion ion-edit"></i> Edit</a> 
                                    
                                    
                </small>
            </h1>


        </section>
        <hr />
        <div class="content">
            <div class="row">
                <div class="col-sm-12">
                    <div class="box box-widget widget-user-2">
                        <!-- Add the bg color to the header using any of the bg-* classes -->
                        <div class="widget-user-header bg-red">
                            <!-- /.widget-user-image -->
                            <h3 class="widget-user-username" style="text-transform:capitalize;margin-left:0;font-size:1.3em;">{{ student.strLastname }}, {{ student.strFirstname }} {{ student.strMiddlename }}</h3>
                            <h5 class="widget-user-desc" style="margin-left:0;">{{ student.strProgramDescription }} {{ (student.strMajor != 'None')?'Major in '+student.strMajor:'' }}</h5>
                        </div>
                        <div class="box-footer no-padding">
                            <ul class="nav nav-stacked">
                            <li><a href="#" style="font-size:13px;">Student Number <span class="pull-right text-blue">{{ student.strStudentNumber }}</span></a></li>
                            <li><a href="#" style="font-size:13px;">Curriculum <span class="pull-right text-blue">{{ student.strName }}</span></a></li>
                            <li><a style="font-size:13px;" href="#">Registration Status <span class="pull-right">{{ reg_status }}</span></a></li>
                            <li>
                                <a style="font-size:13px;" href="#">Date Registered <span class="pull-right">
                                    <span style="color:#009000" v-if="registration" >{{ registration.date_enlisted }}</span>
                                    <span style="color:#900000;" v-else>N/A</span>                                
                                </a>
                            </li>
                            <li v-if="registration"><a style="font-size:13px;" href="#">Scholarship Type <span class="pull-right">{{ registration.scholarshipName }}</span></a></li>
                                
                            </ul>
                        </div>
                    </div>                   
                </div>                            
                <div class="col-sm-12">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li v-if="advanced_privilages">
                                <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + selected_ay + '/tab_1'">
                                    Personal Information
                                </a>
                            </li>                            
                            <li v-if="advanced_privilages">
                                <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + selected_ay + '/tab_2'">                            
                                    Report of Grades
                                </a>
                            </li>
                            <li v-if="advanced_privilages">
                                <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + selected_ay + '/tab_3'">                            
                                    Assessment
                                </a>
                            </li>
                            
                            <li v-if="advanced_privilages">
                                <a :href="base_url + 'unity/student_viewer/' + student.intID + '/' + selected_ay + '/tab_5'">                            
                                    Schedule
                                </a>
                            </li>
                            
                            <li>
                                <a :href="base_url + 'unity/registration_viewer/' + student.intID + '/' + selected_ay">                                
                                Finance
                                </a>
                            </li>
                            <li class="active"><a href="#tab_1" data-toggle="tab">Accounting Summary</a></li>
                        </ul>                    
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1">            
                                <div class="box box-solid box-success">
                                    <div class="box-header">                            
                                        <h4 class="box-title">Transactions</h4>
                                    </div>
                                    <div class="box-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th>OR Number</th>
                                                <th>Payment Type</th>
                                                <th>Amount Paid</th>
                                                <th>Online Payment Charge</th>
                                                <th>Total Due</th>
                                                <th>Status</th>
                                                <th>Online Response Message</th>
                                                <th>Date Updated</th>
                                            </tr>    
                                            <tr v-if="application_payment">
                                                <td>{{ application_payment.or_number }}</td>
                                                <td>{{ application_payment.description }}</td>
                                                <td>{{ application_payment.subtotal_order }}</td>
                                                <td>{{ application_payment.charges }}</td>
                                                <td>{{ application_payment.total_amount_due }}</td>
                                                <td>{{ application_payment.status }}</td>                                            
                                                <td>{{ application_payment.response_message }}</td>
                                                <td>{{ application_payment.updated_at }}</td>                                                
                                            </tr> 
                                            <tr v-if="reservation_payment">
                                                <td>{{ reservation_payment.or_number }}</td>
                                                <td>{{ reservation_payment.description }}</td>
                                                <td>{{ reservation_payment.subtotal_order }}</td>
                                                <td>{{ reservation_payment.charges }}</td>
                                                <td>{{ reservation_payment.total_amount_due }}</td>
                                                <td>{{ reservation_payment.status }}</td>
                                                <td>{{ reservation_payment.response_message }}</td>
                                                <td>{{ reservation_payment.updated_at }}</td>
                                            </tr>
                                            <tr>
                                            <th colspan="7">
                                                Other Payments:
                                                </th>
                                            </tr>  
                                            <tr v-for="payment in other_payments">
                                                <td>{{ payment.or_number }}</td>
                                                <td>{{ payment.description }}</td>
                                                <td>{{ payment.subtotal_order }}</td>
                                                <td>{{ payment.charges }}</td>
                                                <td>{{ payment.total_amount_due }}</td>
                                                <td>{{ payment.status }}</td>                                            
                                                <td>{{ payment.updated_at }}</td>
                                            </tr>    
                                            <tr>
                                                <th colspan="7">
                                                Tuition Payments:
                                                </th>
                                            </tr>
                                            <tr v-for="payment in payments">
                                                <td>{{ payment.or_number }}</td>
                                                <td>{{ payment.description }}</td>
                                                <td>{{ payment.subtotal_order }}</td>
                                                <td>{{ payment.charges }}</td>
                                                <td>{{ payment.total_amount_due }}</td>
                                                <td>{{ payment.status }}</td>
                                                <td>{{ payment.response_message }}</td>
                                                <td>{{ payment.updated_at }}</td>
                                            </tr>           
                                            <tr>
                                                <td colspan="3">
                                                Total Tuition: P{{ total_formatted }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="" colspan="3">
                                                remaining balance: P{{ remaining_amount_formatted }}
                                                </td>
                                            </tr>
                                        </table>

                                        <hr />                                    
                                    </div><!---box body--->
                                </div><!---box--->    
                            </div><!---tab pane--->                    
                        </div><!---tab content--->
                    </div><!---tabs container--->
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
        sem: <?php echo $sem; ?>,
        payments:[],       
        other_payments:[], 
        tuition: {},
        total_tuition: 0,
        remaining_amount: 0,
        remaining_amount_formatted: 0,
        total_formatted: 0,
        sy: {},
        reservation_payment: undefined,
        application_payment: undefined,
        registration: {},
        reg_status: undefined, 
        advanced_privilages: false,
        selected_ay: undefined,
        student: {},
        loader_spinner: true,
        type: "",
        slug: "<?php echo $student['slug']; ?>",
        base_url: "<?php echo base_url(); ?>",
        update_status: "",
        has_partial: false,
        status_remarks: "",
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        this.loader_spinner = true;

        axios.get('<?php echo base_url(); ?>unity/accounting_viewer_data/<?php echo $id."/".$sem; ?>')
        .then((data) => {
            this.tuition = data.data.data;         
            this.student = data.data.data.student;  
            this.selected_ay = data.data.data.selected_ay;             
            this.advanced_privilages = data.data.data.advanced_privilages;
            this.registration = data.data.data.registration;
            this.reg_status = data.data.data.reg_status;
            this.loader_spinner = false;
            if(this.tuition.tuition){
                this.total_tuition = this.tuition.tuition.total;
                this.total_formatted = this.tuition.tuition.total.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                this.remaining_amount = this.total_tuition;
            }
            axios.get(api_url + 'finance/transactions/' + this.slug + '/' + this.tuition.selected_ay)
            .then((data) => {
                this.payments = data.data.data;
                this.other_payments = data.data.other;
                for(i in this.payments){
                    if(this.payments[i].status == "Paid"){
                        if(this.payments[i].description == "Tuition Partial" || this.payments[i].description == "Tuition Down Payment")
                            this.has_partial = true;
                    }
                }

                if(this.has_partial)
                    this.remaining_amount = this.tuition.tuition.total_installment;

                for(i in this.payments){
                    if(this.payments[i].status == "Paid")
                        this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
                }
                this.loader_spinner = false;

                axios.get(api_url + 'finance/reservation/' + this.slug + '/' + this.sem)
                .then((data) => {
                    this.reservation_payment = data.data.data;    
                    this.application_payment = data.data.application;

                    if(this.reservation_payment.status == "Paid" && data.data.student_sy == this.tuition.selected_ay)
                            this.remaining_amount = this.remaining_amount - this.reservation_payment.subtotal_order;            

                    this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;                            
                    this.remaining_amount_formatted = this.remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                    this.loader_spinner = false;
                })
                .catch((error) => {
                    console.log(error);
                })
            })
            .catch((error) => {
                console.log(error);
            })

            
        })
        .catch((error) => {
            console.log(error);
        })



    },

    methods: {        

        updateStatus: function() {
            Swal.fire({
                title: 'Update Status',
                text: "Are you sure you want to update?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios
                        .post(api_url + 'admissions/student-info/' + this.slug +
                            '/update-status', {
                                status: this.update_status,
                                remarks: this.status_remarks,
                                admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
                            }, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            if (data.data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )
                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                // if (result.isConfirmed) {
                //     Swal.fire({
                //         icon: result?.value.data.success ? "success" : "error",
                //         html: result?.value.data.message,
                //         allowOutsideClick: false,
                //     }).then(() => {
                //         if (reload && result?.value.data.success) {
                //             if (reload == "reload") {
                //                 location.reload();
                //             } else {
                //                 window.location.href = reload;
                //             }
                //         }
                //     });
                // }
            })
        }


    }

})
</script>