<script type="text/javascript">
    $(document).ready(function(){
            
        <?php foreach($advised_subjects as $sn): ?>
            $("#subject-selector option[value='<?php echo $sn['intSubjectID']; ?>']").remove();
        <?php endforeach; ?>
        
        //$("#subject-to-add").find('option').remove(); 
          $("#autoload-advised").click(function(e){  
              e.preventDefault();
            var sem = $("#active-sem").val();
            var year = $("#academic-standing").val();
            var sid = $("#student-id").val();
            var cid = $("#curriculum-id").val();
            var data = {'year':year,'sem':sem,'sid':sid,'cid':cid};
            var stat = $("#academic-standing-stat").val();
            
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/load_advised_subjects/'+stat,
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    $("#advised-subjects").html('');
                    $.each(ret, function (i, item) {
                        $("#advised-subjects").append($('<option>', { 
                                    value: item.intID,
                                    text : item.strCode
                                }));
                        $("#subject-selector option[value='"+item.intID+"']").remove();
                    });
                    
                }
            });
              
        });
        
        $("#save-advised").click(function(e){
            e.preventDefault();
            var subjects = Array();
            $('#advised-subjects option').each(function(i, selected){ 
                subjects[i] = $(selected).val();
            });
            
            var sid = $("#student-id").val();
            var syid = $("#active-sem-id").val();
            
            data = {'subjects':subjects,'studentID':sid,'strAcademicYear':syid};
            
            
            $.ajax({
                'url':'<?php echo base_url(); ?>department/submit_advised/',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                   alert("saved"); 
                   document.location = "<?php echo base_url(); ?>registrar/register_old_student/"+sid;
                }
            });
            
        });
        
        $("#load-advised").click(function(e){ 
             e.preventDefault();
                $('#subject-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#advised-subjects").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#subject-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-advised").click(function(e){ 
             e.preventDefault();
                $('#advised-subjects :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#subject-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#advised-subjects option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
    });
</script>