<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default btn-sm">Actions</button><button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var daterange = "";
    var filter_status = $("#status_filter").val();
    
    // Initialize DataTable with modern configuration
    var dtable = $('#subjects-table').DataTable({
        lengthMenu: [10, 20, 50, 100, 250, 500, 750, 1000],
        processing: true,
        serverSide: true,
        responsive: true,
        language: {
            processing: '<i class="fa fa-spinner fa-spin"></i> Loading data...',
            emptyTable: 'No applicants found',
            zeroRecords: 'No matching records found',
            info: 'Showing _START_ to _END_ of _TOTAL_ applicants',
            infoEmpty: 'Showing 0 to 0 of 0 applicants',
            infoFiltered: '(filtered from _MAX_ total applicants)',
            lengthMenu: 'Show _MENU_ applicants per page',
            search: 'Search applicants:',
            paginate: {
                first: 'First',
                last: 'Last',
                next: 'Next',
                previous: 'Previous'
            }
        },
        ajax: function(data, callback, settings) {
            // Hide any previous error messages
            $("#alert-container").addClass("d-none");
            
            // Determine sort column with corrected column mapping
            var s_column = "last_name";
            filter_status = $("#status_filter").val();
            
            switch (data.order[0].column) {
                case 1:
                    s_column = "created_at";
                    break;
                case 2:
                    s_column = "date_interviewed"; // Fixed typo
                    break;
                case 3:
                    s_column = "date_reserved";
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
                    s_column = "tos";
                    break;
                case 8:
                    s_column = "program";
                    break;
                case 9:
                    s_column = "status";
                    break;
            }
            
            // Make AJAX request with proper error handling
            $.get(api_url + "admissions/applications" + daterange, {
                limit: data.length,
                page: Math.floor(data.start / data.length) + 1,
                search_data: data.search.value,
                search_field: "first_name",
                count_content: data.length,
                sort_field: s_column,
                order_by: data.order[0].dir,
                filter: filter_status,
                current_sem: <?php echo $current_sem; ?>,
                campus: '<?php echo $campus; ?>'
            })
            .done(function(json) {
                // Success callback
                callback({
                    recordsTotal: json.meta.to || 0,
                    recordsFiltered: json.meta.total || 0,
                    data: json.data || []
                });
                
                // Show export button and setup click handler
                $("#print_form").show().off('click').on('click', function(e) {
                    e.preventDefault();
                    handleExportToExcel();
                });
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                // Error callback - show user-friendly error message
                var errorMsg = 'Failed to load data';
                if (jqXHR.status === 404) {
                    errorMsg = 'API endpoint not found';
                } else if (jqXHR.status === 500) {
                    errorMsg = 'Server error occurred';
                } else if (textStatus === 'timeout') {
                    errorMsg = 'Request timed out';
                }
                
                $("#alert-container")
                    .removeClass("d-none")
                    .find("#alert-text")
                    .text(errorMsg + '. Please try again or contact support.');
                
                // Return empty data to prevent DataTable errors
                callback({
                    recordsTotal: 0,
                    recordsFiltered: 0,
                    data: []
                });
                
                console.error('DataTable AJAX Error:', textStatus, errorThrown);
            });
        },
        columnDefs: [
            {
                targets: 10, // Actions column
                sortable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return '<?php echo $d_open; ?>' +
                        '<li><a target="_blank" href="<?php echo base_url(); ?>admissionsV1/view_lead_new/' + row.slug + '">' +
                        '<i class="fa fa-eye"></i> View Details</a></li>' +
                        '<li><a target="_blank" href="<?php echo base_url(); ?>finance/manualPay/' + row.slug + '">' +
                        '<i class="fa fa-money"></i> Finance Viewer</a></li>' +
                        '<li><a target="_blank" href="<?php echo base_url(); ?>admissionsV1/app_form/' + row.slug + '">' +
                        '<i class="fa fa-file-text"></i> Application Form</a></li>' +
                        '</ul></div>';
                }
            },
            {
                targets: 0, // Hide ID column
                visible: false
            }
        ],
        columns: [
            { data: "id" },
            { data: "date" },
            { data: "date_interviewed" },
            { data: "date_reserved" },
            { data: "date_enrolled" },
            { data: "last_name" },
            { data: "first_name" },
            { data: "tos" },
            { data: "program" },
            { data: "status" },
            { data: null } // Actions column
        ],
        order: [[1, 'desc']], // Default sort by date applied (newest first)
        drawCallback: function() {
            // Additional UI interactions after table draw
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
    
    // Export to Excel function
    function handleExportToExcel() {
        const currentTerm = $("#select-term-leads").val();
        const campus = '<?php echo $campus; ?>';
        
        // Show loading state
        $("#print_form").prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Exporting...');
        
        axios.get('https://smsapi.iacademy.edu.ph/api/v1/sms/admissions/student-info/view-applicants/' + currentTerm + '/' + campus)
            .then((response) => {
                const students = response.data.data;
                const exportUrl = "<?php echo base_url(); ?>excel/export_leads";
                
                // Create and submit form
                const form = $('<form target="_blank" method="POST" style="display:none;"></form>')
                    .attr('action', exportUrl)
                    .appendTo(document.body);
                
                $('<input type="hidden" name="applicants" />')
                    .val(JSON.stringify(students))
                    .appendTo(form);
                
                form.submit();
                form.remove();
                
                // Reset button state
                $("#print_form").prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> Export to Excel');
            })
            .catch((error) => {
                console.error('Export error:', error);
                $("#alert-container")
                    .removeClass("d-none")
                    .find("#alert-text")
                    .text('Failed to export data. Please try again.');
                
                // Reset button state
                $("#print_form").prop('disabled', false).html('<i class="fa fa-file-excel-o"></i> Export to Excel');
            });
    }
    
    // Date range picker initialization
    $('#daterange-btn-users').daterangepicker({
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        startDate: moment().subtract(29, 'days'),
        endDate: moment(),
        locale: {
            format: 'YYYY-MM-DD'
        }
    }, function(start, end) {
        daterange = "?start=" + start.format('YYYY-MM-DD') + '&end=' + end.format('YYYY-MM-DD') + '&range_field=' + $("#range-to-select").val();
        
        // Update button text to show selected range
        $('#daterange-btn-users').html('<i class="fa fa-calendar"></i> ' + start.format('MMM D') + ' - ' + end.format('MMM D, YYYY') + ' <i class="fa fa-caret-down"></i>');
        
        // Reload table data
        dtable.ajax.reload();
    });
    
    // Status filter change handler
    $("#status_filter").on('change', function(e) {
        dtable.ajax.reload();
    });
    
    // Term selection change handler
    $("#select-term-leads").on('change', function(e) {
        const term = $(this).val();
        if (term) {
            window.location.href = "<?php echo base_url(); ?>admissionsV1/view_all_leads/" + term;
        }
    });
    
    // Clear date range button (optional enhancement)
    $(document).on('click', '.clear-daterange', function() {
        daterange = "";
        $('#daterange-btn-users').html('<i class="fa fa-calendar"></i> Choose Date Range <i class="fa fa-caret-down"></i>');
        dtable.ajax.reload();
    });
});
</script>
