<div id="registration-container">
    <div class="container">
        <form class="content">

            <div style="margin-top:5rem">
                <h3>Student Exam</h3>
            </div>

            <?php for($i = 1; $i<=10; $i++) { ?>

            <div class="panel panel-default">
                <!-- Default panel contents -->
                <div class="panel-heading">1. What lorem ipsum donr oex 1289 ?</div>
                <div class="panel-body">
                    <div class="choices_box">
                        <div class="in_choice">
                            <input type="radio" name="question1" required id="choice1">
                            <label for="choice1" class="choices_label"> a. The CSS Grid Layout
                                Module offers a
                                grid-based layout
                                system</label>
                        </div>
                        <div class="in_choice">
                            <input type="radio" name="question1" required id="choice2">
                            <label for="choice2" class="choices_label"> b. The CSS Grid Layout
                                Module offers a
                                grid-based layout
                                system</label>
                        </div>
                        <div class="in_choice">
                            <input type="radio" name="question1" required id="choice3">
                            <label for="choice3" class="choices_label"> c. The CSS Grid Layout
                                Module offers a
                                grid-based layout
                                system</label>
                        </div>
                        <div class="in_choice">
                            <input type="radio" name="question1" required id="choice4">
                            <label for="choice4" class="choices_label"> d. The CSS Grid Layout
                                Module offers a
                                grid-based layout
                                system</label>
                        </div>
                    </div>
                </div>

            </div>

            <?php } ?>

            <div>
                <button type="submit" class="btn btn-default">SUBMIT EXAM</button>
            </div>

        </form>
    </div>
</div>

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
    justify-content: center;
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