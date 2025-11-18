<aside class="right-side" id="mutual-exclusions-container">    
    <section class="content-header">
        <h1>Manage Mutually Exclusive Scholarships</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>scholarship/scholarships"><i class="fa fa-dashboard"></i>Scholarships</a></li>
            <li class="active">Mutual Exclusions</li>
        </ol>     
    </section>

    <div class="content"> 
        <div class="box box-default">
            <div class="box-header">
                <h3 class="box-title">Configure Mutual Exclusions</h3>
            </div>
            <div class="box-body"> 
                <div class="row">
                    <div class="col-md-5">
                        <label>Scholarship A</label>
                        <select class="form-control" v-model.number="selectedA">
                            <option :value="0">-- Select Scholarship A --</option>
                            <option v-for="sch in scholarships" :key="sch.intID" :value="sch.intID">{{ sch.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label>Scholarship B</label>
                        <select class="form-control" v-model.number="selectedB">
                            <option :value="0">-- Select Scholarship B --</option>
                            <option v-for="sch in scholarships" :key="'b-'+sch.intID" :value="sch.intID">{{ sch.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&amp;nbsp;</label>
                        <button class="btn btn-primary btn-block" :disabled="!canAdd" @click.prevent.stop="addExclusion">Add Pair</button>
                    </div>
                </div>
                <hr />

                <h4>Existing Exclusions</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 80px;">ID</th>
                                <th>Scholarship A</th>
                                <th>Scholarship B</th>
                                <th style="width: 160px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="exclusions.length === 0">
                                <td colspan="4" class="text-center">No exclusions configured.</td>
                            </tr>
                            <tr v-for="item in exclusions" :key="item.id">
                                <td>{{ item.id }}</td>
                                <td>{{ item.scholarship_a_name }} ({{ item.scholarship_id_a }})</td>
                                <td>{{ item.scholarship_b_name }} ({{ item.scholarship_id_b }})</td>
                                <td>
                                    <button class="btn btn-danger btn-sm" @click.prevent.stop="deleteExclusion(item.id)">Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>

<script>
new Vue({
    el: '#mutual-exclusions-container',
    data: {
        base_url: '<?php echo base_url(); ?>',
        scholarships: [],
        exclusions: [],
        selectedA: 0,
        selectedB: 0,
        loader_spinner: false
    },
    computed: {
        canAdd() {
            return this.selectedA > 0 &amp;&amp; this.selectedB > 0 &amp;&amp; this.selectedA !== this.selectedB &amp;&amp; !this.loader_spinner;
        }
    },
    mounted() {
        this.fetchData();
    },
    methods: {
        fetchData() {
            axios.get(this.base_url + 'scholarship/mutual_exclusions_data')
                .then((res) => {
                    this.scholarships = res.data.scholarships || [];
                    this.exclusions = res.data.exclusions || [];
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        addExclusion() {
            if (!this.canAdd) return;

            let formdata = new FormData();
            formdata.append('scholarship_id_a', this.selectedA);
            formdata.append('scholarship_id_b', this.selectedB);

            this.loader_spinner = true;
            axios.post(this.base_url + 'scholarship/add_mutual_exclusion', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then((res) => {
                this.loader_spinner = false;
                const data = res.data || {};
                if (window.Swal) {
                    Swal.fire({
                        title: data.success,
                        text: data.message,
                        icon: data.success,
                    }).then(() => {
                        if (data.success === 'success') {
                            this.selectedA = 0;
                            this.selectedB = 0;
                            this.fetchData();
                        }
                    });
                } else {
                    alert(data.message || 'Done');
                    if (data.success === 'success') {
                        this.selectedA = 0;
                        this.selectedB = 0;
                        this.fetchData();
                    }
                }
            })
            .catch((err) => {
                this.loader_spinner = false;
                console.log(err);
                if (window.Swal) {
                    Swal.fire({
                        title: 'error',
                        text: 'Failed to add mutual exclusion.',
                        icon: 'error',
                    });
                } else {
                    alert('Failed to add mutual exclusion.');
                }
            });
        },
        deleteExclusion(id) {
            if (!id) return;

            let formdata = new FormData();
            formdata.append('id', id);

            this.loader_spinner = true;
            axios.post(this.base_url + 'scholarship/delete_mutual_exclusion', formdata, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                }
            })
            .then((res) => {
                this.loader_spinner = false;
                const data = res.data || {};
                if (window.Swal) {
                    Swal.fire({
                        title: data.success,
                        text: data.message,
                        icon: data.success,
                    }).then(() => {
                        if (data.success === 'success') {
                            this.fetchData();
                        }
                    });
                } else {
                    alert(data.message || 'Done');
                    if (data.success === 'success') {
                        this.fetchData();
                    }
                }
            })
            .catch((err) => {
                this.loader_spinner = false;
                console.log(err);
                if (window.Swal) {
                    Swal.fire({
                        title: 'error',
                        text: 'Failed to delete record.',
                        icon: 'error',
                    });
                } else {
                    alert('Failed to delete record.');
                }
            });
        }
    }
});
</script>
