
</div>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery-ui-1.10.3.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/AdminLTE/app.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/excanvas.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.pie.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.categories.min.js"></script>
<script>
    
    

    $(document).ready(function(){
        
          /*
                 * DONUT CHART
                 * -----------
                 */

                var donutData = [
                    {label: "passing", data: <?php echo $passing; ?>, color: "#3c8dbc"},
                    {label: "failing", data: <?php echo $failing; ?>, color: "#cc0000"},
                    {label: "inc.", data: <?php echo $incomplete; ?>, color: "#ff8c00"},  // newly added ^_^ 10-20-15 6:59pm
                    {label: "o.d.", data: <?php echo $od; ?>, color: "#808080"},
                ];
                $.plot("#donut-chart", donutData, {
                    series: {
                        pie: {
                            show: true,
                            radius: 1,
                            innerRadius: 0.5,
                            label: {
                                show: true,
                                radius: 2 / 3,
                                formatter: labelFormatter,
                                threshold: 0.1
                            }

                        }
                    },
                    legend: {
                        show: false
                    }
                });
                /*
                 * END DONUT CHART
                 */
        
        $("#s1").change(function(e){
            var str = $(this).val();
            $(".filter-year :nth-child(2)").each(function(){
                if($(this).html().trim() != str)
                    $(this).parent().hide();
                else
                    $(this).parent().show();
            });
        });
        //<!--  newly added ^_^ 4-22-2016            -->
         $(".studNumInput").blur(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var studid = $(this).attr('rel');
            var stud_num = $(this).val();
            var parent = $(this).parent();
            var data = {'intID':studid,'strStudentNumber':stud_num};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_studNum',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                }
            });
        
        });
        
        $("#finalize-term").click(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Submit Grades?',
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    $(".loading-img").show();
                    $(".overlay").show();
                    var csid = $(this).attr('data-csid');
                    var intFinalized = $(this).attr('rel');
                    var parent = $(this).parent();
                    var data = {'intID':csid,'intFinalized':intFinalized};
                    $.ajax({
                        'url':'<?php echo base_url(); ?>unity/finalize_term',
                        'method':'post',
                        'data':data,
                        'dataType':'json',
                        'success':function(ret){
                            document.location ="<?php echo current_url(); ?>";
                        }
                    });
                }
            });
            
                
            
        
        });
        
        $(".finalsInput").change(function(){
             $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');                    
            var values = $(this).val().split("-");                  
            var remarks = values[1];
            var points = values[0];
            var parent = $(this).parent();
            var data = {'intCSID':csid,'floatFinalGrade': points,'strRemarks':remarks};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_grade/3',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                    $("#eq-"+csid).html(''+ret.eq);
                    $("#eq2-"+csid).html(''+ret.eq_raw);
					 $("#rem-"+csid).html(''+remarks);
                }
            });
        
        });
        
        
        
        $(".prelimInput").blur(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');
            
            if(parseInt($(this).val()) < 50 || $(this).val()==""){
                var points = 50;
                $(this).val('50');
            }
            else
                var points = $(this).val();
            
            var parent = $(this).parent();
            var data = {'intCSID':csid,'floatPrelimGrade':points};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_grade/1',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                    $("#eq-"+csid).html(''+ret.eq);
                    $("#eq2-"+csid).html(''+ret.eq_raw);
					$("#rem-"+csid).html(''+ret.remarks);
                }
            });
        
        });
        $(".midtermInput").blur(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');  
            var values = $(this).val().split("-");                  
            var remarks = values[1];
            var points = values[0];
            var parent = $(this).parent();
            var data = {'intCSID':csid,'floatMidtermGrade':points, 'strRemarks':remarks};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_grade/2',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                    $("#eq-"+csid).html(''+ret.eq);
                    $("#eq2-"+csid).html(''+ret.eq_raw);
					 $("#rem-"+csid).html(''+remarks);
                }
            });
        
        });
        $(".studentStatus").change(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');
            var finInput = $("#inputFinalsID-").attr('rel');
            var status = $(this).val();
            var parent = $(this).parent();
            var data = {'intCSID':csid,'enumStatus':status};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_student_status',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    var grade = ''+ret.eq;
                    var raw_grade = ''+ret.eq_raw;
                    if(ret.remarks == "OD")
                        grade = "---";

                    $(".loading-img").hide();
                   
                    $("#eq-"+csid).html(grade);
                    $(".overlay").hide();
                    $("#eq2-"+csid).html(raw_grade);
                    $("#rem-"+csid).html(''+ret.remarks);
                    if (status=="drp") {
                        $("#gradeStat-"+csid).prop('disabled', 'disabled');
                        $("#inputPrelimID-"+csid).prop('disabled', 'disabled');
                        $("#inputMidtermID-"+csid).prop('disabled', 'disabled');
                        $("#inputFinalsID-"+csid).prop('disabled', 'disabled');
                    }
                    //document.location = "<?php echo current_url(); ?>";   
                }
            });
        
        });   
        $(".remarks").blur(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');
            var remarks = $(this).val();
            var parent = $(this).parent();
            var data = {'intCSID':csid,'strRemarks':remarks};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_remarks',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                }
            });
        
        }); 
        
        $(".trash-student").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var csid = $(this).attr('rel');
                var parent = $(this).parent().parent();
                var data = {'intCSID':csid};
                $.ajax({
                    'url':'<?php echo base_url(); ?>unity/delete_student_cs',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        if(ret.message == "failed"){
                            alert("you are not allowed");
                        }
                       // parent.hide();
                         document.location = "<?php echo current_url(); ?>";
                        //$(".loading-img").hide();
                        //$(".overlay").hide();
                }
            });
            }
        });
        
       
        
        
    });
    
    /*
             * Custom Label formatter
             * ----------------------
             */
            function labelFormatter(label, series) {
                return "<div style='font-size:13px; text-align:center; padding:2px; color: #fff; font-weight: 600;'>"
                        + label
                        + "<br/>"
                        + Math.round(series.percent) + "%</div>";
            }
</script>
</body>
</html>