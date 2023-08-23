<div id="student-exam">
    <div class="container">
        <form @submit.prevent="submitExam()" class="content" method="post">


            <div style="margin-top:5rem">
                <h3>Student Exam</h3>
            </div>


            <div v-for="(q,q_index) in request.question" class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">{{q.title}}</div>
                <div class="panel-body">
                    <div class="choices_box">
                        <div v-for="(c,index) in q.choices" class="in_choice">
                            <input type="radio" v-model="c.is_selected" value="1"
                                @click="updateChoices(q.choices, index, q_index)" :name="'question-' + q_index" required
                                id="choice1">
                            <label for="choice1" class="choices_label"> {{c.choice}} </label>
                        </div>

                    </div>
                </div>



            </div>
            <div>
                <button type="submit" class="btn btn-default">SUBMIT EXAM</button>
            </div>

        </form>
    </div>
</div>


<script>
new Vue({
    el: "#student-exam",
    data: {
        request: {

        },
        student_name: "",
        slug: "<?php echo $this->uri->segment('3'); ?>",
        exam_id: "<?php echo $this->uri->segment('4'); ?>",
    },
    mounted() {
        axios.get("http://cebuapi.iacademy.edu.ph/api/v1/sms/" + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.request = data.data.data;
                this.student_name = this.request.first_name + ' ' + this.request.last_name;
                this.loader_spinner = false;
                //this.program_update = this.request.type_id;

                axios.get("<?php echo base_url();?>" + "examination/get_questions_per_section/" + this
                        .exam_id)
                    .then(
                        (data) => {
                            this.request = data.data

                        }).catch((e) => {
                        console.log(e)
                    })


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
                .then(function(response) {
                    if (data.data.success) {
                        alert(data.data.message);

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
    align-items: center;
    gap: 1rem;
}

.choices_label {
    margin-bottom: 0 !important;
    font-weight: normal
}

@media screen and (max-width: 767px) {
    .choices_box {
        grid-template-rows: repeat(1, 1fr);
    }
}
</style>