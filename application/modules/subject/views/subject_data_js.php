<script type="text/javascript" src="<?php echo $js_dir; ?>Chart.bundle.min.js" ></script>
<script>

    var ctx3 = document.getElementById("gradesChart");
    var data2 = {
    labels: ['1','1.25','1.5','1.75','2.0','2.25','2.5','2.75','3.0','inc','5.0'],
    datasets: [
        <?php 
        if(!empty($grades_charts)):
        foreach($grades_charts as $grades_chart): 
            $i=rand(0,230);
            $j=rand(0,230);
            $k=rand(0,230);
            $bgcolor = 'rgba('.$j.",".$i.",".$k.',0.8)';
            $bordercolor = 'rgba('.$j.",".$i.",".$k.',1)';
        ?>
        {
            label: "<?php echo $grades_chart['label']; ?>",
            fill: true,
            lineTension: 0.1,
            backgroundColor: "<?php echo $bgcolor; ?>",
            borderColor: "<?php echo $bordercolor; ?>",
            borderCapStyle: 'butt',
            borderDash: [],
            borderDashOffset: 0.0,
            borderJoinStyle: 'miter',
            pointBorderColor: "<?php echo $bordercolor; ?>",
            pointBackgroundColor: "#fff",
            pointBorderWidth: 1,
            pointHoverRadius: 5,
            pointHoverBackgroundColor: "<?php echo $bgcolor; ?>",
            pointHoverBorderColor: "<?php echo $bordercolor; ?>",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?php echo isset($grades_chart['1'])?$grades_chart['1']:0; ?>,<?php echo isset($grades_chart['125'])?$grades_chart['125']:0; ?>,<?php echo isset($grades_chart['15'])?$grades_chart['15']:0; ?>,<?php echo isset($grades_chart['175'])?$grades_chart['175']:0; ?>,<?php echo isset($grades_chart['2'])?$grades_chart['2']:0; ?>,<?php echo isset($grades_chart['225'])?$grades_chart['225']:0; ?>,<?php echo isset($grades_chart['25'])?$grades_chart['25']:0; ?>,<?php echo isset($grades_chart['275'])?$grades_chart['275']:0; ?>,<?php echo isset($grades_chart['3'])?$grades_chart['3']:0; ?>,<?php echo isset($grades_chart['35'])?$grades_chart['35']:0; ?>,<?php echo isset($grades_chart['5'])?$grades_chart['5']:0; ?>],
            spanGaps: false,
        },
        <?php endforeach; ?>
        <?php endif; ?>
    ]
};
    
    var options = {
            scales: {
            yAxes: [{
                stacked: true
            }]
        },
        hover: {
            // Overrides the global setting
            mode: 'index'
        },
        tooltips:
        {
            mode: 'index',
            bodySpacing: 5
        }
    };
    
    var myLineChart = Chart.Line(ctx3, {
        data: data2,
        options: options
    });
    
</script>