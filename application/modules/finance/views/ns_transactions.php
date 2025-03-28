<aside class="right-side">
    <div id="vue-container">
        <section class="content-header">
            <h1> NS Payments <small>
                    <a class="btn btn-app" :href="base_url + 'finance/view_payees_cashier'"><i
                            class="ion ion-arrow-left-a"></i>All Payees</a>
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
                            <div class="pull-right" style="margin-left:1rem;">
                                <select class="form-control" @change="selectTerm($event)"
                                    v-model="request.sem">
                                    <option v-for="s in sy" :value="s.intID">
                                        {{ s.term_student_type}} {{ s.enumSem }} {{ s.term_label }}
                                        {{ s.strYearStart }} - {{ s.strYearEnd }}
                                    </option>
                                </select>
                            </div>
                            <h3 v-if="payee" class="widget-user-username"
                                style="text-transform:capitalize;margin-left:0;font-size:1.3em;">
                                {{ payee.firstname+' '+payee.lastname }}
                            </h3>
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
                                    <th>OR Number</th>
                                    <th>Invoice Number</th>
                                    <th>Cashier</th>
                                    <th>Payment Type</th>
                                    <th>Reference No.</th>
                                    <th>Amount Paid</th>
                                    <th>Online Payment Charge</th>
                                    <th>Total Due</th>
                                    <th>Status</th>
                                    <th>Online Response Message</th>
                                    <th>Transaction Date</th>
                                    <th>Actions</th>
                                </tr>
                                <tr v-for="payment in payments">
                                    <td>{{ payment.or_number }}</td>
                                    <td>{{ payment.invoice_number }}</td>
                                    <td><a href="#"
                                            @click.prevent.stop="cashierDetails(payment.cashier_id)">{{ payment.cashier_id }}</a>
                                    </td>
                                    <td>{{ payment.description }}</td>
                                    <td>{{ payment.check_number }}</td>
                                    <td>{{ payment.subtotal_order }}</td>
                                    <td>{{ payment.charges }}</td>
                                    <td>{{ payment.total_amount_due }}</td>
                                    <td>{{ payment.status }}</td>
                                    <td>{{ payment.response_message }}</td>
                                    <td>{{ payment.or_date }}</td>
                                    <td>
                                        <button v-if="payment.or_number" @click="printOR(payment)"
                                            class="btn btn-primary"> Print OR </button>
                                        <button v-if="payment.invoice_number"
                                            @click="printInvoice(payment)" class="btn btn-primary">
                                            Print Invoice </button>
                                        <button v-if="cashier && payment.remarks != 'Voided'"
                                            data-toggle="modal"
                                            @click="or_details.id = payment.id; or_details.status = payment.status; or_details.or_date = payment.or_date;"
                                            data-target="#orDetailsUpdate" class="btn btn-primary">
                                            Update Details </button>
                                        <button
                                            v-if="cashier && finance_manager_privilages && payment.status == 'Paid'"
                                            class="btn btn-danger"
                                            @click="deletePayment(payment.id)">Retract
                                            Payment</button>
                                    </td>
                                </tr>
                            </table>
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
        <form ref="print_or" method="post" :action="base_url + 'pdf/print_updated_or'"
            target="_blank">
            <input type="hidden" name="student_name" v-model="or_print.student_name">
            <input type="hidden" name="campus" :value="request.student_campus">
            <input type="hidden" name="cashier_id" v-model="or_print.cashier_id">
            <input type="hidden" name="student_id" v-model="or_print.student_id">
            <input type="hidden" name="student_address" v-model="or_print.student_address">
            <input type="hidden" name="is_cash" v-model="or_print.is_cash">
            <input type="hidden" name="check_number" v-model="or_print.check_number">
            <input type="hidden" name="remarks" v-model="or_print.remarks">
            <input type="hidden" name="or_number" v-model="or_print.or_number" />
            <input type="hidden" name="invoice_number" v-model="or_print.invoice_number" />
            <input type="hidden" name="description" v-model="or_print.description" />
            <input type="hidden" name="total_amount_due" v-model="or_print.total_amount_due" />
            <input type="hidden" name="name" v-model="or_print.student_name" />
            <input type="hidden" name="sem" v-model="or_print.sem" />
            <input type="hidden" name="payee_id" v-model="or_print.payee_id" />
            <input type="hidden" name="transaction_date" v-model="or_print.transaction_date" />
            <input type="hidden" name="type" v-model="or_print.type" />
        </form>
        <form ref="print_invoice" method="post" :action="base_url + 'pdf/print_invoice/0'"
            target="_blank">
            <input type="hidden" name="student_name" v-model="or_print.student_name">
            <input type="hidden" name="slug" v-model="slug">
            <input type="hidden" name="campus" :value="request.student_campus">
            <input type="hidden" name="cashier_id" v-model="or_print.cashier_id">
            <input type="hidden" name="student_id" v-model="or_print.student_id">
            <input type="hidden" name="student_address" v-model="or_print.student_address">
            <input type="hidden" name="is_cash" v-model="or_print.is_cash">
            <input type="hidden" name="check_number" v-model="or_print.check_number">
            <input type="hidden" name="remarks" v-model="or_print.remarks">
            <input type="hidden" name="or_number" v-model="or_print.or_number" />
            <input type="hidden" name="invoice_number" v-model="or_print.invoice_number" />
            <input type="hidden" name="description" v-model="or_print.description" />
            <input type="hidden" name="total_amount_due" v-model="or_print.total_amount_due" />
            <input type="hidden" name="sem" v-model="or_print.sem" />
            <input type="hidden" name="transaction_date" v-model="or_print.transaction_date" />
            <input type="hidden" name="type" v-model="or_print.type" />
            <input type="hidden" name="withholding_tax_percentage"
                v-model="or_print.withholding_tax_percentage" />
            <input type="hidden" name="invoice_amount" v-model="or_print.invoice_amount" />
            <input type="hidden" name="invoice_amount_ves" v-model="or_print.invoice_amount_ves" />
            <input type="hidden" name="invoice_amount_vzrs"
                v-model="or_print.invoice_amount_vzrs" />
            <input type="hidden" name="payee_id" v-model="or_print.payee_id" />
        </form>
        <div class="modal fade" id="orDetailsUpdate" role="dialog">
            <form @submit.prevent="updateORDetails" class="modal-dialog modal-lg">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <!-- modal header  -->
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Update Details</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Issued Date <span class="text-danger">*</span> </label>
                            <input type="date" class="form-control" v-model="or_details.or_date"
                                required />
                        </div>
                        <div class="form-group">
                            <label>Payment Status</label>
                            <select class="form-control" v-model="or_details.status">
                                <option value="Paid">Paid</option>
                                <option value="Pending">Pending</option>
                                <option value="Refunded">Refunded</option>
                            </select>
                        </div>
                    </div>
                    <div class=" modal-footer">
                        <!-- modal footer  -->
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-default"
                            data-dismiss="modal">Close</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!---vue container--->
