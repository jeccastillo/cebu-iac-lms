<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1>
                <small>
                    <a class="btn btn-app"
                        :href="base_url + 'admissionsV1/view_all_leads'"><i
                            class="ion ion-arrow-left-a"></i>All Students Applicants</a>
                </small>
                {{ student.first_name+" "+student.last_name+", "+student.middle_name }}
            </h1>
        </section>
        <hr />
        <div class="content">
            <div class="alert alert-danger"
                role="alert"
                v-if="!uploaded_requirements">
                This student has not submitted any requirements.
            </div>
            <div class="alert alert-info"
                role="alert"
                v-if="student.waive_app_fee">
                The Application Fee for this student is waived for the reason of:
                {{ student.waive_reason }}
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div v-if="cashier"
                        class="box box-solid box-success">
                        <div class="box-header">
                            <h4 class="box-title">New Application Transaction - Cashier
                                {{ cashier.intID }}
                            </h4>

                        </div>
                        <div class="box-body">
                            <div class="row">
                                <form @submit.prevent="submitManualPayment"
                                    method="post">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Select payment for</label>
                                            <select required
                                                @change="selectDescription"
                                                class="form-control"
                                                v-model="request.description">
                                                <option value="Reservation Payment">Reservation
                                                </option>
                                                <option v-if="!student.waive_app_fee"
                                                    value="Application Payment">Application</option>
                                                <option value="Other">Other</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div v-if="!student.waive_app_fee"
                                        class="col-sm-6">
                                        <div class="form-group">
                                            <label>Deduct referal discount from application
                                                fee?</label>
                                            <select @change="selectDescription"
                                                class="form-control"
                                                v-model="application_referal">
                                                <option value=false>No</option>
                                                <option value=true>Yes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Enter type if other is selected:</label>
                                            <input type="text"
                                                :disabled="request.description != 'Other'"
                                                required
                                                class="form-control"
                                                v-model="description_other" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Enter amount to pay/refund:</label>
                                            <input type="text"
                                                :disabled="request.description != 'Other' && (request.description != 'Reservation Payment' || (cashier.temporary_admin !=  1 && user.special_role != 2))"
                                                required
                                                class="form-control"
                                                v-model="amount_to_pay" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Contact Number:</label>
                                            {{ request.contact_number }}
                                            <input type="hidden"
                                                required
                                                class="form-control"
                                                v-model="request.contact_number" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Payment Type</label>
                                            <select class="form-control"
                                                v-model="request.is_cash">
                                                <option value="1">Cash</option>
                                                <option value="0">Check</option>
                                                <option value="2">Credit Card</option>
                                                <option value="3">Debit Card</option>
                                                <option value="4">Online Payment</option>
                                            </select>
                                        </div>
                                    </div>
                                    <input type="hidden"
                                        v-model="request.status"
                                        value="Paid" />
                                    <!-- <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Payment Status</label>
                                            <select class="form-control" v-model="request.status">
                                                <option value="Paid">Paid</option>
                                                <option value="Pending">Pending</option>                                                        
                                                <option value="Refunded">Refunded</option>
                                            </select>
                                        </div>
                                    </div> -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Reference No.:</label>
                                            <input type="text"
                                                :disabled="request.is_cash == 1"
                                                required
                                                class="form-control"
                                                v-model="request.check_number" />
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Remarks:</label>
                                            <textarea type="text"
                                                required
                                                class="form-control"
                                                v-model="request.remarks"></textarea>
                                        </div>
                                    </div>
                                    <!-- <div class="col-sm-6"
                                        v-if="request.description == 'Application Payment'">
                                        <div class="form-group">
                                            <label>OR Number <span class="text-danger">*</span>
                                            </label>
                                            <div
                                                v-if="user.special_role == 2 || cashier.temporary_admin ==  1">
                                                <input type="number"
                                                    class="form-control"
                                                    v-model="request.or_number" />
                                            </div>
                                            <div v-else>
                                                <div>{{ request.or_number }}</div>
                                                <input type="hidden"
                                                    class="form-control"
                                                    v-model="request.or_number" />
                                            </div>
                                        </div>
                                    </div> -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Invoice Number:</label>
                                            <!-- <div>
                                                <input type="text" class="form-control" v-model="request.invoice_number" />
                                            </div>    -->

                                            <div
                                                v-if="user.special_role == 2 || cashier.temporary_admin ==  1">
                                                <input type="number"
                                                    class="form-control"
                                                    v-model="request.invoice_number" />
                                            </div>
                                            <div v-else>
                                                <div>{{ request.invoice_number }}</div>
                                                <input type="hidden"
                                                    class="form-control"
                                                    v-model="request.invoice_number" />
                                            </div>
                                        </div>
                                    </div>
                                    <!-- <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Amount to Pay:</label>
                                            {{ request.subtotal_order }}
                                        </div>
                                    </div> -->
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label>Email: {{ request.email_address }}</label>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <button class="btn btn-primary btn-lg"
                                            type="submit">Submit Payment</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12">
                    <div class="box box-solid box-success">
                        <div class="box-header">
                            <h4 class="box-title">Transactions</h4>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>OR Number</th>
                                    <th>Cashier</th>
                                    <th>Payment Type</th>
                                    <th>Reference No.</th>
                                    <th>Request ID</th>
                                    <th>Amount Paid</th>
                                    <th>Online Payment Charge</th>
                                    <th>Total Due</th>
                                    <th>Status</th>
                                    <th>Online Response Message</th>
                                    <th>Date Updated</th>
                                    <th>Actions</th>
                                </tr>
                                <tr v-for="refunded in refunded_payments">
                                    <td>{{ refunded.invoice_number }}</td>
                                    <td>{{ refunded.or_number }}</td>
                                    <td><a href="#"
                                            @click.prevent.stop="cashierDetails(application_payment.cashier_id)">{{ refunded.cashier_id }}</a>
                                    </td>
                                    <td>{{ refunded.description }}</td>
                                    <td>{{ refunded.check_number }}</td>
                                    <td>{{ refunded.request_id }}</td>
                                    <td>{{ refunded.subtotal_order }}</td>
                                    <td>{{ refunded.charges }}</td>
                                    <td>{{ refunded.total_amount_due }}</td>
                                    <td>{{ refunded.status }}</td>
                                    <td>{{ refunded.response_message }}</td>
                                    <td>{{ refunded.or_date }}</td>
                                    <td>
                                        <button v-if="!refunded.or_number"
                                            data-toggle="modal"
                                            @click="or_update.id = refunded.id;"
                                            data-target="#myModal"
                                            class="btn btn-primary">
                                            Update OR
                                        </button>
                                        <button data-toggle="modal"
                                            @click="invoice_update.id = refunded.id;"
                                            data-target="#invoiceUpdate"
                                            class="btn btn-primary">
                                            Update Invoice
                                        </button>
                                        <button v-if="refunded.or_number"
                                            @click="printOR(refunded)"
                                            class="btn btn-primary">
                                            Print OR
                                        </button>
                                    </td>
                                </tr>
                                <!-- <tr v-if="application_payment">
                                    <td>{{ application_payment.or_number }}</td>
                                    <td><a href="#" @click.prevent.stop="cashierDetails(application_payment.cashier_id)">{{ application_payment.cashier_id }}</a></td>
                                    <td>{{ application_payment.description }}</td>
                                    <td>{{ application_payment.check_number }}</td>
                                    <td>{{ application_payment.subtotal_order }}</td>
                                    <td>{{ application_payment.charges }}</td>
                                    <td>{{ application_payment.total_amount_due }}</td>
                                    <td>{{ application_payment.status }}</td>                                            
                                    <td>{{ application_payment.response_message }}</td>
                                    <td>{{ application_payment.updated_at }}</td>            
                                    <td>
                                        <button v-if="!application_payment.or_number" data-toggle="modal"                                                
                                                @click="or_update.id = application_payment.id;" 
                                                data-target="#myModal" class="btn btn-primary">
                                                Update OR
                                        </button>
                                        <button v-if="application_payment.or_number"                                             
                                                @click="printOR(application_payment)" 
                                                class="btn btn-primary">
                                                Print OR
                                        </button>
                                    </td>                                    
                                </tr> 
                                <tr v-if="reservation_payment">
                                    <td>{{ reservation_payment.or_number }}</td>
                                    <td><a href="#" @click.prevent.stop="cashierDetails(reservation_payment.cashier_id)">{{ reservation_payment.cashier_id }}</a></td>
                                    <td>{{ reservation_payment.description }}</td>
                                    <td>{{ reservation_payment.check_number }}</td>
                                    <td>{{ reservation_payment.subtotal_order }}</td>
                                    <td>{{ reservation_payment.charges }}</td>
                                    <td>{{ reservation_payment.total_amount_due }}</td>
                                    <td>{{ reservation_payment.status }}</td>
                                    <td>{{ reservation_payment.response_message }}</td>
                                    <td>{{ reservation_payment.updated_at }}</td>
                                    <td>
                                        <button v-if="!reservation_payment.or_number" data-toggle="modal"                                                
                                                @click="or_update.id = reservation_payment.id;" 
                                                data-target="#myModal" class="btn btn-primary">
                                                Update OR
                                        </button>
                                        <button v-if="reservation_payment.or_number"                                             
                                                @click="printOR(reservation_payment)" 
                                                class="btn btn-primary">
                                                Print OR
                                        </button>
                                    </td>
                                </tr>                                                                                                     -->
                                <tr v-for="(payment,i) in payments">
                                    <td>{{ payment.invoice_number }}</td>
                                    <td>{{ payment.or_number }}</td>
                                    <td><a href="#"
                                            @click.prevent.stop="cashierDetails(payment.cashier_id)">{{ payment.cashier_id }}</a>
                                    </td>
                                    <td :class="payment.muted"
                                        v-if="(payment.description == 'Application Payment' || payment.description == 'Reservation Payment' || payment.description == 'Tuition Fee')">
                                        <select @change="updateDescription(payment.id,$event)"
                                            class="form-control"
                                            v-model="payments[i].description">
                                            <option value="Application Payment">Application Payment
                                            </option>
                                            <option value="Tuition Fee">Tuition Fee</option>
                                            <option value="Reservation Payment">Reservation Payment
                                            </option>
                                        </select>
                                    </td>
                                    <td v-else>
                                        {{ payment.description }}
                                    </td>
                                    <td>{{ payment.check_number }}</td>
                                    <td>{{ payment.request_id }}</td>
                                    <td>{{ payment.subtotal_order }}</td>
                                    <td>{{ payment.charges }}</td>
                                    <td>{{ payment.total_amount_due }}</td>
                                    <td>{{ payment.status }}</td>
                                    <td v-if="payment.remarks != 'Voided'">
                                        {{ payment.response_message }}
                                    </td>
                                    <td v-else>{{ payment.void_reason }}</td>
                                    <td>{{ payment.or_date }}</td>
                                    <td>
                                        <button
                                            v-if="!payment.or_number && payment.status == 'Paid'"
                                            data-toggle="modal"
                                            @click="or_update.id = payment.id;"
                                            data-target="#myModal"
                                            class="btn btn-primary">
                                            Update OR
                                        </button>
                                        <button data-toggle="modal"
                                            @click="invoice_update.id = payment.id;"
                                            data-target="#invoiceUpdate"
                                            class="btn btn-primary">
                                            Update Invoice
                                        </button>
                                        <button v-if="payment.or_number"
                                            @click="printOR(payment)"
                                            class="btn btn-primary">
                                            Print OR
                                        </button>
                                        <button
                                            v-if="payment.status == 'Paid' && payment.remarks != 'Voided' && cashier && finance_manager_privilages"
                                            data-toggle="modal"
                                            data-target="#voidPaymentModal"
                                            class="btn btn-primary"
                                            @click="setToVoid(payment.id)">Void/Cancel</button>
                                    </td>
                                </tr>
                            </table>
                            <hr />
                            <div v-if="sms_account">
                                <a class="btn btn-primary"
                                    :href="base_url+'unity/registration_viewer/'+sms_account.intID">View
                                    SMS Account Data</a>
                            </div>
                        </div>
                        <!---box body--->
                    </div>
                    <!---box--->
                </div>
                <!---column--->
            </div>
            <!---row--->
        </div>
        <!---content container--->
        <form ref="print_or"
            method="post"
            :action="base_url + 'pdf/print_or_new'"
            target="_blank">
            <input type="hidden"
                name="campus"
                :value="request.student_campus">
            <input type="hidden"
                name="student_name"
                v-model="or_print.student_name">
            <input type="hidden"
                name="cashier_id"
                v-model="or_print.cashier_id">
            <input type="hidden"
                name="student_id"
                v-model="or_print.student_id">
            <input type="hidden"
                name="student_address"
                v-model="or_print.student_address">
            <input type="hidden"
                name="is_cash"
                v-model="or_print.is_cash">
            <input type="hidden"
                name="check_number"
                v-model="or_print.check_number">
            <input type="hidden"
                name="or_number"
                v-model="or_print.or_number" />
            <input type="hidden"
                name="remarks"
                v-model="or_print.remarks">
            <input type="hidden"
                name="description"
                v-model="or_print.description" />
            <input type="hidden"
                name="total_amount_due"
                v-model="or_print.total_amount_due" />
            <input type="hidden"
                name="name"
                v-model="or_print.student_name" />
            <input type="hidden"
                name="sem"
                v-model="or_print.sem" />
            <input type="hidden"
                name="type"
                v-model="or_print.type" />
            <input type="hidden"
                name="transaction_date"
                v-model="or_print.transaction_date" />
        </form>
        <div class="modal fade"
            id="invoiceUpdate"
            role="dialog">
            <form @submit.prevent="updateInvoice"
                class="modal-dialog modal-lg">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button"
                            class="close"
                            data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Update Invoice Number</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Invoice Number <span class="text-danger">*</span> </label>
                            <template v-if="invoiceNumbers.length !== 0">
                                <select v-model="invoice_update.invoice_number" class="form-control">                           
                                    <option v-for="invoice in invoiceNumbers" :value="invoice.invoice_number">
                                        {{invoice.invoice_number}}
                                    </option>                    
                                </select>
                            </template>         
                            <template v-if="invoiceNumbers.length === 0">
                                <p>{{invoice_update.invoice_number}}</p>                 
                                <input  type="hidden"
                                    class="form-control"
                                    v-model="invoice_update.invoice_number"
                                    required />
                            </template>  
                            
                        </div>
                    </div>
                    <div class=" modal-footer">
                        <!-- modal footer  -->
                        <button type="submit"
                            :disabled="!or_update.or_number"
                            class="btn btn-primary">Submit</button>
                        <button type="button"
                            class="btn btn-default"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>

            </form>
        </div>
        <div class="modal fade"
            id="myModal"
            role="dialog">
            <form @submit.prevent="updateOR"
                class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button"
                            class="close"
                            data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Add OR Number</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>OR Number <span class="text-danger">*</span> </label>
                            <input type="hidden"
                                class="form-control"
                                v-model="or_update.or_number"
                                required>
                            <h4>{{ String(or_update.or_number).padStart(5, '0') }}</h4>
                        </div>
                    </div>
                    <div class=" modal-footer">
                        <!-- modal footer  -->
                        <button type="submit"
                            class="btn btn-primary">Submit</button>
                        <button type="button"
                            :disabled="!or_update.or_number"
                            class="btn btn-default"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>

            </form>
        </div>
        <div class="modal fade"
            id="voidPaymentModal"
            role="dialog">
            <form @submit.prevent="voidPayment"
                class="modal-dialog modal-lg">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button"
                            class="close"
                            data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Set Payment to Void</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <textarea class="form-control"
                                v-model="void_reason"
                                required></textarea>
                        </div>
                    </div>
                    <div class=" modal-footer">
                        <!-- modal footer  -->
                        <button type="submit"
                            class="btn btn-primary">Submit</button>
                        <button type="button"
                            class="btn btn-default"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
    <!---vue container--->
