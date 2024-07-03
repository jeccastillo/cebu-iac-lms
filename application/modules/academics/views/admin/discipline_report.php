<aside class="right-side"
  id="registration-container">
  <section class="content-header">
    <h1>
      Discipline Report
      <small>

      </small>
    </h1>
    <hr />
  </section>
  <hr />
  <div class="content">
    <div class="box box-primary">
      <div class="box-header">
        <h4>{{ Student Full Name }}</h4>
        <h5>{{ Student Number }}</h5>
      </div>
      <div class="box-body">
        <div class="row"
          style="margin-bottom:10px">
          <div class="col-sm-4">
            <label>Select Term</label>
            <select class="form-control"
              v-model="">
              <option>

              </option>
            </select>
          </div>
        </div>
        <hr />

        <form method="post"
          @submit.prevent="">
          <div class="row">
            <div class="col-sm-6 form-group">
              <label>Discipline Report</label>
              <textarea required
                class="form-control"
                v-model="request.remarks"></textarea>
            </div>
            <div class="col-sm-6 form-group">
              <label>Date</label>
              <input required
                type="date"
                class="form-control"
                v-model="request.remarks"></input>
            </div>
          </div>
          <hr />
          <button type="submit"
            class="btn btn-primary">Submit</button>
        </form>
        <hr />
        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th>Term</th>
              <th>Deficiency</th>
              <th>Department</th>
              <th>Remarks</th>
              <th>Date Added</th>
              <th>Added By</th>
              <th>Date Resolved</th>
              <th>Resolved By</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="true">
              <td colspan='8'>No Records</td>
            </tr>
            <tr v-else>
              <td>

              </td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td>

              </td>

            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript"
  src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
  integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
  crossorigin="anonymous"
  referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
  el: '#registration-container',
  data: {
    base_url: '<?php echo base_url(); ?>',
    sem: '<?php echo $sem; ?>',
    id: '<?php echo $id; ?>',
    disciplineDetails: {

    }


  },

  mounted() {




  },

})
</script>