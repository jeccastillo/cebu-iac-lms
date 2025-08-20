<script>
$(document).ready(function() {
    // Initialize DataTables for reservations table
    if ($('#reservationsTable').length) {
        $('#reservationsTable').DataTable({
            "responsive": true,
            "order": [[ 0, "desc" ]], // Order by date descending
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            "columnDefs": [
                {
                    "targets": [6], // Actions column
                    "orderable": false,
                    "searchable": false
                }
            ],
            "language": {
                "search": "Search reservations:",
                "lengthMenu": "Show _MENU_ reservations per page",
                "info": "Showing _START_ to _END_ of _TOTAL_ reservations",
                "infoEmpty": "No reservations found",
                "infoFiltered": "(filtered from _MAX_ total reservations)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                },
                "emptyTable": "No reservations available"
            },
            "dom": 'Bfrtip',
            "buttons": [
                {
                    extend: 'copy',
                    text: '<i class="fa fa-copy"></i> Copy',
                    className: 'btn btn-default btn-sm'
                },
                {
                    extend: 'csv',
                    text: '<i class="fa fa-file-text-o"></i> CSV',
                    className: 'btn btn-default btn-sm'
                },
                {
                    extend: 'excel',
                    text: '<i class="fa fa-file-excel-o"></i> Excel',
                    className: 'btn btn-default btn-sm'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fa fa-file-pdf-o"></i> PDF',
                    className: 'btn btn-default btn-sm',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fa fa-print"></i> Print',
                    className: 'btn btn-default btn-sm'
                }
            ]
        });
    }

    // Reservation actions
    window.viewReservation = function(id) {
        // Load reservation details via AJAX
        $.ajax({
            url: '<?php echo base_url(); ?>reservation/get_reservation_details',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            beforeSend: function() {
                $('#reservationDetails').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>');
            },
            success: function(data) {
                if (data.success) {
                    var reservation = data.reservation;
                    var html = '<div class="row">';
                    html += '<div class="col-md-6">';
                    html += '<table class="table table-bordered">';
                    html += '<tr><th>Date:</th><td>' + formatDate(reservation.dteReservationDate) + '</td></tr>';
                    html += '<tr><th>Time:</th><td>' + formatTime(reservation.dteStartTime) + ' - ' + formatTime(reservation.dteEndTime) + '</td></tr>';
                    html += '<tr><th>Room:</th><td>' + reservation.strRoomCode + '</td></tr>';
                    html += '<tr><th>Purpose:</th><td>' + reservation.strPurpose + '</td></tr>';
                    html += '</table>';
                    html += '</div>';
                    html += '<div class="col-md-6">';
                    html += '<table class="table table-bordered">';
                    html += '<tr><th>Requested By:</th><td>' + reservation.strFirstname + ' ' + reservation.strLastname + '</td></tr>';
                    html += '<tr><th>Status:</th><td><span class="label ' + getStatusClass(reservation.enumStatus) + '">' + reservation.enumStatus.charAt(0).toUpperCase() + reservation.enumStatus.slice(1) + '</span></td></tr>';
                    html += '<tr><th>Created:</th><td>' + formatDateTime(reservation.dteCreated) + '</td></tr>';
                    if (reservation.strDescription) {
                        html += '<tr><th>Description:</th><td>' + reservation.strDescription + '</td></tr>';
                    }
                    html += '</table>';
                    html += '</div>';
                    html += '</div>';
                    
                    $('#reservationDetails').html(html);
                } else {
                    $('#reservationDetails').html('<div class="alert alert-danger">Error loading reservation details.</div>');
                }
            },
            error: function() {
                $('#reservationDetails').html('<div class="alert alert-danger">Error loading reservation details.</div>');
            }
        });
        
        $('#reservationModal').modal('show');
    };

    window.deleteReservation = function(id) {
        if (confirm('Are you sure you want to delete this reservation?')) {
            $.ajax({
                url: '<?php echo base_url(); ?>reservation/delete_reservation',
                type: 'POST',
                data: { id: id },
                dataType: 'json',
                success: function(data) {
                    if (data.message == 'success') {
                        showAlert('Reservation deleted successfully!', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 1500);
                    } else {
                        showAlert('Error deleting reservation', 'error');
                    }
                },
                error: function() {
                    showAlert('Error deleting reservation', 'error');
                }
            });
        }
    };

    window.approveReservation = function(id) {
        if (confirm('Are you sure you want to approve this reservation?')) {
            updateReservationStatus(id, 'approved');
        }
    };

    window.rejectReservation = function(id) {
        var remarks = prompt('Please enter rejection reason (optional):');
        if (confirm('Are you sure you want to reject this reservation?')) {
            updateReservationStatus(id, 'rejected', remarks);
        }
    };

    function updateReservationStatus(id, status, remarks) {
        var data = {
            id: id,
            status: status
        };
        
        if (remarks) {
            data.remarks = remarks;
        }

        $.ajax({
            url: '<?php echo base_url(); ?>reservation/approve_reservation',
            type: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                if (response.message == 'success') {
                    showAlert('Reservation ' + status + ' successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('Error ' + (status === 'approved' ? 'approving' : 'rejecting') + ' reservation', 'error');
                }
            },
            error: function() {
                showAlert('Error ' + (status === 'approved' ? 'approving' : 'rejecting') + ' reservation', 'error');
            }
        });
    }

    // Utility functions
    function formatDate(dateString) {
        var date = new Date(dateString);
        return date.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        });
    }

    function formatTime(timeString) {
        var time = new Date('2000-01-01 ' + timeString);
        return time.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit',
            hour12: true 
        });
    }

    function formatDateTime(dateTimeString) {
        var date = new Date(dateTimeString);
        return date.toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function getStatusClass(status) {
        var classes = {
            'approved': 'label-success',
            'pending': 'label-warning',
            'rejected': 'label-danger',
            'cancelled': 'label-default'
        };
        return classes[status] || 'label-default';
    }

    function showAlert(message, type) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var icon = type === 'success' ? 'fa-check' : 'fa-times';
        
        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" id="tempAlert">';
        alertHtml += '<button type="button" class="close" data-dismiss="alert">&times;</button>';
        alertHtml += '<i class="fa ' + icon + '"></i> ' + message;
        alertHtml += '</div>';
        
        // Remove existing alerts
        $('#tempAlert').remove();
        
        // Add new alert at the top of content
        $('.content').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('#tempAlert').fadeOut();
        }, 5000);
    }

    // Status filter functionality
    $('#statusFilter').on('change', function() {
        var status = $(this).val();
        var table = $('#reservationsTable').DataTable();
        
        if (status === '') {
            table.column(5).search('').draw(); // Column 5 is status
        } else {
            table.column(5).search(status).draw();
        }
    });

    // Date range filter functionality
    $('#dateFromFilter, #dateToFilter').on('change', function() {
        var dateFrom = $('#dateFromFilter').val();
        var dateTo = $('#dateToFilter').val();
        
        // Custom date range filtering would need to be implemented
        // This is a placeholder for the functionality
        filterByDateRange(dateFrom, dateTo);
    });

    function filterByDateRange(dateFrom, dateTo) {
        var table = $('#reservationsTable').DataTable();
        
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var dateCol = data[0]; // Date column
                var date = new Date(dateCol);
                var from = dateFrom ? new Date(dateFrom) : null;
                var to = dateTo ? new Date(dateTo) : null;
                
                if (from && date < from) return false;
                if (to && date > to) return false;
                
                return true;
            }
        );
        
        table.draw();
        
        // Remove the filter after drawing
        $.fn.dataTable.ext.search.pop();
    }

    // Bulk actions functionality
    $('#selectAll').on('change', function() {
        $('.reservation-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActions();
    });

    $('.reservation-checkbox').on('change', function() {
        updateBulkActions();
    });

    function updateBulkActions() {
        var checkedCount = $('.reservation-checkbox:checked').length;
        if (checkedCount > 0) {
            $('#bulkActions').show();
            $('#selectedCount').text(checkedCount);
        } else {
            $('#bulkActions').hide();
        }
    }

    // Bulk approve
    $('#bulkApprove').on('click', function() {
        var selectedIds = [];
        $('.reservation-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length > 0 && confirm('Are you sure you want to approve ' + selectedIds.length + ' reservation(s)?')) {
            bulkUpdateStatus(selectedIds, 'approved');
        }
    });

    // Bulk reject
    $('#bulkReject').on('click', function() {
        var selectedIds = [];
        $('.reservation-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });
        
        if (selectedIds.length > 0) {
            var remarks = prompt('Please enter rejection reason (optional):');
            if (confirm('Are you sure you want to reject ' + selectedIds.length + ' reservation(s)?')) {
                bulkUpdateStatus(selectedIds, 'rejected', remarks);
            }
        }
    });

    function bulkUpdateStatus(ids, status, remarks) {
        $.ajax({
            url: '<?php echo base_url(); ?>reservation/bulk_update_status',
            type: 'POST',
            data: {
                ids: ids,
                status: status,
                remarks: remarks || ''
            },
            dataType: 'json',
            success: function(response) {
                if (response.message == 'success') {
                    showAlert(ids.length + ' reservation(s) ' + status + ' successfully!', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    showAlert('Error updating reservations', 'error');
                }
            },
            error: function() {
                showAlert('Error updating reservations', 'error');
            }
        });
    }

    // Auto-refresh functionality (optional)
    var autoRefresh = false;
    var refreshInterval;

    $('#autoRefresh').on('change', function() {
        autoRefresh = $(this).is(':checked');
        
        if (autoRefresh) {
            refreshInterval = setInterval(function() {
                location.reload();
            }, 60000); // Refresh every minute
        } else {
            clearInterval(refreshInterval);
        }
    });

    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // Ctrl+R for refresh
        if (e.ctrlKey && e.keyCode === 82) {
            e.preventDefault();
            location.reload();
        }
        
        // Escape to close modals
        if (e.keyCode === 27) {
            $('.modal').modal('hide');
        }
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Initialize popovers
    $('[data-toggle="popover"]').popover();
});

// Export functions for external use
window.ReservationManager = {
    viewReservation: viewReservation,
    deleteReservation: deleteReservation,
    approveReservation: approveReservation,
    rejectReservation: rejectReservation,
    refreshTable: function() {
        $('#reservationsTable').DataTable().ajax.reload();
    }
};
</script>

<style>
/* Custom styles for reservation management */
.reservation-actions {
    white-space: nowrap;
}

.reservation-status {
    font-weight: bold;
}

.bulk-actions {
    background-color: #f9f9f9;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 15px;
    display: none;
}

.filter-section {
    background-color: #f5f5f5;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.reservation-checkbox {
    margin-right: 5px;
}

.table-responsive {
    overflow-x: auto;
}

.dataTables_wrapper .dataTables_filter {
    float: right;
    text-align: right;
}

.dataTables_wrapper .dataTables_length {
    float: left;
}

.dataTables_wrapper .dataTables_info {
    clear: both;
    float: left;
    padding-top: 6px;
}

.dataTables_wrapper .dataTables_paginate {
    float: right;
    text-align: right;
    padding-top: 0px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        float: none;
        text-align: center;
        margin-bottom: 10px;
    }
    
    .reservation-actions .btn {
        margin-bottom: 5px;
    }
}

/* Status-specific styling */
.label-success {
    background-color: #5cb85c;
}

.label-warning {
    background-color: #f0ad4e;
}

.label-danger {
    background-color: #d9534f;
}

.label-default {
    background-color: #777;
}

/* Loading states */
.loading {
    opacity: 0.6;
    pointer-events: none;
}

.loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 20px;
    height: 20px;
    margin: -10px 0 0 -10px;
    border: 2px solid #ccc;
    border-top-color: #333;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>
