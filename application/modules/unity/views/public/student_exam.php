<div id="student-exam">
    <div class="container">
        <form @submit.prevent="submitExam()" class="content" method="post">


            <div style="margin-top:5rem">
                <h1 style="text-align:center;"><strong>iACADEMY</strong></h1>
                <h3 style="text-align:center;"><strong>STUDENT ENTRANCE EXAM</strong></h3>
                <br><br>
            </div>

            <!-- <p v-if="student.id"><strong>Student Name:</strong> {{student.first_name + ' ' + student.last_name}} </p> -->

            <div v-if="!request.success" style="margin-top:3rem">
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <strong>{{request.message}}</strong>
                </div>
            </div>


            <div v-for="(q,q_index) in request.question" class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">
                    <div style="display:flex; justify-content:space-between; width:100%">
                        <span> Question # {{q_index + 1}}</span>
                        <span>{{q.section}}</span>
                    </div>
                </div>
                <div class="panel-body">

                    <div v-html="q.title">
                    </div>

                    <div v-if="q.image">
                        <img :src="q.image" style="max-width:100%; display:block; margin:0 auto;" alt="">
                    </div>

                    <hr>


                    <div class="choices_box">
                        <div v-for="(c,index) in q.choices" class="choice_container alert" style="background:#e3e7e552"
                            style="white-space:pre-line">


                            <div class="in_choice">
                                <input style="margin-right:25px" type="radio" v-model="c.is_selected" value="1"
                                    @click="updateChoices(q.choices, index, q_index)" :name="'question-' + q_index"
                                    required class="radioBtn">
                                <label for="choice1" class="choices_label"> {{c.choice}} </label>

                            </div>
                            <br>
                            <div v-if="c.choice_image">
                                <img :src="c.choice_image" style="max-width:100%; display:block;" alt="">
                            </div>
                        </div>

                    </div>
                </div>



            </div>
            <div v-if="student.id && request.question.length > 0 ">
                <button type="submit" class="btn btn-default">SUBMIT EXAM</button>
            </div>

        </form>
    </div>
</div>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script>
new Vue({
    el: "#student-exam",
    data: {
        request: {
            question: []
        },
        student: {
            id: ""
        },
        is_submitted_exam: false,
        student_name: "",
        slug: "<?php echo $this->uri->segment('3'); ?>",
        exam_id: "<?php echo $this->uri->segment('4'); ?>",
        token: "<?php echo $this->uri->segment('5'); ?>",
    },
    mounted() {

        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.student = data.data.data;
                this.student_name = this.request.first_name + ' ' + this.request.last_name;
                this.loader_spinner = false;
                //this.program_update = this.request.type_id;

                if (this.student.id) {
                    axios.get("<?php echo base_url();?>" + "examination/get_questions_per_section/" + this
                            .exam_id + '/' + this.token + '/' + this.slug)
                        .then(
                            (data) => {
                                this.request = data.data


                            }).catch((e) => {
                            console.log(e)
                        })
                } else {
                    this.request.success = false;
                    this.request.message = "Invalid exam link."
                }


            })
            .catch((error) => {
                console.log(error);
            })
    },

    methods: {
        submitExam: function() {
            if (!confirm('Are you sure you want to submit?')) {
                return false;
            }

            this.request.student_id = this.slug;
            const request_data = {
                data: this.request
            }


            let formData = new FormData();
            formData.append("question", JSON.stringify(this.request.question))
            formData.append("student_id", this.slug)
            formData.append("student_name", this.student_name)

            axios.post("<?php echo base_url();?>" + "examination/submit_exam", formData)
                .then(function(data) {
                    if (data.data.success) {
                        alert(data.data.message);
                        document.location = "<?php echo base_url(); ?>";
                    }
                })
                .catch(function(error) {
                    console.log(error);
                });


        },

        updateChoices: function(choices, c_index, q_index) {

            console.log(q_index)

            var updated_choices = choices.map((c, i) => {
                if (i != c_index) {
                    return {
                        ...c,
                        is_selected: 0
                    }
                } else {
                    return {
                        ...c,
                        is_selected: 1
                    }
                }
            })
            this.request.question[q_index].choices = updated_choices
        }
    }

})
</script>

<style>
.choices_box {
    display: -ms-grid;
    display: grid;
    -ms-grid-rows: (1fr)[2];
    grid-template-rows: repeat(2, 1fr);
    -ms-grid-columns: (1fr)[2];
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem;

}

.in_choice {
    display: flex;
    align-items: baseline;
    gap: 1rem;
}

.choices_label {
    margin-bottom: 0 !important;
    font-weight: normal
}

.radioBtn {
    transform: scale(1.5);
}

@media screen and (max-width: 767px) {
    .choices_box {
        grid-template-columns: repeat(1, 1fr);
    }
}
</style>