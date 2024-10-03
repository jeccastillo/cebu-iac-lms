<aside class="right-side">
    <section class="content-header">
        <h1>
            Previous Balance
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Finance</a></li>
            <li class="active">Upload Previous Balance</li>
        </ol>
    </section>
    <div class="content">
        <div id="add-student" class="span10 box box-primary">
        <form v-on:submit.prevent="importPreviousBalance">
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-xs-4">
                            <input @change="attachFile" type="file" name="previous_balance_excel" id="previous_balance_excel" size="20" />
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col-xs-4">
                            <button type="submit" class="btn btn-lg btn-default  btn-flat">Import</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
</aside>

<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script>
new Vue({
    el: '#add-student',
    data: {
        studentLevel: 'college',
        attachment: '',
    },
    methods: {
        attachFile($event) {
            this.attachment = $event.target.files[0]
        },
        async importPreviousBalance() {
            const formData = new FormData()

            formData.append('previous_balance_excel', this.attachment)

            const {
                data
            } = await axios
                .post('<?php echo base_url(); ?>excel/import_previous_balance', formData, {
                })
            
                console.log(data);
            if (data == true) {
                Swal.fire({
                    showCancelButton: false,
                    showCloseButton: true,
                    allowEscapeKey: false,
                    title: 'Successfully Import ',
                    icon: 'success',
                });
                $("#previous_balance_excel").val('');
            } else {
                Swal.fire({
                    showCancelButton: false,
                    showCloseButton: true,
                    allowEscapeKey: false,
                    title: data,
                    icon: 'error',
                });
            }
        },
    }
})
</script>