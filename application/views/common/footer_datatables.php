<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery-ui-1.10.3.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/bootstrap.min.js"></script>
<!--DATA TABLES--->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/datatables/dataTables.bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/AdminLTE/app.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/excanvas.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.resize.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.pie.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.categories.min.js"></script>
<script>
    
    

    $(document).ready(function(){
        
        $("#s1").change(function(e){
            var str = $(this).val();
            $(".filter-year :nth-child(2)").each(function(){
                if($(this).html().trim() != str)
                    $(this).parent().hide();
                else
                    $(this).parent().show();
            });
        });
        
//         $('#users-table').dataTable({
//             "aoColumnDefs": [
//                          { "sTitle": "Student Number", "aTargets": [0]},
//                          { "sTitle": "Name", "aTargets": [1]},
//                          { "sTitle": "Course", "aTargets": [2]},
//                          { "sTitle": "Section", "aTargets": [3]},
//                          { "bSearchable":false,"bSortable" :false,"aTargets":[4]}
//                          
//                      ]
//         
//         });
        
        var editing = false;
        $(".edit-section").click(function(e){
            var studentId = $(this).attr('rel');
            var current = $(this).html();
            if(!editing)
            {
                editing=true;
               var str = "<form role='form'><div class='form-group'><input type='text' id='section' value='"+current+"'></div></form>";
                 $(this).html(str);
                $("#section").focus();
                var td = $(this);
                
                $("#section").blur(function(e){
                    data = {'intID':studentId,'strSection':$(this).val()};
                    $.ajax({
                        'url':'<?php echo base_url(); ?>unity/update_section',
                        'method':'post',
                        'data':data,
                        'dataType':'json',
                        'success':function(ret){
                           
                        }
                    });
                     td.html($(this).val());
                    editing = false;
                });
                
            
            }
            
        });
        
        $(".prelimInput").blur(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');
            var points = $(this).val();
            var data = {'intCSID':csid,'floatPrelimGrade':points};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_grade',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                    $("#average-"+csid).html(''+ret.average);
                     $("#eq-"+csid).html(''+ret.eq);
                }
            });
        
        });
        
        $(".midtermInput").blur(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');
            var points = $(this).val();
            var data = {'intCSID':csid,'floatMidtermGrade':points};
            
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_grade',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                    $("#average-"+csid).html(''+ret.average);
                     $("#eq-"+csid).html(''+ret.eq);
                }
            });
        
        });
        $(".finalsInput").blur(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var csid = $(this).attr('rel');
            var points = $(this).val();
            var parent = $(this).parent();
            var data = {'intCSID':csid,'floatFinalGrade':points};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_grade',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $(".loading-img").hide();
                    $(".overlay").hide();
                    $("#average-"+csid).html(''+ret.average);
                     $("#eq-"+csid).html(''+ret.eq);
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
                        parent.hide();
                        $(".loading-img").hide();
                        $(".overlay").hide();
                }
            });
            }
        });
        
        $(".trash-student-record").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var csid = $(this).attr('rel');
                var parent = $(this).parent().parent().parent().parent().parent();
                var data = {'id':csid};
                $.ajax({
                    'url':'<?php echo base_url(); ?>unity/delete_student',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        if(ret.message == "success"){
                        parent.hide();
                        $(".loading-img").hide();
                        $(".overlay").hide();
                        }
                        else
                        {
                            $(".alert").show();
                            setTimeout(function() {
                                $(".alert").hide('fade', {}, 500)
                            }, 3000);
                        }
                }
            });
            }
        });
        
        $(".trash-subject").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var id = $(this).attr('rel');
                var parent = $(this).parent().parent().parent().parent().parent();
                var data = {'id':id};
                $.ajax({
                    'url':'<?php echo base_url(); ?>unity/delete_subject',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        if(ret.message == "failed"){
                            $(".alert").show();
                            setTimeout(function() {
                                $(".alert").hide('fade', {}, 500)
                            }, 3000);
                        }
                        else
                            parent.hide();
                        
                        $(".loading-img").hide();
                        $(".overlay").hide();
                }
            });
            }
        });
        
        $(".trash-classlist").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var id = $(this).attr('rel');
                var parent = $(this).parent().parent().parent().parent().parent();
                //alert(parent.html());
                var data = {'id':id};
                
                $.ajax({
                    'url':'<?php echo base_url(); ?>unity/delete_classlist',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        parent.hide();
                        $(".loading-img").hide();
                        $(".overlay").hide();
                }
            });
            }
        });
        $(".trash-classroom-record").click(function(e){
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
                $(".loading-img").show();
                $(".overlay").show();
                var id = $(this).attr('rel');
                var parent = $(this).parent().parent().parent().parent().parent();
                //alert(parent.html());
                var data = {'id':id};
                
                $.ajax({
                    'url':'<?php echo base_url(); ?>unity/delete_classroom',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        parent.hide();
                        $(".loading-img").hide();
                        $(".overlay").hide();
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