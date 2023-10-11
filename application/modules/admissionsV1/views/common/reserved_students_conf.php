<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script type="text/javascript">
$(document).ready(function() {    
    
    var dtable = $('#subjects-table').dataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "bServerSide": true,
        // "sAjaxSource": "http://localhost:8004/api/v1/admissions/applications",
        ajax: function(data, callback, settings) {
            var s_column = "last_name";                        
            filter_status = 'Reserved';
            switch(data.order[0].column){
                case 1:
                    s_column = "created_at";
                break;
                case 2:
                    s_column = "last_name";
                break;
                case 3:
                    s_column = "first_name";
                break;
                case 4:
                    s_column = "email";
                break;
                case 5:
                    s_column = "program";
                break;
                case 6:
                    s_column = "status";
                break;
            }
            $.get(
                api_url + "admissions/applications", {
                    limit: data.length,
                    page: data.start / data.length + 1,
                    search_data: data.search.value,
                    search_field: "first_name",
                    count_content: data.length,
                    sort_field: s_column,
                    order_by: data.order[0].dir,
                    filter: filter_status,
                    current_sem: <?php echo $current_sem; ?>,
                    campus: '<?php echo $campus; ?>',
                },
                function(json) {
                    callback({
                        recordsTotal: json.meta.to,
                        recordsFiltered: json.meta.total,
                        data: json.data
                    });

                    console.log(data);
                }
            );
        },
        "aoColumnDefs": [{
                "aTargets": [7],
                "mData": null,
                "bSortable": false,
                "mRender": function(data, type, row, meta) {
                    return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>admissionsV1/view_lead/'
                        +row.slug 
                        +'">View Details</a></li>'
                        +'<li><a href="<?php echo base_url(); ?>finance/manualPay/'
                        + row.slug
                        +'">Finance Viewer</a></li></ul></div>';
                }
            },
            {
                "aTargets": [0],
                "bVisible": false
            },
        ],

        columns: [{
                data: "id"
            },
            {
                data: "date"
            },
            {
                data: "last_name"
            },
            {
                data: "first_name"
            },
            {
                data: "email"
            },
            {
                data: "program"
            },
            {
                data: "status"
            }
        ],
        "aaSorting": [
            [1, 'asc']
        ],
        "fnDrawCallback": function() {
            $("#")
            $(".trash-item").click(function(e) {
                conf = confirm("Are you sure you want to delete?");
                if (conf) {
                    $(".loading-img").show();
                    $(".overlay").show();
                    var id = $(this).attr('rel');
                    var parent = $(this).parent().parent().parent().parent().parent();
                    var code = parent.children(':first-child').html();
                    var data = {
                        'id': id,
                        'code': code
                    };
                    $.ajax({
                        'url': '<?php echo base_url(); ?>index.php/subject/delete_subject',
                        'method': 'post',
                        'data': data,
                        'dataType': 'json',
                        'success': function(ret) {
                            if (ret.message == "failed") {
                                $("#alert-text").html('<b>Alert! ' + code +
                                    '</b> cannot be deleted it is connected to classlist.'
                                )
                                $(".alert").show();
                                setTimeout(function() {
                                    $(".alert").hide('fade', {}, 500)
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

    $("#status_filter").on('change',function(e){
        
        dtable.fnDraw(false);
    })

    $("#select-term-reserved").on('change', function(e){
        const term = $(this).val();
        document.location = "<?php echo base_url()."admissionsV1/view_reserved_leads/"; ?>"+term;
    });

});
</script>