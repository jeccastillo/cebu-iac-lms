<script type="text/javascript" src="<?php echo $js_dir; ?>Chart.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize enhanced dashboard functionality
    initializeEnhancedDashboard();
});

function initializeEnhancedDashboard() {
    // Initialize charts
    initializeProgramChart();
    initializeGradeChart();
    
    // Initialize interactive elements
    initializeQuickActions();
    initializeNotifications();
    
    // Initialize responsive features
    initializeResponsiveFeatures();
}

// Program Distribution Chart
function initializeProgramChart() {
    var ctx = document.getElementById('programChart');
    if (!ctx) return;
    
    var programData = <?php echo json_encode($faculty_program_stats); ?>;
    
    var labels = [];
    var data = [];
    var backgroundColors = [];
    
    // Generate colors and prepare data
    programData.forEach(function(program, index) {
        labels.push(program.strProgramCode);
        data.push(program.studentCount);
        
        // Generate vibrant colors
        var hue = (index * 137.508) % 360; // Golden angle approximation
        backgroundColors.push('hsla(' + hue + ', 70%, 60%, 0.8)');
    });
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom',
                labels: {
                    padding: 20,
                    usePointStyle: true
                }
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index];
                        var value = data.datasets[0].data[tooltipItem.index];
                        var total = data.datasets[0].data.reduce(function(a, b) { return a + b; }, 0);
                        var percentage = Math.round((value / total) * 100);
                        return label + ': ' + value + ' students (' + percentage + '%)';
                    }
                }
            },
            animation: {
                animateRotate: true,
                duration: 1000
            }
        }
    });
}

// Grade Distribution Chart
function initializeGradeChart() {
    var ctx = document.getElementById('gradeChart');
    if (!ctx) return;
    
    var gradeData = <?php echo json_encode($grade_distribution); ?>;
    
    var labels = [];
    var data = [];
    var backgroundColors = [];
    
    // Process grade data
    gradeData.forEach(function(grade) {
        labels.push(grade.floatFinalGrade);
        data.push(grade.count);
        
        // Color coding based on grade ranges
        var gradeValue = parseFloat(grade.floatFinalGrade);
        if (gradeValue >= 1.0 && gradeValue <= 1.5) {
            backgroundColors.push('#00a65a'); // Green for excellent
        } else if (gradeValue >= 1.75 && gradeValue <= 2.5) {
            backgroundColors.push('#3c8dbc'); // Blue for good
        } else if (gradeValue >= 2.75 && gradeValue <= 3.0) {
            backgroundColors.push('#f39c12'); // Orange for satisfactory
        } else {
            backgroundColors.push('#dd4b39'); // Red for needs improvement
        }
    });
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Number of Students',
                data: data,
                backgroundColor: backgroundColors,
                borderColor: backgroundColors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1
                    },
                    scaleLabel: {
                        display: true,
                        labelString: 'Number of Students'
                    }
                }],
                xAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Grade'
                    }
                }]
            },
            legend: {
                display: false
            },
            tooltips: {
                callbacks: {
                    title: function(tooltipItems, data) {
                        return 'Grade: ' + tooltipItems[0].xLabel;
                    },
                    label: function(tooltipItem, data) {
                        return 'Students: ' + tooltipItem.yLabel;
                    }
                }
            },
            animation: {
                duration: 1000,
                easing: 'easeOutQuart'
            }
        }
    });
}

// Quick Actions functionality
function initializeQuickActions() {
    // Add hover effects and click handlers for quick action buttons
    $('.btn-app').hover(
        function() {
            $(this).addClass('animated pulse');
        },
        function() {
            $(this).removeClass('animated pulse');
        }
    );
    
    // Add click tracking for analytics
    $('.btn-app').click(function() {
        var action = $(this).find('i').next().text().trim();
        console.log('Quick action clicked:', action);
        // You can add analytics tracking here
    });
}

// Notifications and alerts
function initializeNotifications() {
    // Check for pending grades and show notifications
    var pendingGrades = <?php echo $pending_grades; ?>;
    
    if (pendingGrades > 0) {
        showNotification('You have ' + pendingGrades + ' class(es) with pending grade submissions.', 'warning');
    }
    
    // Check for today's schedule
    var todaySchedule = <?php echo json_encode($today_schedule); ?>;
    
    if (todaySchedule.length > 0) {
        showNotification('You have ' + todaySchedule.length + ' class(es) scheduled for today.', 'info');
    }
}

// Show notification function
function showNotification(message, type) {
    var alertClass = 'alert-info';
    var icon = 'fa-info-circle';
    
    switch(type) {
        case 'warning':
            alertClass = 'alert-warning';
            icon = 'fa-exclamation-triangle';
            break;
        case 'success':
            alertClass = 'alert-success';
            icon = 'fa-check-circle';
            break;
        case 'danger':
            alertClass = 'alert-danger';
            icon = 'fa-times-circle';
            break;
    }
    
    var notification = $('<div class="alert ' + alertClass + ' alert-dismissible fade in" role="alert">' +
        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '<i class="fa ' + icon + '"></i> ' + message +
        '</div>');
    
    $('.content').prepend(notification);
    
    // Auto-hide after 5 seconds
    setTimeout(function() {
        notification.fadeOut();
    }, 5000);
}

