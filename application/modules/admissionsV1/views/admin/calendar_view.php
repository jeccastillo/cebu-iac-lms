<link href="https://unpkg.com/vue-cal@legacy/dist/vuecal.css" rel="stylesheet">
<script src="https://unpkg.com/vue@legacy"></script>
<script src="https://unpkg.com/vue-cal@legacy"></script>
<script src="http://cdnjs.cloudflare.com/ajax/libs/moment.js/2.7.0/moment.min.js"></script>

<script>
window.moment || document.write(
    '\x3Cscript src="assets/plugins/moment/moment.min.js" type="text/javascript">\x3C/script>')
</script>

<div class="content-wrapper " id="applicant-container">
    <section class="content-header container ">
        <h1>
            FI Schedules
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>First Interview Schedules </a></li>
            <li class="active">Details</li>
        </ol>
    </section>
    <div class="content container">
        <div class="custom-container">
            <vue-cal v-if="events" active-view="month" :on-cell-click="true" :disable-views="['years', 'year', '']"
                default-view="month" events-on-month-view="short" twelveHour :hide-weekdays="[7,1,2,3,4,5]" :events="events"
                 @cell-click="showEvents('cell-click', $event)" style="height: 550px">
            </vue-cal>
        </div>
    </div>
    <div id="modal" class="hidden relative z-10" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">                
                <div class=" bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">                       
                    <button type="button"
                        class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        @click="toggleModal()">Close</button>
                </div>
                
            </div>
        </div>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#applicant-container',
    components: {
        'vue-cal': vuecal,        
    },
    data: {
        events: [],
        tags: ['foo', 'bar'],          
    },

    mounted() {
        



    },

    methods: {

        showEvents: function(event, data) {
            // console.log(event, data);

            let today = moment(new Date()).format("YYYY-MM-DD");
            console.log(today)
            this.date_selected = moment(data).format("MMMM DD, YYYY");
            this.date_selected_formatted = moment(data).format("YYYY-MM-DD");            
            


            // axios
            //     .get(api_url + 'interview-schedules/' + this.date_selected_formatted, {
            //         headers: {
            //             Authorization: `Bearer ${window.token}`
            //         },
            //     })

            //     .then((data) => {
            //         this.time_scheduled = data.data.data;
            //     })
            //     .catch((e) => {
            //         console.log("error");
            //     });


            toggleModal()


        },
        toggleModal: function() {
            document.getElementById('modal').classList.toggle('hidden')
        },

    }

})
</script>