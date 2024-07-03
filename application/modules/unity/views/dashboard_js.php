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


</script>