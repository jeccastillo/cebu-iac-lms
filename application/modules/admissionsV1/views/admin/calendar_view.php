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
        <div class="row">
            <div class="col-sm-6">
                <p>Filter by Year</p>
                <select @change="updateList" v-model="selected_year" class="form-select form-control">
                    <option v-for="year in years" :value="year">{{ year }}</option>                 
                </select>
            </div>
        </div>
    </section>
    <div class="content container">
        <div v-if="interviews">
            <table class="table table-primary table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Date</th>
                        <th>From</th>
                        <th>To</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="interview in interviews">
                        <td>{{ interview.applicant_name }}</td>
                        <td>{{ interview.date }}</td>
                        <td>{{ interview.time_from }}</td>
                        <td>{{ interview.time_to }}</td>
                    </tr>                
                </tbody>
            </table>
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
const current_year = new Date().getFullYear();
new Vue({
    el: '#applicant-container',
    data: {
        selected_year: current_year,
        tags: ['foo', 'bar'],         
        interviews: undefined, 
        campus: "<?php echo $campus ?>",
    },
    computed : {
        years () {            
            return Array.from({length: 50}, (value, index) => 2023 + index)
        }
    },

    mounted() {
        
        axios.get(api_url + 'interview-schedules/year/' + this.selected_year + '/' + this.campus, {
            headers: {
                Authorization: `Bearer ${window.token}`
            },
        })

        .then((data) => {
            this.interviews = data.data.data;
            // console.log(data);
        })
        .catch((e) => {
            console.log("error");
        });




    },

    methods: {
        updateList: function(){            
            axios.get(api_url + 'interview-schedules/year/' + this.selected_year + '/' + this.campus, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {
                this.interviews = data.data.data;
                // console.log(data);
            })
            .catch((e) => {
                console.log("error");
            });
        }

    }

})
</script>