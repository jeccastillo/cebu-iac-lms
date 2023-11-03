<script type="text/javascript">
    $(document).ready(function(){
        
        //FORM VALIDATION FOR SUBJECT
        $("input[name='strCode']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strCode']").html("<i class='fa fa-times-circle-o'></i> Subject Code is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strCode']").html("Subject Code*");
            }
        
        });
        $("input[name='strUnits']").blur(function(){
            if($(this).val() == "" )
            {
                $(this).parent().addClass('has-error');
                $("label[for='strUnits']").html("<i class='fa fa-times-circle-o'></i> Units field is required");
            }
            else
            {
                 $(this).parent().removeClass('has-error');
                 $("label[for='strUnits']").html("Number of Units*");
            }
        
        });
        $("#validate-subject").submit(function(e){
            
            
            if($("input[name='strCode']").val() == ""){
                $("input[name='strCode']").parent().addClass('has-error');
                $("label[for='strCode']").html("<i class='fa fa-times-circle-o'></i> Subject Code is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strCode']").parent().removeClass('has-error');
                $("label[for='strCode']").html("Subject Code*");
            }
            if($("input[name='strUnits']").val() == ""){
                $("input[name='strUnits']").parent().addClass('has-error');
                 $("label[for='strUnits']").html("<i class='fa fa-times-circle-o'></i> Units field is required");
                e.preventDefault();
            }
            else
            {
                $("input[name='strUnits']").parent().removeClass('has-error');
                $("label[for='strUnits']").html("Units*");
                
            }
            
            return;
        });
        
        
        $("#save-rooms").click(function(e){
           e.preventDefault();
            var rooms = Array();
            $('#room-selected option').each(function(i, selected){ 
                rooms[i] = $(selected).val();
            });
            
            
            var sid = $("#intID").val();
            
            data = {'intSubjectID':sid,'rooms':rooms};
           
                $.ajax({
                    'url':'<?php echo base_url(); ?>subject/submit_room_subject/',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                       alert("saved"); 
                    }
                });
           
        });
        
        //Classlists
        
        $("#load-rooms").click(function(e){ 
                e.preventDefault();
                $('#room-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#room-selected").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#room-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-rooms").click(function(e){ 
                e.preventDefault();
                $('#room-selected :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#room-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#room-selected option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#save-prereq").click(function(e){
           e.preventDefault();
            var subj = Array();
            $('#prereq-selected option').each(function(i, selected){ 
                subj[i] = $(selected).val();
            });
            
            
            var sid = $("#intID").val();
            
            data = {'intSubjectID':sid,'subj':subj};
           
                $.ajax({
                    'url':'<?php echo base_url(); ?>subject/submit_prereq_subject/',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                       alert("saved"); 
                    }
                });
           
        });
        
        $("#load-prereq").click(function(e){ 
                e.preventDefault();
                $('#prereq-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#prereq-selected").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#prereq-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-prereq").click(function(e){ 
                e.preventDefault();
                $('#prereq-selected :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#prereq-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#prereq-selected option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });

        $("#save-eq").click(function(e){
           e.preventDefault();
            var subj = Array();
            $('#eq-selected option').each(function(i, selected){ 
                subj[i] = $(selected).val();
            });
            
            
            var sid = $("#intID").val();
            
            data = {'intSubjectID':sid,'subj':subj};
           
                $.ajax({
                    'url':'<?php echo base_url(); ?>subject/submit_eq_subject/',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                       alert("saved"); 
                    }
                });
           
        });
        
        $("#load-eq").click(function(e){ 
                e.preventDefault();
                $('#eq-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#eq-selected").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#eq-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-eq").click(function(e){ 
                e.preventDefault();
                $('#eq-selected :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#eq-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#eq-selected option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#save-days").click(function(e){
           e.preventDefault();
            var subj = Array();
            $('#days-selected option').each(function(i, selected){ 
                subj[i] = $(selected).val();
            });
            
            
            var sid = $("#intID").val();
            
            data = {'intSubjectID':sid,'subj':subj};
           
                $.ajax({
                    'url':'<?php echo base_url(); ?>subject/submit_days_subject/',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                       alert("saved"); 
                    }
                });
           
        });
        
        $("#load-days").click(function(e){ 
                e.preventDefault();
                $('#days-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#days-selected").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#days-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-days").click(function(e){ 
                e.preventDefault();
                $('#days-selected :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#days-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#days-selected option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
    });
</script>