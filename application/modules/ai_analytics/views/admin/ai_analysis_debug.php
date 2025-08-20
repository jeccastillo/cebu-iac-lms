<aside class="right-side" id="ai-analytics-container">    
    <section class="content-header">
        <h1>
            AI Admissions Analytics - Debug Mode
            <small>
                <a class="btn btn-app" href="<?php echo base_url(); ?>admissionsV1/admissions_report" >
                    <i class="ion ion-arrow-left-a"></i>
                    Back to Report
                </a>
            </small>
        </h1>     
    </section>
    <hr />
    
    <div class="content"> 
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">System Debug Information</h3>
                    </div>
                    <div class="box-body">
                        <h4>Basic Information</h4>
                        <ul>
                            <li><strong>Base URL:</strong> <?php echo base_url(); ?></li>
                            <li><strong>Current Term:</strong> <?php echo isset($current_sem) ? $current_sem : 'Not set'; ?></li>
                            <li><strong>Available Terms:</strong> <?php echo isset($sy) ? count($sy) . ' terms' : 'No terms'; ?></li>
                            <li><strong>PHP Version:</strong> <?php echo phpversion(); ?></li>
                            <li><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></li>
                        </ul>
                        
                        <h4>JavaScript Test</h4>
                        <button onclick="testBasic()" class="btn btn-primary">Test Basic JS</button>
                        <button onclick="testAjax()" class="btn btn-info">Test AJAX</button>
                        <div id="test-output" style="margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd;"></div>
                        
                        <h4>Quick Analysis Test</h4>
                        <button onclick="quickAnalysis()" class="btn btn-success">Generate Quick Analysis</button>
                        <div id="analysis-output" style="margin-top: 10px; padding: 10px; background: #f0f8ff; border: 1px solid #ddd;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script>
console.log('AI Analytics Debug - Script loaded');

function testBasic() {
    const output = document.getElementById('test-output');
    output.innerHTML = 'Basic JavaScript is working! Time: ' + new Date().toLocaleTimeString();
    console.log('Basic test completed');
}

function testAjax() {
    const output = document.getElementById('test-output');
    output.innerHTML = 'Testing AJAX...';
    
    const baseUrl = '<?php echo base_url(); ?>';
    
    // Use jQuery AJAX since it's more reliable
    $.ajax({
        url: baseUrl + 'ai_analytics/get_realtime_metrics',
        method: 'POST',
        data: {
            term: <?php echo isset($current_sem) ? $current_sem : '1'; ?>
        },
        success: function(response) {
            console.log('AJAX Success:', response);
            output.innerHTML = '<span style="color: green;">✓ AJAX working! Response received.</span>';
        },
        error: function(xhr, status, error) {
            console.log('AJAX Error:', xhr, status, error);
            output.innerHTML = '<span style="color: red;">✗ AJAX Error: ' + error + '</span>';
        }
    });
}

function quickAnalysis() {
    const output = document.getElementById('analysis-output');
    output.innerHTML = 'Generating analysis...';
    
    const baseUrl = '<?php echo base_url(); ?>';
    
    $.ajax({
        url: baseUrl + 'ai_analytics/generate_analysis',
        method: 'POST',
        data: {
            analysis_type: 'comprehensive',
            term: <?php echo isset($current_sem) ? $current_sem : '1'; ?>
        },
        success: function(response) {
            console.log('Analysis Success:', response);
            if (response.success) {
                output.innerHTML = '<span style="color: green;">✓ Analysis generated successfully!</span>';
            } else {
                output.innerHTML = '<span style="color: orange;">Analysis completed with message: ' + (response.error || 'Unknown') + '</span>';
            }
        },
        error: function(xhr, status, error) {
            console.log('Analysis Error:', xhr, status, error);
            output.innerHTML = '<span style="color: red;">✗ Analysis Error: ' + error + '</span>';
        }
    });
}

// Auto-run basic test on load
$(document).ready(function() {
    console.log('Document ready - AI Analytics Debug');
    testBasic();
});
</script>
</aside>
