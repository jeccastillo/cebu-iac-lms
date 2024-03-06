<div id="paid-applicant"
  class="content-wrapper ">
  <section class="content-header container ">
    <h1>
      Student Paid Applicants
      <small>
        <a class="btn btn-app"
          :href="base_url + 'admissionsV1/view_all_leads'"><i class="fa fa-users"></i>
          Students Applicants</a>
        <a class="btn btn-app"
          href="#"
          id="print_form"><i class="fa fa-file"></i> Export to Excel</a>
      </small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i> Student Paid Applicants</a></li>
      <li class="active">View All Paid Applicants</li>
    </ol>
    <hr />
    <div class="pull-right">
      <select id="select-term-leads"
        class="form-control"
        v-model="current_sem">
        <option v-for="sem in sems"
          :value="sem.intID">
          {{ `${sem.term_student_type} ${sem.enumSem} ${sem.term_label} ${sem.strYearStart} ${sem.strYearEnd}`}}
        </option>
      </select>
    </div>
    <hr />

    <div class="row">
  </section>
  <div class="content container">
    <div class="alert alert-danger"
      style="display:none;">
      <i class="fa fa-ban"></i>
      <span id="alert-text"></span>
    </div>
    <div class="box box-solid box-primary">
      <div class="box-header">
        <h3 class="box-title">Student Applicants</h3>

      </div><!-- /.box-header -->
      <div class="box-body table-responsive">
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Last Name</th>
              <th>First Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>

            </tr>
          </tbody>

        </table>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div>
</div>
</div>


<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript"
  src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
  integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
  crossorigin="anonymous"
  referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
  el: '#paid-applicant',
  data: {
    sems: JSON.parse(`<?php echo json_encode($sy); ?>`),
    base_url: '<?php echo base_url(); ?>',
    current_sem: '<?php echo $current_sem; ?>',
    applicants_data: []
  },
  mounted() {
    this.getAllPaidList()
  },
  methods: {

    getAllPaidList: function() {
      axios.get(
          `${api_url}admissions/applications/paid-application-fee?current_sem=${this.current_sem} `
        )
        .then((data) => {
          console.log(data.data);
          this.applicants_data = data.data

        })
        .catch((error) => {
          console.log(error);

        });
    }
  }
})
</script>