<script type="text/javascript" src="<?php echo $js_dir; ?>Chart.bundle.min.js" ></script>
<script>

labels = new Array();
data_students = new Array();
bg_colors = new Array();
<?php  $ctr = 0;
        foreach($student_course as $sc): 
        $i=rand(0,230);
        $j=rand(0,230);
        $k=rand(0,230);
        $colors[$ctr] = 'rgba('.$j.",".$i.",".$k.',0.4)';
    ?>
    labels.push('<?php echo $sc['strProgramCode'] ?>');
    data_students.push('<?php echo $sc['numStudents']; ?>');
    bg_colors.push('rgba(<?php echo $j.",".$i.",".$k ?>,0.4)');
<?php 
    $ctr++;
    endforeach; ?>
var ctx = document.getElementById("studentsChart");

var myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            data: data_students,
            label: "students",
            backgroundColor: bg_colors
        }]
    }
});

var ctx2 = document.getElementById("eStudentsChart");

    
var myChart = new Chart(ctx2, {
    type: 'bar',
    data: {
        labels: ['registered','enrolled(paid)','cleared'],
        datasets: [
            <?php 
                $ctr = 0;
                foreach($student_course as $sc): 
                $registration_data = getRegistrationDataCourse($active_sem['intID'],$sc['intProgramID']);       
            ?>
            {
            data: [<?php echo $registration_data['registered']; ?>,<?php echo $registration_data['enrolled']; ?>,<?php echo $registration_data['cleared']; ?>],
            label: "<?php echo $sc['strProgramCode']; ?>",
            backgroundColor: "<?php echo $colors[$ctr]; ?>",
            borderColor: "<?php echo $colors[$ctr]; ?>",
            },
            <?php 
            $ctr++;
            endforeach; ?>
            
        ]
    }
    
    
    
});
    var ctx3 = document.getElementById("gradesChart");
    var data2 = {
    labels: ['1','1.25','1.5','1.75','2.0','2.25','2.5','2.75','3.0','inc','5.0'],
    datasets: [
        <?php foreach($grades_charts as $grades_chart): 
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
            pointHoverBackgroundColor: "<?php echo $bordercolor; ?>",
            pointHoverBorderColor: "<?php echo $bordercolor; ?>",
            pointHoverBorderWidth: 2,
            pointRadius: 1,
            pointHitRadius: 10,
            data: [<?php echo isset($grades_chart['1'])?$grades_chart['1']:0; ?>,<?php echo isset($grades_chart['125'])?$grades_chart['125']:0; ?>,<?php echo isset($grades_chart['15'])?$grades_chart['15']:0; ?>,<?php echo isset($grades_chart['175'])?$grades_chart['175']:0; ?>,<?php echo isset($grades_chart['2'])?$grades_chart['2']:0; ?>,<?php echo isset($grades_chart['225'])?$grades_chart['225']:0; ?>,<?php echo isset($grades_chart['25'])?$grades_chart['25']:0; ?>,<?php echo isset($grades_chart['275'])?$grades_chart['275']:0; ?>,<?php echo isset($grades_chart['3'])?$grades_chart['3']:0; ?>,<?php echo isset($grades_chart['35'])?$grades_chart['35']:0; ?>,<?php echo isset($grades_chart['5'])?$grades_chart['5']:0; ?>],
            spanGaps: false,
        },
        <?php endforeach; ?>
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
    
    
    var ctx5 = document.getElementById("classlistsChart");

    var myClasslistChart = new Chart(ctx5, {
    type: 'bar',
    data: {
        labels: ['submitted classlists','unsubmitted classlists'],
        datasets: [{
            data: [<?php echo $submitted_classlists; ?>,<?php echo $un_submitted_classlists; ?>],
            label: "classlists",
            backgroundColor: ['rgba(60,185,60,1)','rgba(185,60,60,1)'],
        }]
        },
    options: {
        scales: {
            yAxes: [{
                ticks: {
                    beginAtZero: true
                }
            }]
        }
    }
    });
    
</script>