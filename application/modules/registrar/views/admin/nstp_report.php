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
        <tr>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Grade</th>
          <th>NSTP 1</th>
          <th>NSTP 2</th>

        </tr>
        <tr>
          <td>

          </td>
          <td>

          </td>
          <td>

          </td>
          <td>

          </td>
          <td>

          </td>

        </tr>
        <tr>
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


  },

  methods: {


  }

})
</script>