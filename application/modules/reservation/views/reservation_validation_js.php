<script>
$(document).ready(function() {
    // Form validation for reservation forms
    $('#reservationForm, #editReservationForm').validate({
        rules: {
            dteReservationDate: {
                required: true,
                date: true,
                minDate: 0 // Today or future dates only
            },
            dteStartTime: {
                required: true
            },
            dteEndTime: {
                required: true
            },
            intRoomID: {
                required: true
            },
            strPurpose: {
                required: true,
                minlength: 3,
                maxlength: 255
            },
            strDescription: {
                maxlength: 500
            }
        },
        messages: {
            dteReservationDate: {
                required: "Please select a reservation date",
                date: "Please enter a valid date",
                minDate: "Reservation date must be today or in the future"
            },
            dteStartTime: {
                required: "Please select a start time"
            },
            dteEndTime: {
                required: "Please select an end time"
            },
            intRoomID: {
                required: "Please select a room"
            },
            strPurpose: {
                required: "Please enter the purpose of reservation",
                minlength: "Purpose must be at least 3 characters long",
                maxlength: "Purpose cannot exceed 255 characters"
            },
            strDescription: {
                maxlength: "Description cannot exceed 500 characters"
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('help-block');
            element.parent().addClass('has-error');
            error.insertAfter(element);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).parent().addClass('has-error');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).parent().removeClass('has-error');
        },
        submitHandler: function(form) {
            // Additional validation before submission
            if (validateTimeRange() && validateReservationDuration()) {
                form.submit();
            }
        }
    });

    // Custom validation methods
    $.validator.addMethod("minDate", function(value, element) {
        var today = new Date();
        var selectedDate = new Date(value);
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);
        return selectedDate >= today;
    }, "Date must be today or in the future");

    // Time range validation
    function validateTimeRange() {
        var startTime = $('#dteStartTime').val();
        var endTime = $('#dteEndTime').val();
        
        if (startTime && endTime) {
            if (startTime >= endTime) {
                showValidationError('dteEndTime', 'End time must be after start time');
                return false;
            }
        }
        return true;
    }

    // Minimum duration validation (30 minutes)
    function validateReservationDuration() {
        var startTime = $('#dteStartTime').val();
        var endTime = $('#dteEndTime').val();
        
        if (startTime && endTime) {
            var start = new Date('2000-01-01 ' + startTime);
            var end = new Date('2000-01-01 ' + endTime);
            var diffMinutes = (end - start) / (1000 * 60);
            
            if (diffMinutes < 30) {
                showValidationError('dteEndTime', 'Reservation must be at least 30 minutes long');
                return false;
            }
            
            if (diffMinutes > 480) { // 8 hours max
                showValidationError('dteEndTime', 'Reservation cannot exceed 8 hours');
                return false;
            }
        }
        return true;
    }

    // Show validation error
    function showValidationError(fieldId, message) {
        var field = $('#' + fieldId);
        var parent = field.parent();
        
        // Remove existing error
        parent.find('.help-block').remove();
        parent.removeClass('has-error');
        
        // Add new error
        parent.addClass('has-error');
        field.after('<span class="help-block">' + message + '</span>');
    }

    // Real-time validation on field changes
    $('#dteStartTime, #dteEndTime').on('change', function() {
        // Clear previous errors
        $(this).parent().removeClass('has-error');
        $(this).parent().find('.help-block').remove();
        
        // Validate time range and duration
        validateTimeRange();
        validateReservationDuration();
    });

    // Date validation
    $('#dteReservationDate').on('change', function() {
        var selectedDate = new Date($(this).val());
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);
        
        if (selectedDate < today) {
            showValidationError('dteReservationDate', 'Reservation date must be today or in the future');
        } else {
            $(this).parent().removeClass('has-error');
            $(this).parent().find('.help-block').remove();
        }
    });

    // Room selection validation
    $('#intRoomID').on('change', function() {
        if ($(this).val()) {
            $(this).parent().removeClass('has-error');
            $(this).parent().find('.help-block').remove();
        }
    });

    // Purpose validation
    $('#strPurpose').on('input', function() {
        var purpose = $(this).val().trim();
        var parent = $(this).parent();
        
        // Clear previous errors
        parent.removeClass('has-error');
        parent.find('.help-block').remove();
        
        if (purpose.length < 3 && purpose.length > 0) {
            showValidationError('strPurpose', 'Purpose must be at least 3 characters long');
        } else if (purpose.length > 255) {
            showValidationError('strPurpose', 'Purpose cannot exceed 255 characters');
        }
    });

    // Description validation
    $('#strDescription').on('input', function() {
        var description = $(this).val();
        var parent = $(this).parent();
        
        // Clear previous errors
        parent.removeClass('has-error');
        parent.find('.help-block').remove();
        
        if (description.length > 500) {
            showValidationError('strDescription', 'Description cannot exceed 500 characters');
        }
        
        // Update character count if counter exists
        var counter = parent.find('.char-counter');
        if (counter.length === 0) {
            parent.append('<small class="char-counter text-muted">' + description.length + '/500 characters</small>');
        } else {
            counter.text(description.length + '/500 characters');
        }
    });

    // Business hours validation (optional - can be configured)
    function validateBusinessHours() {
        var startTime = $('#dteStartTime').val();
        var endTime = $('#dteEndTime').val();
        
        if (startTime && endTime) {
            var businessStart = '07:00';
            var businessEnd = '22:00';
            
            if (startTime < businessStart || endTime > businessEnd) {
                return {
                    valid: false,
                    message: 'Reservations are only allowed between 7:00 AM and 10:00 PM'
                };
            }
        }
        
        return { valid: true };
    }

    // Weekend validation (optional)
    function validateWeekend() {
        var selectedDate = new Date($('#dteReservationDate').val());
        var dayOfWeek = selectedDate.getDay();
        
        // 0 = Sunday, 6 = Saturday
        if (dayOfWeek === 0 || dayOfWeek === 6) {
            return {
                valid: false,
                message: 'Weekend reservations require special approval'
            };
        }
        
        return { valid: true };
    }

    // Initialize tooltips for help text
    $('[data-toggle="tooltip"]').tooltip();

    // Auto-format time inputs
    $('.time-input').on('blur', function() {
        var time = $(this).val();
        if (time && time.length === 5) {
            // Ensure proper time format
            var parts = time.split(':');
            if (parts.length === 2) {
                var hours = parseInt(parts[0]);
                var minutes = parseInt(parts[1]);
                
                if (hours >= 0 && hours <= 23 && minutes >= 0 && minutes <= 59) {
                    $(this).val(
                        (hours < 10 ? '0' : '') + hours + ':' + 
                        (minutes < 10 ? '0' : '') + minutes
                    );
                }
            }
        }
    });

    // Prevent form submission on Enter key in text inputs (except textarea)
    $('#reservationForm input[type="text"], #reservationForm input[type="time"], #reservationForm input[type="date"], #reservationForm select').on('keypress', function(e) {
        if (e.which === 13) { // Enter key
            e.preventDefault();
            return false;
        }
    });

    // Form reset functionality
    $('#resetForm').on('click', function() {
        $('#reservationForm')[0].reset();
        $('.has-error').removeClass('has-error');
        $('.help-block').remove();
        $('.char-counter').remove();
    });
});

// Global function for external validation calls
function validateReservationForm() {
    return $('#reservationForm').valid();
}

// Global function to reset form validation
function resetFormValidation() {
    var validator = $('#reservationForm').validate();
    validator.resetForm();
    $('.has-error').removeClass('has-error');
    $('.help-block').remove();
}
</script>

<style>
.has-error .form-control {
    border-color: #dd4b39;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 6px #f4c2c2;
}

.has-error .help-block {
    color: #dd4b39;
    font-size: 12px;
    margin-top: 5px;
}

.char-counter {
    display: block;
    margin-top: 5px;
    font-size: 11px;
}

.time-input {
    width: 100%;
}

.form-group.required .control-label:after {
    content: " *";
    color: red;
}

.validation-summary {
    margin-bottom: 20px;
}

.validation-summary .alert {
    margin-bottom: 10px;
}

/* Custom styles for better UX */
.form-control:focus {
    border-color: #3c8dbc;
    box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 6px rgba(60, 141, 188, 0.6);
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Loading states */
.btn .fa-spinner {
    margin-right: 5px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .form-group {
        margin-bottom: 15px;
    }
    
    .help-block {
        font-size: 11px;
    }
}
</style>
