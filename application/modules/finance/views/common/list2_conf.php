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
var other = <?php echo $other; ?>;

var api = 'finance/transactions_per_term';
if(other != 0)
    api = 'finance/transactions_per_term_other';
    

$(document).ready(function() {
    var filter_status = $("#status_filter").val();    
    daterange = "?start=<?php echo date("Y-m-d") ?>&end=<?php echo date("Y-m-d") ?>";
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
                api_url + api +daterange, {
                    limit: 100,                    
                    page: data.start / data.length + 1,
                    search_data: data.search.value,
                    search_field: $("#search_field").val(),
                    count_content: data.length,
                    sort_field: s_column,
                    order_by: data.order[0].dir,
                    filter: filter_status,      
                    campus: '<?php echo $campus; ?>'              
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
        "aoColumnDefs": [
            
            {
                "aTargets": [12],
                "mData": null,
                "bSortable": false,
                "mRender": function(data, type, row, meta) {
                    return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>finance/manualPay/'
                        + row.slug
                        +'">Finance Viewer</a></li>'
                        +'<li><a href="<?php echo base_url(); ?>finance/remove_or_print/'
                        + row.or_number
                        +'">Delete OR Print</a></li>'
                        // +'<li><a href="#" class="print-or" data-student-name="'
                        // + row.student_name.toUpperCase() +'" '
                        // +'data-slug = " " '
                        // +'data-cashier-id = "'+ row.cashier_id +'" '
                        // +'data-campus ="' + row.campus +'" '
                        // +'data-or-number = "'+ row.or_number +'" '
                        // +'data-description = "'+ row.description +'" '
                        // +'data-total-amount-due = "'+ row.total_amount_due +'" '
                        // +'data-transaction-date = "'+ row.updated_at +'" '
                        // +'data-remarks = "'+ row.remarks +'" '                        
                        // +'data-student-id = '+ row.applicant_id +'" '
                        // +'data-student-address = '+ row.student_address +'" '                                                
                        // +'data-is-cash = "'+ row.is_cash +'" '
                        // +'data-check-number = "'+ row.check_number +'" '
                        // +'">Print OR</a></li>'
                        +'</ul></div>';
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
                        return mode;
                    }
                    if(row.remarks){
                        if(row.remarks == "Paynamics")
                            return row.request_id;
                    }
                    
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
            $(".print-or").click(function(e){
                $("#student_name").val($(this).attr('data-student-name'));
                $("#cashier_id").val($(this).attr('data-cashier-id'));
                $("#student_id").val($(this).attr('data-student-id'));
                $("#student_address").val($(this).attr('data-student-address'));
                $("#is_cash").val($(this).attr('data-is-cash'));
                $("#check_number").val($(this).attr('data-check-number'));                
                $("#or_number").val($(this).attr('data-or-number'));
                $("#remarks").val($(this).attr('data-remarks'));
                $("#description").val($(this).attr('data-description'));                                                
                $("#total_amount_due").val($(this).attr('data-total-amount-due'));                
                $("#name").val($(this).attr('data-student-name'));                
                $("#transaction_date").val($(this).attr('data-transaction-date'));                                      
                $("#print_or").submit();
            });

        },
    });

    $("#status_filter").on('change',function(e){
        
        dtable.fnDraw(false);
    })

});
</script>