<!--Javascript-->
<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/jquery-ui-1.10.3.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/bootstrap.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/datepicker/bootstrap-datepicker.js"></script>
<script>
    var availableDates = ["13-7-2020","14-7-2020","15-7-2020","16-7-2020","17-7-2020"];
	
	$(document).ready(function(){
	    
        populateCourses();
        
        <?php if(isset($info['strAppPicture']) && $info['strAppPicture'] == ""): ?>
            $("#confirmSubmission").hide();
        <?php endif; ?>

        /*
        $('.datepicker').datepicker({
            pickTime: false
         });
        */
        
        
        $('.datepickerExam').datepicker({
            pickTime: false,
            beforeShowDay:function(date){
                dmy = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear();
                if(availableDates.indexOf(dmy) != -1){
                    return true;
                }
                else{
                    return false;
                }
            }, 
            
         }).on('changeDate',function(e){
            $("#dteScheduleExam").val($(this).val());
            $("label[for='dteScheduleExam']").find('.text-error').remove();
            
        });
        $("#submitForm").click(function(e){
            $(this).addClass('disabled');
            var data = {};
            var btn = $(this);
            btn.val("Submitting...");
            $(".app").each(function(e){
               data[$(this).attr('name')] = $(this).val();
            });
            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/validate_form',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message == "success")
                      $("#appForm").submit();
                    else{
                        btn.removeClass('disabled');
                        btn.val("Submit Application");
                        put_error(ret.errors);
                    }


                }
            });
        });
        
        $("#backAndEdit").click(function(e){            
            $("#appFormBack").submit();
        });
        
        $("#confirmSubmission").click(function(e){
            conf = confirm("Are you sure you want to submit?");
            if(conf)
            {
                $("#appForm").submit();
            }
        })

        $("input.app").blur(function(e)
        {

            var data = {};
            var field = $(this).attr('name');
            data[$(this).attr('name')] = $(this).val();

            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/validate_field',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message != "success")
                        put_error_single(field,ret.errors);
                    else
                        remove_error(field);
                }
            });
        });

        $("textarea.app").blur(function(e)
        {

            var data = {};
            var field = $(this).attr('name');
            data[$(this).attr('name')] = $(this).val();

            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/validate_field',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message != "success")
                        put_error_single(field,ret.errors);
                    else
                        remove_error(field);
                }
            });
        });

        $("select.app").change(function(e)
        {

            var data = {};
            var field = $(this).attr('name');
            data[$(this).attr('name')] = $(this).val();

            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/validate_field',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message != "success")
                        put_error_single(field,ret.errors);
                    else
                        remove_error(field);
                }
            });
        });

        $("select[name='strAppProvince']").change(function(e){
            var code = $(this).val();
            var data = {'code':code};
            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/get_municipality',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                   var ht = "";
                   $("#strAppCity").html(ret.citymun);


                }
            });
        });
        
        

        $("select[name='strAppCity']").change(function(e){
            var code = $(this).val();
            var data = {'code':code};
            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/get_brgy',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                   var ht = "";
                   $("#strAppBrgy").html(ret.citymun);


                }
            });
        });
    
    });
    
    function populateCourses()
    {
        $.ajax({
            'url':'<?php echo base_url(); ?>admissions/get_courses',
            'method':'get',            
            'dataType':'json',
            'success':function(ret){               
                $("#enumCourse1").html(ret.courses);
                $("#enumCourse2").html(ret.courses);
                $("#enumCourse3").html(ret.courses);
            }
        });
    }
    
    function put_error_single(field,errors)
    {
        $("label[for='"+field+"']").find('.text-error').remove();
        $("input[name='"+field+"']").addClass('has-error');
        $("textarea[name='"+field+"']").addClass('has-error');
        $("label[for='"+field+"']").append("<div class='text-error'>"+errors[field]+"</div>");
    }
    
    function remove_error(field)
    {
        $("label[for='"+field+"']").find('.text-error').remove();
        $("input[name='"+field+"']").removeClass('has-error');
        $("select[name='"+field+"']").removeClass('has-error');
        $("textarea[name='"+field+"']").removeClass('has-error');
        
    }
    
    function put_error(errors)
    {
        $(".app").each(function(e){
           if(errors[$(this).attr('name')]){
               $("label[for='"+$(this).attr('name')+"']").find('.text-error').remove();
               $(this).addClass('has-error');
               $("label[for='"+$(this).attr('name')+"']").append("<div class='text-error'>"+errors[$(this).attr('name')]+"</div>");
           }
        });
    }
	
</script>

		
	
	