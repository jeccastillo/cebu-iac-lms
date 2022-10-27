<!-- <script src="https://unpkg.com/vue@legacy"></script> -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.17/vue.js"></script>

<script src="https://unpkg.com/vue-cal@legacy"></script>
<script src="https://unpkg.com/vue-cal"></script>
<link href="https://unpkg.com/vue-cal@legacy/dist/vuecal.css" rel="stylesheet"> -->

<!-- <script>
Vue.component("VueCal", VueCal);
</script> -->

<script src="https://unpkg.com/vue@legacy"></script>
<script src="https://unpkg.com/vue-cal@legacy"></script>
<link href="https://unpkg.com/vue-cal@legacy/dist/vuecal.css" rel="stylesheet">

<!-- <script src="https://unpkg.com/vue@2.5.13/dist/vue.min.js"></script> -->
<!-- <script src="https://unpkg.com/v-tag-input@0.0.3/dist/v-tag-input.js"></script> -->


<div id="admissions-form" style="margin-top:100px">
    <div class="container">
        <vue-cal v-if="events" selected-date="2018-11-19" active-view="month" :on-cell-click="true"
            :disable-views="['years', 'year', '']" default-view="month" events-on-month-view="short" twelveHour
            hide-weekends :events="events" :on-event-dblclick="showDetails"
            @cell-click="logEvents('cell-click', $event)" style="height: 550px">
        </vue-cal>
    </div>

    <div class="modal fade" id="myModal" role="dialog">
        <form @submit.prevent="submitForm">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Interview Schedules</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Hey</label>
                            <input type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Submit</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </form>
    </div>


</div>

<script>
Vue.component('asd', {
    template: "<div>test</div>"
});
new Vue({
    el: "#admissions-form",
    data: {
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
            console.log(event, data)

            $("#myModal").modal("toggle");
        },

        submitForm: function() {
            alert();
        }
    },

    components: {
        'vue-cal': vuecal
    },



});
</script>