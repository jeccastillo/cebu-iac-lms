<aside class="right-side"
  id="nstp-report-container">
  <section class="content-header">
    <h1>
      NSTP report
      <small>
        <a class="btn btn-app"
          href="<?php echo base_url(); ?>registrar/registrar_reports">
          <i class="ion ion-arrow-left-a"></i>
          All Reports
        </a>
        <a class="btn btn-app"
          target="_blank"
          href="<?php //echo $pdf_link; ?>"><i class="fa fa-book"></i>Generate PDF</a>
        <a class="btn btn-app"
          target="_blank"
          href="<?php //echo $excel_link; ?>"><i class="fa fa-book"></i>Generate Excel</a>
      </small>
    </h1>
  </section>
  <hr />
  <div class="content">
    <div>
      <div class="form-group pull-right">
        <label>Term Select</label>
        <select class="form-control">
          <option value="">college 1st term </option>
        </select>
      </div>
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th rowspan="2">Serial No.</th>
            <th colspan="3">Student Name</th>
            <th rowspan="2">Course/Program</th>
            <th rowspan="2">Gender</th>
            <th rowspan="2">Birthdate</th>
            <th rowspan="2">Street/Barangay</th>
            <th rowspan="2">Town/City Address</th>
            <th rowspan="2">Provincial Address</th>
            <th rowspan="2">Contact Number Telephone/Mobile</th>
            <th rowspan="2">Email Address</th>
          </tr>
          <tr>
            <th>Surname</th>
            <th>First Name</th>
            <th>Middle Name</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <td rowspan="1"
              style="vertical-align:middle;">Sub Total:</td>
            <td>
              <div>Male:</div>
              <div>Female:</div>
            </td>
            <td style="text-align:center">
              <div>0</div>
              <div>0</div>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
          <tr>
            <td></td>
            <td rowspan="2"
              style="vertical-align:middle;">Grand Total:</td>
            <td>
              <div>Male:</div>
              <div>Female:</div>
            </td>
            <td style="text-align:right">
              <div>0</div>
              <div>0</div>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tfoot>

      </table>
    </div>
  </div>

</aside>

<style>
thead th {
  text-align: center;
  vertical-align: middle !important;
}
</style>

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
  el: '#nstp-report-container',
  data: {
    base_url: '<?php echo base_url(); ?>',
    current_sem: '<?php echo $sem; ?>',

  },

  mounted() {

    let url_string = window.location.href;

    axios.get(this.base_url + 'registrar/nstp_report_data/' + this.current_sem)
      .then((data) => {
        console.log(data);

      })
      .catch((error) => {
        console.log(error);

      });



  }

})
</script>