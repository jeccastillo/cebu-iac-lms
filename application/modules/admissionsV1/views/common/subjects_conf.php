<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script type="text/javascript">
$(document).ready(function() {

    var daterange = "";
    var filter_status = $("#status_filter").val();
    
    var dtable = $('#subjects-table').dataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000,2000,10000],
        "bProcessing": true,
        "bServerSide": true,
        // "sAjaxSource": "http://localhost:8004/api/v1/admissions/applications",
        ajax: function(data, callback, settings) {
            var s_column = "last_name";                        
            filter_status = $("#status_filter").val();
            switch(data.order[0].column){
                case 1:
                    s_column = "created_at";
                break;
                case 2:
                    s_column = "date_inteviewed";
                break;
                case 3:
                    s_column = "date_registered";
                break;
                case 4:
                    s_column = "date_enrolled";
                break;
                case 5:
                    s_column = "last_name";
                break;
                case 6:
                    s_column = "first_name";
                break;                
                case 7:
                    s_column = "program";
                break;                
                case 8:
                    s_column = "status"
                break;
                
            }
            $.get(
                api_url + "admissions/applications"+daterange, {
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
                    $("#print_form").show();
                    $("#print_form").click(function(e){
                        e.preventDefault();
                        // The rest of this code assumes you are not using a library.
                        // It can be made less verbose if you use one.
                        const form = document.createElement('form');
                        form.method = "post";
                        form.action = "<?php echo base_url() ?>excel/export_leads";
                        form.dataType = "json";

                        
                        const hiddenField = document.createElement('input');
                        hiddenField.type = 'hidden';
                        hiddenField.name = 'data';
                        hiddenField.value = JSON.stringify(json.data);

                        form.appendChild(hiddenField);
                        

                        document.body.appendChild(form);
                        form.submit();
                    });
                                        
                }
            );
        },
        "aoColumnDefs": [{
                "aTargets": [9],
                "mData": null,
                "bSortable": false,
                "mRender": function(data, type, row, meta) {
                    return '<?php echo $d_open; ?><li><a target="_blank" href="<?php echo base_url(); ?>admissionsV1/view_lead/'
                        +row.slug 
                        +'">View Details</a></li>'
                        +'<li><a target="_blank" href="<?php echo base_url(); ?>finance/manualPay/'
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
                data: "date_interviewed"
            },
            {
                data: "date_reserved"
            },
            {
                data: "date_enrolled"
            },
            {
                data: "last_name"
            },
            {
                data: "first_name"
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

    $('#daterange-btn-users').daterangepicker(
    {
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract('days', 1), moment().subtract('days', 1)],
            'Last 7 Days': [moment().subtract('days', 6), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract('month', 1).startOf('month'), moment().subtract('month',1).endOf('month')]
        },
        startDate: moment().subtract('days', 29),
        endDate: moment()
    },
    function(start, end) {
        daterange = "?start=" + start.format('YYYY-MM-D') + '&end=' + end.format('YYYY-MM-D') +'&range_field='+$("#range-to-select").val();
        dtable.fnDraw(false);   
    }
    );  

    $("#status_filter").on('change',function(e){
        
        dtable.fnDraw(false);
    });

    $("#select-term-leads").on('change', function(e){
        const term = $(this).val();
        document.location = "<?php echo base_url()."admissionsV1/view_all_leads/"; ?>"+term;
    });



});
</script>