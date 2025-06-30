<aside class="right-side" id="enrollment-statistics">
    <section class="content-header">
        <h1> Enrollment Statistics </h1>
        <p>Institutional Identifier No. 13315</p>
        <p>Term/SY: 2nd Term/SY 2024-2025</p>
    </section>
    <hr />
    <div class="content">
        <h4>Officially Enrolled</h4>
        <div>
            <table class="table table-bordered">
                <tr>
                    <th>Program</th>
                    <th>ID2024</th>
                    <th>ID2023</th>
                    <th>ID2022</th>
                    <th>ID2021</th>
                    <th>ID2019</th>
                    <th>ID2018</th>
                    <th>Total</th>
                </tr>
                <tr v-for="program in programName ">
                    <td>{{program}}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>
</aside>
<style>
th,
td {
    border: 1px solid #ddd !important;
}

tbody tr:last-child {
    font-weight: 700;
}
</style>
<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js">
</script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>
<script>
const programName = ['Accountancy, Business and Management', 'Animation',
    'Arts and Design (Media Arts and Visual Arts)', 'Computer Programming', 'Fashion Design',
    'Graphic Illustration ', 'Humanities and Social Sciences', 'Total'
]
new Vue({
    el: '#enrollment-statistics',
    data: {
        base_url: '<?php echo base_url(); ?>',
        current_sem: '<?php echo $sem; ?>',
        programName: programName,
    },
})
</script>