</aside>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js">
</script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>
<script>
new Vue({
    el: '#vue-container',
    data: {
        student: undefined,
        base_url: "<?php echo base_url(); ?>",
        payee_id: "<?php echo $payee_id; ?>",
        payee: undefined,
        finance_manager_privilages: undefined,
        slug: undefined,
        request: {
            first_name: "<?php echo $first_name; ?>",
            last_name: "<?php echo $last_name; ?>",
            sem: "<?php echo $sem; ?>",
        },
        base_url: "<?php echo base_url(); ?>",
        user: {
            special_role: 0,
        },
        or_details: {
            id: undefined,
            or_date: undefined,
            status: undefined,
        },
        payments: [],
        cashier: undefined,
        sy: [],
        or_print: {
            or_number: undefined,
            description: undefined,
            total_amount_due: undefined,
            student_name: undefined,
            transaction_date: undefined,
            student_name: undefined,
            student_address: undefined,
            student_id: undefined,
            payee_id: <?php echo $payee_id; ?>,
            remarks: undefined,
            is_cash: undefined,
            cashier_id: undefined,
            check_number: undefined,
            sem: undefined,
            type: undefined,
            withholding_tax_percentage: 0,
            invoice_amount: 0,
            invoice_amount_ves: 0,
            invoice_amount_vzrs: 0,
        },
    },
    mounted() {
        let url_string = window.location.href;
        let url = new URL(url_string);
        const d = new Date();
        let year = d.getFullYear();
        this.loader_spinner = true;
        axios.post(api_url + 'finance/ns_transactions', this.request, {
            headers: {
                Authorization: `Bearer ${window.token}`
            }
        }).then((data) => {
            this.payments = data.data.data;
            axios.get(base_url + 'finance/ns_transactions_data/' + this.payee_id +
                '/' + this.request.sem).then((data) => {
                this.cashier = data.data.cashier;
                this.user = data.data.user;
                this.payee = data.data.payee;
                this.finance_manager_privilages = data.data
                    .finance_manager_privilages;
                this.sy = data.data.sy;
                if (this.cashier) {
                    this.request.or_number = this.cashier.or_current;
                    this.request.cashier_id = this.cashier.user_id;
                }
            }).catch((error) => {
                console.log(error);
            })
        }).catch((error) => {
            console.log(error);
        })
    },
    methods: {
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
                    this.or_print.transaction_date = payment.updated_at;
                    this.or_print.remarks = payment.remarks;
                    this.or_print.student_name = this.payee.lastname +
                        ", " + this.payee.firstname + ", " + this.payee
                        .middlename;
                    this.or_print.student_address = this.payee.address;
                    this.or_print.student_id = this.payee.id_number;
                    this.or_print.is_cash = payment.is_cash;
                    this.or_print.check_number = payment.check_number;
                    this.or_print.cashier_id = payment.cashier_id;
                    this.or_print.sem = payment.sy_reference;
                    this.or_print.type = "ns_payment";
                    this.$nextTick(() => {
                        this.$refs.print_or.submit();
                    });
                }
            });
        },
        updateORDetails: function() {
            let url = api_url + 'finance/update_or_details';
            let slug = this.slug;
            this.loader_spinner = true;
            Swal.fire({
                title: 'Continue with the update',
                text: "Are you sure you want to update the payment details?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return axios.post(url, this.or_details, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    }).then(data => {
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
                        }
                    });
                },
            });
        },
        deletePayment: function(payment_id) {
            let url = api_url + 'finance/delete_payment';
            this.loader_spinner = true;
            Swal.fire({
                title: 'Continue with deleting Payment',
                text: "Are you sure you want to delete payment?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    let payload = {
                        'id': payment_id
                    }
                    return axios.post(url, payload, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    }).then(data => {
                        this.loader_spinner = false;
                        if (data.data.success) {
                            Swal.fire({
                                title: "Success",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.reload();
                            });
                        } else Swal.fire({
                            title: "Failed",
                            text: data.data.message,
                            icon: "error"
                        }).then(function() {
                            //location.reload();
                        });
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {})
        },
        printInvoice: function(payment) {
            Swal.fire({
                title: 'Continue with Printing Invoice',
                text: "Are you sure you want to continue? You can only print the Invoice once",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (data) => {
                    this.or_print.or_number = payment.or_number;
                    this.or_print.invoice_number = payment.invoice_number;
                    this.or_print.description = payment.description;
                    this.or_print.total_amount_due = payment.subtotal_order;
                    this.or_print.transaction_date = payment.or_date;
                    this.or_print.remarks = payment.remarks;
                    this.or_print.withholding_tax_percentage = payment
                        .withholding_tax_percentage,
                        this.or_print.invoice_amount = payment
                        .invoice_amount,
                        this.or_print.invoice_amount_ves = payment
                        .invoice_amount_ves,
                        this.or_print.invoice_amount_vzrs = payment
                        .invoice_amount_vzrs,
                        this.or_print.student_name = this.payee.lastname +
                        ", " + this.payee.firstname;
                    if (this.payee.middlename && this.payee.middlename !=
                        "undefined") this.or_print.student_name += ", " +
                        this.payee.middlename;
                    this.or_print.student_address = this.payee.address;
                    this.or_print.payee_id = this.payee.id;
                    this.or_print.student_id = '';
                    this.or_print.is_cash = payment.is_cash;
                    this.or_print.type = "ns_payment";
                    this.or_print.check_number = payment.check_number;
                    this.or_print.sem = payment.sy_reference;
                    this.or_print.cashier_id = payment.cashier_id;
                }
            }).then((result) => {
                var delayInMilliseconds = 1000; //1 second
                var or_send = this.$refs.print_invoice;
                setTimeout(function() {
                    or_send.submit();
                }, delayInMilliseconds);
            });
        },
        cashierDetails: function(id) {
            axios.get(base_url + 'finance/cashier_details/' + id).then((data) => {
                var cashier_details = data.data.cashier_data;
                Swal.fire({
                    title: "Cashier",
                    text: cashier_details.strFirstname + " " +
                        cashier_details.strLastname,
                    icon: "info"
                })
            })
        },
        selectTerm: function(event) {
            document.location = base_url + "finance/ns_transactions/" + this.payee_id +
                "/" + event.target.value;
        },
    }
})
</script>