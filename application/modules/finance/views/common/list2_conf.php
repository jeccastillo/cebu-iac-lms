<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script type="text/javascript">
var daterange = "";
function formatDate(date) {
    var d = new Date(date),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) 
        month = '0' + month;
    if (day.length < 2) 
        day = '0' + day;

    return [year, month, day].join('-');
}
$(document).ready(function() {
    var filter_status = $("#status_filter").val();    
    daterange = "?start=" + <?php echo date("Y-m-d") ?> + '&end=' + <?php echo date("Y-m-d") ?>;
    $("#print_form").hide();
    $('#chooseDate').daterangepicker(
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
        daterange = "?start=" + start.format('YYYY-MM-D') + '&end=' + end.format('YYYY-MM-D');
        dtable.fnDraw(false);   
    }
    );     
            
    
    var dtable = $('#subjects-table').dataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "bServerSide": true,
        // "sAjaxSource": "http://localhost:8004/api/v1/admissions/applications",
        ajax: function(data, callback, settings) {
            var s_column = "or_number";                        
            filter_status = $("#status_filter").val();           
            $.get(
                api_url + "finance/transactions_per_term"+daterange, {
                    limit: 100,
                    page: data.start / data.length + 1,
                    search_data: data.search.value,
                    search_field: "student_name",
                    count_content: data.length,
                    sort_field: s_column,
                    order_by: data.order[0].dir,
                    filter: filter_status,
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
                        form.action = "<?php echo base_url() ?>excel/daily_collection_report";
                        form.dataType = "json";

                        
                        const hiddenField = document.createElement('input');
                        hiddenField.type = 'hidden';
                        hiddenField.name = 'data';
                        hiddenField.value = JSON.stringify(json.data);

                        form.appendChild(hiddenField);

                        const hiddenField2 = document.createElement('input');
                        hiddenField2.type = 'hidden';
                        hiddenField2.name = 'date';
                        hiddenField2.value = "<?php echo $date; ?>";

                        form.appendChild(hiddenField2);
                        

                        document.body.appendChild(form);
                        form.submit();
                    });
                                        
                }
            );
        },
        "aoColumnDefs": [{
                "aTargets": [12],
                "mData": null,
                "bSortable": false,
                "mRender": function(data, type, row, meta) {
                    return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>finance/manualPay/'
                        + row.slug
                        +'">Finance Viewer</a></li></ul></div>';
                }
            },
            {
                "aTargets": [2],                                
                "mRender": function(data, type, row, meta) {
                    return '<a class="cashier-id" rel="'+row.cashier_id+'" href="#">'+row.cashier_id+'</a>';
                }
            },
            {
                "aTargets": [4],                                
                "mRender": function(data, type, row, meta) {
                    return String(row.or_number).padStart(5, '0');
                }
            }, 
            {
                "aTargets": [6],                                
                "mRender": function(data, type, row, meta) {
                    return row.student_name.toUpperCase();
                }
            },            
            {
                "aTargets": [7],                                
                "mRender": function(data, type, row, meta) {                    
                    var mode = "Online";
                    if(row.is_cash){
                        switch(row.is_cash){
                            case 0:
                                mode = "Check";
                                break;
                            case 1:
                                mode = "Cash";
                                break;
                            case 2:
                                mode = "Credit Card";
                                break;
                            case 3:
                                mode = "Debit Card";
                                break;  
                            case 4:     
                                mode = "Online";
                                break;                   

                        }
                    }
                    return mode;
                }
            },
            {
                "aTargets": [0],
                "bVisible": false
            },
            {
                "aTargets": [1],
                "bVisible": false
            },
        ],
       columns: [
            {
                data: "id"
            },
            {
                data: "slug"
            },
            {
                data: "cashier_id"
            },
            {
                data: "updated_at"
            },
            {
                data: "or_number"
            },
            {
                data: "student_information_id"
            },
            {
                data: "student_name"
            },        
            {
                data: "is_cash"
            }, 
            {
                data: "check_number"
            },            
            {
                data: "subtotal_order"
            },
            {
                data: "description"
            },      
            {
                data: "remarks"
            }     
           
        ],
        "aaSorting": [
            [2, 'asc']
        ],
        "fnDrawCallback": function() {          
            $(".cashier-id").click(function(e){
                var id = $(this).attr('rel');
                $.ajax({
                        'url': '<?php echo base_url(); ?>finance/cashier_details/'+id,
                        'method': 'get',                        
                        'dataType': 'json',
                        'success': function(data) {
                            var cashier_details = data.cashier_data;            
                            Swal.fire({
                                title: "Cashier",
                                text: cashier_details.strFirstname+" "+cashier_details.strLastname,
                                icon: "info"
                            })
                        }
                    });             

            });  
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

});
</script>