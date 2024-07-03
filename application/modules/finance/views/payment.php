<aside class="right-side">
    <section class="content-header">
        <h1>
            Finance
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
            <li class="active">Payment</li>
        </ol>
    </section>
    <div class="content">
        <section class="section section_port relative" id="finance-form">
            <div class="custom-container md:h-[500px] relative z-1">
                <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png" class="absolute top-0 md:right-[25%] hidden md:block"
                    alt="" data-scroll-speed="4" data-aos="zoom-in" />

                <img src="<?php echo $img_dir; ?>home-poly/yellow-poly.png"
                    class="absolute top-[10%] md:left-[17%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />
                <img src="<?php echo $img_dir; ?>home-poly/red-poly.png" class="absolute top-[30%] md:left-[0%] hidden md:block"
                    alt="" data-scroll-speed="4" data-aos="zoom-in" />

                <img src="<?php echo $img_dir; ?>home-poly/peach-poly.png"
                    class="absolute top-[25%] md:left-[33%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

                <img src="<?php echo $img_dir; ?>home-poly/lyellow-poly.png"
                    class="absolute top-[50%] md:right-[0%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

                <img src="<?php echo $img_dir; ?>home-poly/lblue-poly.png"
                    class="absolute top-[20%] md:right-[10%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

                
            </div>

            
                    <div class="md:w-1/2 w-full">



                        <div>
                            <h5 class="my-3">Select Mode of Payment ( Banks )</h5>
                            <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                                <div v-for="t in payment_modes" style="border:1px solid #000" @click="selectPayment(t)"
                                    class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                                    style="display:flex; align-itenms:center;">
                                    <img :src="t.image_url" class="img-fluid d-block mx-auto" width="51px" alt="">
                                </div>
                            </div>

                            <hr>
                            <h5 class="my-3">Select Mode of Payment ( Non-Banks )</h5>
                            <div class="d-flex flex-wrap" style="display:flex; flex:wrap;">
                                <div v-for="t in payment_modes_nonbanks" style="border:1px solid #000" @click="selectPayment(t)"
                                    class="box_mode_payment d-flex align-items-center justify-content-center mr-3 my-3 p-1"
                                    style="display:flex; align-itenms:center;">
                                    <img class="img-fluid d-block mx-auto" width="51px" :src="t.image_url" alt="">
                                </div>
                            </div>
                        </div>

                        <hr>                

                </form>
            </div>
        </section>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<style scoped="">
.box_mode_payment {
    border: 1px solid #000;
    height: 41px;
    width: 57px;
    margin: 4px;
    cursor: pointer;
}

.box_mode_payment.active {
    background: #1c54a5;
}

.spinner {
    animation-name: spin;
    animation-duration: 1000ms;
    animation-iteration-count: infinite;
    animation-timing-function: linear;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>


<script>
new Vue({
    el: "#finance-form",
    data: {
        request: {
            mode_of_release: "",
            delivery_region_id: "",
            selected_location: "",
            mailing_fee: 0,
        },
        loading_spinner: false,
        registration: {},
        payment_modes: [],
        mode_of_releases: [],
        area_delivery: [],
        city_delivery: [],
        payment_modes_nonbanks: [],
        selected_items: [],
        payment_type: "<?php echo $this->uri->segment('2'); ?>",
        item_details: {
            price: 0,
            hey: this.payment_type
        },
        item: {}, //single order
        selected_mode_of_payment: {},
        total_single: 0,
        new_charge: 0,
        total_single_without_charge: 0,
        join_selected: '',
        redirect_link: '',
        qty_single: '',
        qty_global: 0,
        from_cart: false,
        total_single_format: 0,
        total_price_cart: 0,
        total_price_from_cart: 0,
        total_price_cart_with_charge_es: 0,
        total_price_cart_with_charge: 0,
        payload: {},
        slug: "<?php echo $this->uri->segment('3'); ?>",
    },
    mounted() {

        this.item_details.price = this.payment_type == 'admissions_student_payment_reservation' ? 10000 : 500;

        axios
            .get(api_url + 'payments/modes?count_content=100', {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {
                this.payment_modes = _.filter(data.data.data, item => item.is_nonbank != true);
                this.payment_modes_nonbanks = _.filter(data.data.data, item => item.is_nonbank == true);

                $(function() {
                    $(".box_mode_payment").click(function() {
                        $(".box_mode_payment").removeClass("active");
                        $(this).addClass("active");
                    })
                })
            })
            .catch((e) => {
                console.log("error");
            });

        axios.get('<?php echo base_url(); ?>registrar/get_registration_info/<?php echo $student_id; ?>')
            .then((data) => {            
                this.registration = data.data.data;
            })
            .catch((error) => {
                console.log(error);
            })


    },

    methods: {

        selectPayment: function(mode_payment) {
            this.selected_mode_of_payment = mode_payment;

            var new_price = parseFloat(this.item_details.price);
            var new_charge = parseFloat(this.selected_mode_of_payment.charge);
            var qty = 1;


            if (this.selected_mode_of_payment.type == 'percentage') {
                var new_price_with_qty = new_price * qty;

                new_charge = ((new_charge / 100) * new_price_with_qty);
                if (new_charge < 25) {
                    new_charge = 25.00;
                }


            }

            this.total_single_without_charge = (new_price * qty);
            this.total_single = (new_price * qty) + new_charge;
            this.total_single_format = (this.total_single + parseFloat(this.request.mailing_fee))
                .toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');


            this.new_charge = new_charge.toFixed(2);

            console.log("total_single_format", this.total_single_format);
            console.log("new_charge", this.new_charge);


            this.payload = {
                "description": "Reservation Payment",
                "order_items": [{
                    "price_default": "700",
                    "title": "Reservation Payment",
                    "qty": "1",
                    "id": 1
                }],
                "total_price_without_charge": this.total_single_without_charge,
                "first_name": this.student.first_name,
                "last_name": this.student.last_name,
                "contact_number": this.student.mobile_number,
                "email": this.student.email,
                "remarks": "",
                "mode_of_payment_id": mode_payment.id,
                "delivery_region_id": null,
                "delivery_city_id": "",
                "country": "",
                "other_country": "",
                "total_price_with_charge": this.total_single,
                "charge": parseFloat(this.new_charge),
                "mode_of_release": null,
                "mailing_fee": 0,
                "student_information_id": this.student.id
            }


            // console.log(this.payload)

        },

        submitPayment: function() {
            // Swal.fire({
            //     title: "Submit Payment",
            //     text: "Are you sure you want to submit?",
            //     showCancelButton: true,
            //     confirmButtonText: "Yes",
            //     imageWidth: 100,
            //     icon: "question",
            //     cancelButtonText: "No, cancel!",
            //     showCloseButton: true,
            //     showLoaderOnConfirm: true,
            //     preConfirm: (login) => {
            //         return 
            //     },
            //     allowOutsideClick: () => !Swal.isLoading()
            // }).then((result) => {
            //     if (result.isConfirmed) {

            //     }
            // })

            this.loading_spinner = true;

            axios
                .post(api_url + 'payments', this.payload, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {
                    this.is_done = true;

                    if (data.data.success) {

                        if (!this.selected_mode_of_payment.is_nonbank) {
                            this.redirect_link = data.data.payment_link;
                            this.loading_spinner = false;

                            setTimeout(() => {
                                document.getElementById("payment_link")
                                    .click();
                            }, 500);

                        } else {}
                    } else {
                        Swal.fire(
                            'Failed!',
                            data.data.message,
                            'error'
                        )
                    }
                });
        }
    }

})
</script>