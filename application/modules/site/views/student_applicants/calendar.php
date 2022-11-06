<script src="https://unpkg.com/vue@legacy"></script>
<script src="https://unpkg.com/vue-cal@legacy"></script>
<link href="https://unpkg.com/vue-cal@legacy/dist/vuecal.css" rel="stylesheet">


<div id="admissions-form" style="margin-top:150px">
    <div class="custom-container">
        <vue-cal v-if="events" active-view="month" :on-cell-click="true" :disable-views="['years', 'year', '']"
            default-view="month" events-on-month-view="short" twelveHour hide-weekends :events="events"
            :on-event-dblclick="showDetails" @cell-click="logEvents('cell-click', $event)" style="height: 550px">
        </vue-cal>
    </div>



    <div id="modal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!--
    Background backdrop, show/hide based on modal state.

    Entering: "ease-out duration-300"
      From: "opacity-0"
      To: "opacity-100"
    Leaving: "ease-in duration-200"
      From: "opacity-100"
      To: "opacity-0"
  -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <!--
        Modal panel, show/hide based on modal state.

        Entering: "ease-out duration-300"
          From: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          To: "opacity-100 translate-y-0 sm:scale-100"
        Leaving: "ease-in duration-200"
          From: "opacity-100 translate-y-0 sm:scale-100"
          To: "opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      -->
                <form
                    class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div
                                class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <!-- Heroicon name: outline/exclamation-triangle -->
                                <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 10.5v3.75m-9.303 3.376C1.83 19.126 2.914 21 4.645 21h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 4.88c-.866-1.501-3.032-1.501-3.898 0L2.697 17.626zM12 17.25h.007v.008H12v-.008z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">
                                    {{date_selected}}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">Lorem ipsum dolor sit amet, consectetur adipiscing
                                        elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim
                                        ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea
                                        commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit
                                        esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat
                                        non proident, sunt in culpa qui officia deserunt mollit anim id est laborum</p>
                                </div>


                                <div class="mt-10 mb-6">
                                    <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                                        Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="date" required v-model="request.date">
                                </div>

                                <div class="mb-6">
                                    <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                                        Time <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                        type="time" required v-model="request.time">
                                </div>


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
        request: {},
        events: [{
                start: '2018-11-19 10:35',
                end: '2018-11-19 11:30',
                title: 'Doctor appointment'
            },
            {
                start: '2018-11-19 18:30',
                end: '2018-11-19 19:15',
                title: 'Dentist appointment'
            },
            {
                start: '2018-11-20 18:30',
                end: '2018-11-20 20:30',
                title: 'Crossfit'
            },
            {
                start: '2018-11-21 11:00',
                end: '2018-11-21 13:00',
                title: 'Brunch with Jane'
            },
            {
                start: '2018-11-21 19:30',
                end: '2018-11-21 23:00',
                title: 'Swimming lesson'
            },
            {
                start: '2019-09-30 19:30',
                end: '2019-09-30 23:00',
                title: 'Swimming lesson'
            },
            {
                start: "2018-11-19 12:00",
                end: "2018-11-19 14:00",
                title: "LUNCH",
                class: "lunch",
                background: true
            },
            {
                start: "2018-11-20 12:00",
                end: "2018-11-20 14:00",
                title: "LUNCH",
                class: "lunch",
                background: true
            }
        ],
        tags: ['foo', 'bar']
    },
    mounted() {


    },

    methods: {
        showDetails: function() {
            alert();
        },

        logEvents: function(event, data) {
            console.log(event, data);
            this.date_selected = data;

            toggleModal()
        },

        submitForm: function() {
            alert();
        },

        toggleModal: function() {
            document.getElementById('modal').classList.toggle('hidden')
        }
    },

    components: {
        'vue-cal': vuecal
    },



});
</script>