// Responsive features
function initializeResponsiveFeatures() {
    // Adjust chart heights on window resize
    $(window).resize(function() {
        Chart.helpers.each(Chart.instances, function(instance) {
            instance.resize();
        });
    });
    
    // Mobile-friendly table scrolling
    if ($(window).width() < 768) {
        $('.table-responsive').addClass('mobile-scroll');
    }
    
    // Collapsible boxes on mobile
    if ($(window).width() < 576) {
        $('.box').addClass('collapsed-box');
        $('.box .btn-box-tool').click();
    }
}

// Real-time updates (if needed)
function updateDashboardData() {
    // This function can be called periodically to update dashboard data
    // You can implement AJAX calls here to fetch updated statistics
    
    $.ajax({
        url: '<?php echo base_url(); ?>unity/get_dashboard_updates',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update statistics
                updateStatistics(response.data);
            }
        },
        error: function() {
            console.log('Failed to fetch dashboard updates');
        }
    });
}

// Update statistics function
function updateStatistics(data) {
    // Update small boxes with new data
    if (data.my_classes_count !== undefined) {
        $('.small-box.bg-aqua .inner h3').text(data.my_classes_count);
    }
    
    if (data.total_students_taught !== undefined) {
        $('.small-box.bg-green .inner h3').text(data.total_students_taught);
    }
    
    if (data.pending_grades !== undefined) {
        $('.small-box.bg-yellow .inner h3').text(data.pending_grades);
    }
    
    if (data.submitted_grades !== undefined) {
        $('.small-box.bg-red .inner h3').text(data.submitted_grades);
    }
}

// Enhanced table features
function initializeTableFeatures() {
    // Add sorting functionality to tables
    $('.table th').click(function() {
        var table = $(this).parents('table').eq(0);
        var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
        this.asc = !this.asc;
        if (!this.asc) {
            rows = rows.reverse();
        }
        for (var i = 0; i < rows.length; i++) {
            table.append(rows[i]);
        }
    });
}

// Table sorting comparer function
function comparer(index) {
    return function(a, b) {
        var valA = getCellValue(a, index);
        var valB = getCellValue(b, index);
        return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
    };
}

function getCellValue(row, index) {
    return $(row).children('td').eq(index).text();
}

// Initialize periodic updates (every 5 minutes)
setInterval(updateDashboardData, 300000);

// Animation classes for enhanced UX
$('.small-box').hover(function() {
    $(this).addClass('animated pulse');
}, function() {
    $(this).removeClass('animated pulse');
});

// Box collapse/expand animations
$('.box .btn-box-tool').click(function() {
    var box = $(this).closest('.box');
    setTimeout(function() {
        if (box.hasClass('collapsed-box')) {
            box.find('.box-body').slideUp();
        } else {
            box.find('.box-body').slideDown();
        }
    }, 100);
});

// Loading states for AJAX operations
function showLoading(element) {
    element.html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading...</div>');
}

function hideLoading(element, originalContent) {
    element.html(originalContent);
}

// Print functionality for reports
function printDashboard() {
    window.print();
}

// Export functionality (if needed)
function exportDashboardData() {
    // Implementation for exporting dashboard data to CSV/PDF
    console.log('Export functionality can be implemented here');
}

// Keyboard shortcuts
$(document).keydown(function(e) {
    // Ctrl+R for refresh
    if (e.ctrlKey && e.keyCode === 82) {
        e.preventDefault();
        location.reload();
    }
    
    // Ctrl+P for print
    if (e.ctrlKey && e.keyCode === 80) {
        e.preventDefault();
        printDashboard();
    }
});

// Initialize tooltips and popovers
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
});

// Custom animations for better UX
$.fn.extend({
    animateCss: function(animationName, callback) {
        var animationEnd = (function(el) {
            var animations = {
                animation: 'animationend',
                OAnimation: 'oAnimationEnd',
                MozAnimation: 'mozAnimationEnd',
                WebkitAnimation: 'webkitAnimationEnd',
            };

            for (var t in animations) {
                if (el.style[t] !== undefined) {
                    return animations[t];
                }
            }
        })(document.createElement('div'));

        this.addClass('animated ' + animationName).one(animationEnd, function() {
            $(this).removeClass('animated ' + animationName);

            if (typeof callback === 'function') callback();
        });

        return this;
    },
});

</script>

<!-- Additional CSS for animations and enhanced styling -->
<style>
@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.animated {
    animation-duration: 0.5s;
    animation-fill-mode: both;
}

.pulse {
    animation-name: pulse;
}

.mobile-scroll {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.table th {
    cursor: pointer;
    user-select: none;
}

.table th:hover {
    background-color: rgba(0,0,0,0.05);
}

.alert {
    border-radius: 6px;
    margin-bottom: 15px;
}

.box-body canvas {
    max-height: 300px;
}

@media print {
    .box-tools,
    .btn,
    .alert-dismissible .close {
        display: none !important;
    }
    
    .box {
        border: 1px solid #ddd !important;
        box-shadow: none !important;
    }
}

/* Loading spinner styles */
.fa-spinner {
    color: #3c8dbc;
}

/* Enhanced responsive design */
@media (max-width: 576px) {
    .content-header h1 {
        font-size: 24px;
    }
    
    .small-box .inner h3 {
        font-size: 20px;
    }
    
    .small-box .inner p {
        font-size: 12px;
    }
    
    .box-title {
        font-size: 16px;
    }
}
</style>
