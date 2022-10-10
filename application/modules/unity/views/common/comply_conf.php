<script type="text/javascript">
    $(document).ready(function(){

        $("#submit-completion").attr('disabled','disabled');
        $('#floatNewFinalTermGrade').focus();
        
        $("#submit-completion").on('click',function(){
            if($('#floatNewFinalTermGrade').val() < $('#floatFinalTermGrade').val() || $('#floatNewFinalTermGrade').val() > 100){
                alert("Please enter a grade between 50 and 100");
                
                $('#floatNewFinalTermGrade').focus();
            }
            else{
                    if ($('#floatComputedSemGrade').val() <= 74.49) {
                        alert("Please recheck your inputted grade");
                        $('#floatNewFinalTermGrade').focus();
                        $('#floatNewFinalTermGrade').select();
                        $("#submit-completion").attr('disabled','disabled');
                    }
                    else {

                        if($('#floatNewFinalTermGrade').val() > $('#floatFinalTermGrade').val() || $('#floatNewFinalTermGrade').val() < 100)
                        var txt;
                        var r = confirm("Are you sure with "+ $('#floatNewFinalTermGrade').val()  + " as the new final term grade?");
                        if (r == true) {
                            var data = {
                                        'intClasslistStudentID': $('#intClasslistStudentID').val(),
                                        'floatNewFinalTermGrade': $('#floatNewFinalTermGrade').val()
                                    };

                            $.ajax({
                                'url':'<?php echo base_url(); ?>unity/completionRequest',
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
                                    else{
                                        alert("Final Term Grade submitted. Please wait for the approval.");
                                        document.location = "<?php echo base_url();?>unity/classlist_viewer/<?php echo $cs['intID']; ?>#tab_5";
                                        var url ="<?php echo base_url()."pdf/student_completion_form_print/". $cs['intCSID'];?>";
                                        window.open(url,'_self');
                                    }
                                    $(".loading-img").hide();
                                    $(".overlay").hide();
                            }
                            });
                        } else {
                            alert("Please re-check your inputted grade.");
                            $("#floatNewFinalTermGrade").removeAttr('disabled');
                            $('#strNumericalRating').empty();
                            $('#strComputedSemGrade').empty();
                            $("#submit-completion").attr('disabled','disabled');
                            $('#floatNewFinalTermGrade').focus();
                            $('#floatNewFinalTermGrade').select();
                        }
                    }
                }
        });

        $("#compute-completion").on('click',function(){
            if($('#floatNewFinalTermGrade').val() <= $('#floatFinalTermGrade').val()){
                alert("Please enter a grade between " + $('#floatFinalTermGrade').val() + " and 100");
                $('#floatNewFinalTermGrade').focus();
                $('#floatNewFinalTermGrade').val("");
            }
            else if ($('#floatNewFinalTermGrade').val() > 100)
            {
                alert("Please enter a grade between " + $('#floatFinalTermGrade').val() + " and 100");
                $('#floatNewFinalTermGrade').focus();
                $('#floatNewFinalTermGrade').val("");
            }
            else {
                var floatComputedSemGrade = 0.00;
                var pGrade = $('#floatPrelimGrade').val();
                var mGrade = $('#floatMidtermGrade').val();
                var fGrade = $('#floatNewFinalTermGrade').val();
                

                floatComputedSemGrade = (pGrade * .30) + (mGrade * .30 ) + ( fGrade * .40);
                floatComputedSemGrade = parseFloat(floatComputedSemGrade).toFixed(2);
                $('#floatComputedSemGrade').val(floatComputedSemGrade);
                if (floatComputedSemGrade <= 74.49) {
                    $('#strComputedSemGrade').css({ 'color': 'red' });
                    $('#floatNewFinalTermGrade').focus();
                    $('#floatNewFinalTermGrade').select();
                }
                else {
                    $('#strComputedSemGrade').css({ 'color': 'green' });
                    $("#floatNewFinalTermGrade").attr('disabled','disabled');
                }
                $('#strComputedSemGrade').empty().append(floatComputedSemGrade);

                if (floatComputedSemGrade >= 97.5 && floatComputedSemGrade <= 100) {
                    $('#strNumericalRating').empty().append( "1.00");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "1.00");   
                }
                else if (floatComputedSemGrade >= 94.5 && floatComputedSemGrade <= 97.49) {
                    $('#strNumericalRating').empty().append( "1.25");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "1.25");   
                }
                else if (floatComputedSemGrade >= 91.5 && floatComputedSemGrade <= 94.49) {
                    $('#strNumericalRating').empty().append( "1.50");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "1.50");   
                }
                else if (floatComputedSemGrade >= 88.5 && floatComputedSemGrade <= 91.49) {
                    $('#strNumericalRating').empty().append( "1.75");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "1.75");   
                }
                else if (floatComputedSemGrade >= 85.5 && floatComputedSemGrade <= 88.49) {
                    $('#strNumericalRating').empty().append( "2.00");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "2.00");   
                }
                else if (floatComputedSemGrade >= 82.5 && floatComputedSemGrade <= 85.49) {
                    $('#strNumericalRating').empty().append( "2.25");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "2.25");   
                }
                else if (floatComputedSemGrade >= 79.5 && floatComputedSemGrade <= 82.49) {
                    $('#strNumericalRating').empty().append( "2.50");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "2.50");   
                }
                else if (floatComputedSemGrade >= 76.5 && floatComputedSemGrade <= 79.49) {
                    $('#strNumericalRating').empty().append( "2.75");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "2.75");   
                }
                else if (floatComputedSemGrade >= 74.5 && floatComputedSemGrade <= 76.49) {
                    $('#strNumericalRating').empty().append( "3.00");
                    $('#strNumericalRating').css({ 'color': 'green' });
                    $('#floatNumericalRating').val( "3.00");   
                } 
                else if (floatComputedSemGrade > 0 && floatComputedSemGrade <= 74.49) {
                    $('#strNumericalRating').empty().append( "5.00");
                    $('#strNumericalRating').css({ 'color': 'red' });
                    $('#floatNumericalRating').val( "5.00");   
                }
                else if (floatComputedSemGrade <= 0) {
                    $('#strNumericalRating').empty().append( "0.00");
                    $('#floatNumericalRating').val( "0.00");   
                }
                if (floatComputedSemGrade > 74.49) {
                    $('#submit-completion').removeAttr('disabled');
                }
                else {
                    $("#submit-completion").attr('disabled','disabled');
                }
                //var txt;
                //var r = confirm("Are you sure with "+ $('#intFinalTermGrade').val()  + " as the inputted grade?");
            }
        });
    });
</script>