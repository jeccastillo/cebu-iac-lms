<style>
.dataTables_filter {
    display: none;
}
</style>
<script type="text/javascript">
$(document).ready(function() {


    // $('#users_table tfoot th').each( function () {
    //     var title = $(this).text();
    //     $(this).html( '<input type="text" class="form-control" placeholder="'+title+'" size="15" />');
    // });
    $('#users_table thead tr.search td').each(function() {
        var title = $(this).text();
        if (title != "Actions")
            $(this).html('<input type="text" class="form-control" placeholder="' +
                title + '" size="15" />');
        else
            $(this).html('');
    });

    //var table = $('#users_table').DataTable( {
    //$('#users_table').dataTable( {
    var table = $('#users_table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "bServerSide": true,
        "ordering": false,
        "autoWidth": false,
        "sAjaxSource": "<?php echo base_url(); ?>datatables/data_tables_ajax/tb_mas_users/null/null/<?php echo $course."/".$postreg."/".$postyear."/".$gender."/".$graduate."/".$scholarship."/".$registered."/".$sem; ?>",
        "aoColumnDefs": [{
                "aTargets": [0],
                "bVisible": false
            },
            {
                "aTargets": [1],
                "bVisible": false
            },
            {
                "aTargets": [3],
                "mData": null,
                "bSortable": false,
                "mRender": function(data, type, row, meta) {
                    return '<a href="<?php echo base_url(); ?>unity/student_viewer/' +
                        row[0] + '">' + row[3] + '</a>';
                }
            },
        ],
        "aaSorting": [
            [3, 'asc']
        ],
        "deferLoading": 0,
        "language": {
            "emptyTable": "Search to display records"
        },
        "fnDrawCallback": function() {
            $(".trash-item").click(function(e) {
                conf = confirm("Are you sure you want to delete?");
                if (conf) {
                    $(".loading-img").show();
                    $(".overlay").show();
                    var id = $(this).attr('rel');
                    var parent = $(this).parent().parent().parent()
                        .parent().parent();
                    var data = {
                        'table': 'tb_mas_users',
                        'id': id
                    };
                    $.ajax({
                        'url': '<?php echo base_url(); ?>index.php/student/delete_student',
                        'method': 'post',
                        'data': data,
                        'dataType': 'json',
                        'success': function(ret) {
                            if (ret.message == "failed") {
                                $(".alert").show();
                                setTimeout(function() {
                                    $(".alert")
                                        .hide(
                                            'fade', {},
                                            500)
                                }, 3000);
                            } else
                                parent.hide();

                            $(".loading-img").hide();
                            $(".overlay").hide();
                        }
                    });
                }
            });

        },
    });

    //Apply the search
    table.columns().every(function() {
        var that = this;

        // $( 'input', this.footer() ).on( 'keyup change', function () {
        //     if ( that.search() !== this.value ) {
        //         that
        //             .search( this.value )
        //             //.search( "^" + $(this).val() + "$", true, false, true )
        //             .draw();
        //     }
        // } );

        $('input', this.header()).on('keyup change', function() {
            if (that.search() !== this.value) {
                that
                    .search(this.value)
                    //.search( "^" + $(this).val() + "$", true, false, true )
                    .draw();
            }
        });

    });



});
</script>