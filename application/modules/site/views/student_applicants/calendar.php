<link href="https://unpkg.com/vue-cal@legacy/dist/vuecal.css" rel="stylesheet">
<link rel="stylesheet" href="https://unpkg.com/vue2-datepicker/index.css">

<script src="https://unpkg.com/vue@legacy"></script>
<script src="https://unpkg.com/vue-cal@legacy"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/moment.js/2.7.0/moment.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/vue2-datepicker@1.9.7/dist/build.min.js"></script> -->
<script src="https://unpkg.com/vue2-datepicker/index.min.js"></script>
<script src="https://unpkg.com/vue2-datepicker/locale/zh-cn.js"></script>

<script>
window.moment || document.write(
    '\x3Cscript src="assets/plugins/moment/moment.min.js" type="text/javascript">\x3C/script>')
</script>


<div id="admissions-form" style="margin-top:150px" v-show="student.email">
    <div class="custom-container">
        <vue-cal v-if="events" active-view="month" :on-cell-click="true" :disable-views="['years', 'year', '']"
            default-view="month" events-on-month-view="short" twelveHour hide-weekends :events="events"
            :on-event-dblclick="showDetails" @cell-click="logEvents('cell-click', $event)" style="height: 550px">
        </vue-cal>
    </div>



    <div id="modal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <form @submit.prevent="submitSchedule"
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <!-- Heroicon name: outline/exclamation-triangle -->

                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                    {{date_selected}}</h3>
                                <div class="mt-12">
                                    <p class="mb-3">
                                        Scheduled Time:
                                    </p>

                                    <ul class=" text-gray-500" v-if="time_scheduled.length > 0">
                                        <li v-for="time in time_scheduled">{{time.time_from + ' - ' + time.time_to}}
                                        </li>
                                    </ul>

                                    <span v-else>
                                        No reserved scheduled for this date.
                                    </span>

                                </div>


                                <div class="form-group mt-6">
                                    <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                                        Select Time Slot <span class="text-red-500">*</span>
                                    </label>
                                    <date-picker :time-picker-options="
                                                        reserve_time_picker_options
                                                    " v-model="request.from" type="time" lang="en" format="hh:mm A"
                                        @change="checkTime" placeholder="HH:MM AM" :input-attr="{
                                                    required: true,
                                                    id: 'time_from'
                                                }"
                                        input-class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                    </date-picker>
                                </div>

                                <!-- <div class="form-group mt-6">
                                    <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                                        To <span class="text-red-500">*</span>
                                    </label>
                                    <date-picker :time-picker-options="
                                                        reserve_time_picker_options
                                                    " v-model="request.to" type="time" lang="en" format="hh:mm A"
                                        placeholder="HH:MM AM" @change="checkTime" :input-attr="{
                                                    required: true,
                                                    id: 'time_to'
                                                }"
                                        input-class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                    </date-picker>
                                </div> -->


                            </div>
                        </div>
                    </div>
                    <div class=" bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="submit"
                            class="inline-flex w-full justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 sm:ml-3 sm:w-auto sm:text-sm">Schedule</button>
                        <button type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="toggleModal()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>


<script>
function toggleModal() {
    document.getElementById('modal').classList.toggle('hidden')
}

Vue.component('asd', {
    template: "<div>test</div>"
});
new Vue({
    el: "#admissions-form",
    data: {
        date_selected: "",
        time_scheduled: [],
        date_selected_formatted: "",
        request: {
            from: "",
            to: ""
        },
        value5: "",
        reserve_time_picker_options: {
            start: "08:00",
            step: "00:30",
            end: "17:00"
        },
        events: [],
        tags: ['foo', 'bar'],
        student: {},
        slug: "<?php echo $this->uri->segment('3'); ?>",
    },
    mounted() {
        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.student = data.data.data;
            })
            .catch((error) => {
                console.log(error);
            })

    },

    methods: {
        showDetails: function() {
            alert();
        },

        logEvents: function(event, data) {
            // console.log(event, data);

            let today = moment(new Date()).format("YYYY-MM-DD");
            console.log(today)
            this.date_selected = moment(data).format("MMMM DD, YYYY");
            this.date_selected_formatted = moment(data).format("YYYY-MM-DD");

            if (this.date_selected_formatted <= today) {
                Swal.fire(
                    'Ooopps!',
                    "Unable to select pass date and today's date. Please try other dates. ",
                    'error'
                )
                return false;
            }



            axios
                .get(api_url + 'interview-schedules/' + this.date_selected_formatted, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    },
                })

                .then((data) => {
                    this.time_scheduled = data.data.data;
                })
                .catch((e) => {
                    console.log("error");
                });


            toggleModal()


        },

        submitSchedule: function() {


            let time_from = moment(this.request.from).format('LT');
            let time_to = moment(this.request.from).add(30, 'minutes').format('LT');

            this.request.date = this.date_selected_formatted;
            this.request.slug = this.slug;
            this.request.time_from = moment(time_from, ["h:mm A"]).format("HH:mm")
            this.request.time_to = moment(time_to, ["h:mm A"]).format("HH:mm")



            Swal.fire({
                title: "Submit Schedule",
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return axios
                        .post(api_url + 'interview-schedules', this.request, {
                            headers: {
                                Authorization: `Bearer ${window.token}`
                            }
                        })
                        .then(data => {
                            this.is_done = true;

                            if (data.data.success) {

                                Swal.fire({
                                    title: "SUCCESS",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(res => {
                                    window.location =
                                        "<?php echo base_url();?>site"
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
                if (result.isConfirmed) {}
            })
        },

        toggleModal: function() {
            document.getElementById('modal').classList.toggle('hidden')
        },

        checkTime: function() {

            if (this.request.from && this.request.to) {
                if (this.request.from >= this.request.to) {
                    Swal.fire(
                        'Failed!',
                        "Invalid time, please select valid time.",
                        'error'
                    )

                    this.request.to = "";

                }
            }

        }

    },

    components: {
        'vue-cal': vuecal,
        'date-picker': DatePicker
    },



});
</script>