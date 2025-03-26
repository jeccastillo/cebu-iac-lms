<aside class="right-side">
    <section class="content-header">
        <h1>
            Credit Subject
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Registrar</a></li>
            <li class="active">Add Credit Subject</li>
        </ol>
    </section>
    <div class="content">
        <div id="add-student" class="span10 box box-primary">
        <form v-on:submit.prevent="importCreditedSubject">
                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-xs-4">
                            <label for="student_level">Credit Subject</label>
                            <input @change="attachFile" type="file" name="import_credit_subject_excel" id="import_credit_subject_excel" size="20" />
                        </div>
                        <div class="form-group col-xs-4"></div>
                        <div class="form-group col-xs-4" style="text-align:right">
                            <button type="button" @click="downloadFormat" class="btn btn-lg btn-default  btn-flat">Download Format</button>
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
        async importCreditedSubject() {
            const formData = new FormData()

            formData.append('import_credit_subject_excel', this.attachment)

            const {
                data
            } = await axios
                .post('<?php echo base_url(); ?>excel/import_credit_subject', formData, {
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
                $("#import_credit_subject_excel").val('');
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
        downloadFormat()
        {
            var url = base_url + 'excel/download_credit_subject_format';
            window.open(url, '_blank');
        }
    }
})
</script>