</aside>
<script type="text/javascript"
    src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>

<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#vue-container',
    data: {
        student: undefined,
        type: "<?php echo $type; ?>",
        slug: "<?php echo $slug; ?>",
        base_url: "<?php echo base_url(); ?>",
        applicant_id: undefined,
        application_referal: false,
        reservation_payment: undefined,
        application_payment: undefined,
        user: {
            special_role: 0,
        },
        payments: [],
        uploaded_requirements: false,
        refunded_payments: [],
        amount_to_pay: 0,
        sms_account: undefined,
        void_id: undefined,
        void_reason: undefined,
        finance_manager_privilages: false,
        particulars: [],
        description_other: '',
        cashier: undefined,
        request: {
            first_name: '',
            slug: '',
            middle_name: '',
            last_name: '',
            contact_number: '',
            email_address: '',
            mode_of_payment_id: 26,
            description: undefined,
            sy_reference: undefined,
            or_number: undefined,
            remarks: '',
            subtotal_order: 0,
            convenience_fee: 0,
            total_amount_due: 0,
            charges: 0,
            cashier_id: undefined,
            status: 'Paid',
            is_cash: 1,
            check_number: undefined,
            student_campus: '<?php echo $campus; ?>',
        },
        or_print: {
            or_number: undefined,
            description: undefined,
            total_amount_due: undefined,
            student_name: undefined,
            transaction_date: undefined,
            student_name: undefined,
            student_address: undefined,
            student_id: undefined,
            remarks: undefined,
            is_cash: undefined,
            cashier_id: undefined,
            check_number: undefined,
            sem: undefined,
            type: undefined,
        },
        invoice_update_description: undefined,
        invoice_update: {
            id: undefined,
            invoice_number: undefined,
            cashier_id: undefined,
            sy_reference: undefined,
            total_amount_due: undefined,
            student_campus: undefined,
        },
        or_update: {
            id: undefined,
            or_number: undefined,
            cashier_id: undefined,
            sy_reference: undefined,
            student_campus: undefined,
        },
        apiUpdate:'',
        invoiceNumbers: []

    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);

        const d = new Date();
        let year = d.getFullYear();

        this.loader_spinner = true;
        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.student = data.data.data;
                this.request.slug = this.slug;
                this.or_print.type = this.student.type;
                for (i in this.student.payments) {
                    if (this.student.sy_reference == this.student.payments[i]
                        .sy_reference || this.student.payments[i].sy_reference == null)
                        this.payments.push(this.student.payments[i]);
                }
                console.log(this.payments);
                this.request.first_name = this.student.first_name;
                this.request.middle_name = this.student.middle_name;
                this.request.last_name = this.student.last_name;
                this.request.contact_number = this.student.mobile_number;
                this.request.email_address = this.student.email;
                if (this.student.uploaded_requirements.length > 0)
                    this.uploaded_requirements = true;
               
                axios.get(base_url + 'finance/manualPayData/' + this.slug)
                    .then((data) => {
                        this.cashier = data.data.cashier
                        this.request.sy_reference = data.data.current_sem;
                        this.or_update.sy_reference = data.data.current_sem;
                        this.user = data.data.user;
                        this.particulars = data.data.particulars;
                        this.sms_account = data.data.data;
                        this.finance_manager_privilages = data.data
                            .finance_manager_privilages;
                        this.or_update.student_campus = this.request.student_campus;
                        this.applicant_id = "A" + data.data.sem_year + "-" + String(
                            this.student.id).padStart(4, '0');
                        this.getInvoiceNumber()    
                        if (this.cashier) {
                            // this.request.or_number =  this.cashier.or_current;
                            this.or_update.or_number = this.cashier.or_current;
                            this.request.cashier_id = this.cashier.user_id;
                            this.or_update.cashier_id = this.cashier.user_id;
                            // this.request.invoice_number = this.cashier.invoice_current
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                    })

                for (i in this.payments) {
                    if (this.payments[i].status == "Refunded")
                        this.refunded_payments.push(this.payments[i]);
                    else if (this.payments[i].status == "Paid") {
                        if (this.payments[i].description == "Application Payment") {
                            this.application_payment = this.payments[i];
                        }
                        if (this.payments[i].description == "Reservation Payment") {
                            this.reservation_payment = this.payments[i];
                        }
                    }
                }
            })
            .catch((error) => {
                console.log(error);
            })
            
    },
    watch: {
        'request.description': {
            handler(newVal, oldVal) {            
                this.request.invoice_number = this.cashier.invoice_current
                this.request.or_number  = ''
                this.apiUpdate = 'finance/update_cashier_invoice'
            }
                
            },
            deep: true,
            immediate: true
        }
    },

    methods: {
       async getInvoiceNumber(){                                    
           const {data} = await axios.get(`${api_url}finance/invoice-list/${this.student.sy_reference}/${this.student.campus}/${this.slug}`)
           this.invoiceNumbers = data.data

           if (this.invoiceNumbers.length === 0) {
               this.invoice_update.invoice_number = this.cashier?.invoice_current
           }           
        },
        printOR: function(payment) {
            Swal.fire({
                title: 'Continue with Printing OR',
                text: "Are you sure you want to continue? You can only print the OR once",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (data) => {
                    this.or_print.or_number = payment.or_number;
                    this.or_print.description = payment.description;
                    this.or_print.total_amount_due = payment.subtotal_order;
                    this.or_print.transaction_date = payment.or_date;
                    this.or_print.remarks = payment.remarks;
                    this.or_print.student_name = this.request.last_name +
                        ", " + this.request.first_name + ", " + this.request
                        .middle_name;
                    this.or_print.student_address = this.student.address;
                    this.or_print.student_id = this.applicant_id;
                    this.or_print.is_cash = payment.is_cash;
                    this.or_print.check_number = payment.check_number;
                    this.or_print.cashier_id = payment.cashier_id;
                    this.or_print.sem = payment.sy_reference;
                    this.$nextTick(() => {
                        this.$refs.print_or.submit();
                    });
                }
            });
        },
        updateDescription: function(id, event) {
            var desc = event.target.value;
            let url = api_url + 'finance/update_payment_description';

            Swal.fire({
                title: 'Change Description?',
                text: "Are you sure you want to change payment description?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    var formdata = new FormData();
                    formdata.append('description', desc);
                    formdata.append('id', id);


                    return axios.post(url, formdata, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {

                            if (data.data.success)
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            else
                                Swal.fire({
                                    title: "Failed",
                                    text: data.data.message,
                                    icon: "error"
                                }).then(function() {
                                    //location.reload();
                                });
                        });

                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {

            })

        },
        cashierDetails: function(id) {
            axios.get(base_url + 'finance/cashier_details/' + id)
                .then((data) => {
                    var cashier_details = data.data.cashier_data;
                    Swal.fire({
                        title: "Cashier",
                        text: cashier_details.strFirstname + " " +
                            cashier_details.strLastname,
                        icon: "info"
                    })
                })

        },
        updateInvoice: function() {
            let url = api_url + 'finance/update_invoice';
            let slug = this.slug;
            this.loader_spinner = true;
            console.log(this.invoice_update);
            
            Swal.fire({
                title: 'Continue with the update',
                text: "Are you sure you want to update the payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.post(url, this.invoice_update, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.loader_spinner = false;
                            if (data.data.success) {

                                this.loader_spinner = false;
                                Swal.fire({
                                    title: "Success",
                                    text: "Update Success",
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });

                            }else{
                                this.loader_spinner = false;
                                Swal.fire({
                                    title: "failed",
                                    text: `${data.data.message}`,
                                    icon: "error"
                                })
                            }
                        });

                },
            });
        },
        updateOR: function() {
            let url = api_url + 'finance/update_or';

            this.loader_spinner = true;

            Swal.fire({
                title: 'Continue with the update',
                text: "Are you sure you want to update the payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios.post(url, this.or_update, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.loader_spinner = false;
                            if (data.data.success) {
                                var formdata = new FormData();
                                formdata.append('intID', this.cashier.intID);
                                formdata.append('or_current', this.cashier.or_current);
                                formdata.append('or_used', this.cashier.or_current);
                                formdata.append('sy', this.student.sy_reference);
                                axios.post(base_url + 'finance/next_or',
                                        formdata, {
                                            headers: {
                                                Authorization: `Bearer ${window.token}`
                                            }
                                        })
                                    .then(function(data) {
                                        if (data.data.send_notif) {
                                            let url = api_url +
                                                'registrar/send_notif_enrolled/' +
                                                this.student_data
                                                .slug;
                                            let payload = {
                                                'message': "This message serves as a notification that you have been officially enrolled."
                                            }

                                            Swal.fire({
                                                showCancelButton: false,
                                                showCloseButton: false,
                                                allowEscapeKey: false,
                                                title: 'Loading',
                                                text: 'Processing Data do not leave page',
                                                icon: 'info',
                                            })
                                            Swal.showLoading();
                                            axios.post(url,
                                                    payload, {
                                                        headers: {
                                                            Authorization: `Bearer ${window.token}`
                                                        }
                                                    })
                                                .then(data => {
                                                    this.loader_spinner =
                                                        false;
                                                    Swal.fire({
                                                        title: "Success",
                                                        text: data
                                                            .data
                                                            .message,
                                                        icon: "success"
                                                    }).then(
                                                        function() {
                                                            location
                                                                .reload();
                                                        });
                                                });
                                        } else {
                                            Swal.fire({
                                                title: "Success",
                                                text: data
                                                    .data
                                                    .message,
                                                icon: "success"
                                            }).then(function() {
                                                location
                                                    .reload();
                                            });

                                        }

                                    })
                            } else
                                Swal.fire({
                                    title: "Failed",
                                    text: data.data.message,
                                    icon: "error"
                                }).then(function() {
                                    //location.reload();
                                });
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {

            })

        },
        setToVoid: function(payment_id) {
            this.void_id = payment_id;
            this.void_reason = undefined;
        },
        voidPayment: function() {
            let url = api_url + 'finance/set_void';

            this.loader_spinner = true;

            Swal.fire({
                title: 'Continue with processing Payment',
                text: "Are you sure you want to process payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    let payload = {
                        'id': this.void_id,
                        'void_reason': this.void_reason
                    }

                    return axios.post(url, payload, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.loader_spinner = false;
                            if (data.data.success)
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            else
                                Swal.fire({
                                    title: "Failed",
                                    text: data.data.message,
                                    icon: "error"
                                }).then(function() {
                                    //location.reload();
                                });
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {

            })

        },
        submitManualPayment: function() {
            let url = api_url + 'finance/manual_payment';
            this.loader_spinner = true;
            if (this.request.description == "Application Payment" && this
                .application_payment && this.application_payment.status == "Paid")
                Swal.fire({
                    title: "Failed",
                    text: "Application Payment already exists",
                    icon: "error"
                }).then(function() {
                    //location.reload();
                });
            else
                Swal.fire({
                    title: 'Continue with Payment',
                    text: "Are you sure you want to add payment?",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    imageWidth: 100,
                    icon: "question",
                    cancelButtonText: "No, cancel!",
                    showCloseButton: true,
                    showLoaderOnConfirm: true,
                    preConfirm: (login) => {


                        if (this.request.description == 'Other') {
                            this.request.description = this.description_other;
                        }

                        this.request.subtotal_order = this.amount_to_pay;
                        this.request.total_amount_due = this.amount_to_pay;

                        console.log(this.request);
                        // console.log(url);

                        return axios.post(url, this.request, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                            .then(data => {
                                this.loader_spinner = false;
                                if (data.data.success) {
                                    var formdata = new FormData();
                                    if (this.request.description == 'Application Payment') {
                                        formdata.append('intID', this.cashier.intID);
                                        formdata.append('or_current', this.cashier.or_current);
                                        formdata.append('or_used', this.cashier.or_current);
                                        formdata.append('sy', this.student.sy_reference);
                                    }

                                    if (this.request.description == 'Reservation Payment') {
                                        formdata.append('intID', this.cashier.intID);
                                        formdata.append('start', this.cashier.invoice_start);
                                        formdata.append('end', this.cashier.invoice_end);
                                        formdata.append('current', (parseInt(this.cashier.invoice_current) + 1).toString());
                                    }
                                    console.log(this.apiUpdate);
                                    console.log(formdata);
                                    
                                    // check if the next_or increment the or
                                    axios.post(base_url + this.apiUpdate,formdata, {
                                                headers: {
                                                    Authorization: `Bearer ${window.token}`
                                                }
                                            })
                                        .then(function() {
                                            console.log(data);

                                            if (data.data.send_notif) {
                                                let url = api_url +
                                                    'registrar/send_notif_enrolled/' +
                                                    this.student_data
                                                    .slug;
                                                let payload = {
                                                    'message': "This message serves as a notification that you have been officially enrolled."
                                                }

                                                Swal.fire({
                                                    showCancelButton: false,
                                                    showCloseButton: false,
                                                    allowEscapeKey: false,
                                                    title: 'Loading',
                                                    text: 'Processing Data do not leave page',
                                                    icon: 'info',
                                                })
                                                Swal.showLoading();
                                                axios.post(url,
                                                        payload, {
                                                            headers: {
                                                                Authorization: `Bearer ${window.token}`
                                                            }
                                                        })
                                                    .then(data => {
                                                        this.loader_spinner =
                                                            false;
                                                        Swal.fire({
                                                            title: "Success",
                                                            text: data
                                                                .data
                                                                .message,
                                                            icon: "success"
                                                        }).then(
                                                            function() {
                                                                location
                                                                    .reload();
                                                            });

                                                    });
                                            } else {
                                                Swal.fire({
                                                    title: "Success",
                                                    text: data
                                                        .data
                                                        .message,
                                                    icon: "success"
                                                }).then(function() {
                                                    location
                                                        .reload();
                                                });

                                            }

                                        })
                                } else
                                    Swal.fire({
                                        title: "Failed",
                                        text: data.data.message,
                                        icon: "error"
                                    }).then(function() {
                                        //location.reload();
                                    });
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {

                })

        },

        selectDescription: function() {
            if (this.request.description == "Reservation Payment") {
                this.amount_to_pay = 10000;
            } else if (this.request.description == "Application Payment") {
                if (this.student.campus == "Cebu") {
                    if (this.application_referal)
                        this.amount_to_pay = 300;
                    else
                        this.amount_to_pay = 500;
                } else
                if (this.application_referal)
                    this.amount_to_pay = 500;
                else
                    this.amount_to_pay = 700;
            } else {
                this.amount_to_pay = 0;
            }
        },
        updateCashierInvoice() {
            var formdata = new FormData();
            formdata.append('intID', this.cashier.intID);
            formdata.append('start', this.cashier.invoice_start);
            formdata.append('end', this.cashier.invoice_end);
            formdata.append('current', (parseInt(this.cashier.invoice_current) + 1)
                .toString());

            axios.post(base_url + 'finance/update_cashier_invoice', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            }).then().catch()
        }


    }

})
</script>