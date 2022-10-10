<script type="text/javascript">
    $(document).ready(function(){
        var hovm = "none";
        
        $(document).keypress("c",function(e) {
          if(e.ctrlKey && e.keyCode == "3")
            if(hovm == "sel"){
                hovm = "del";
                $(".time-table").css( 'cursor', 'auto' );
                $("#sel-icon").removeClass('fa-plus');
                $("#sel-icon").addClass('fa-minus');
            }
            else if(hovm == "none"){
                hovm = "sel";
                $(".time-table").css( 'cursor', 'cell' );
                $("#sel-icon").removeClass('fa-gear');
                $("#sel-icon").addClass('fa-plus');
            }
            else
            {
                hovm = "none";
                $(".time-table").css( 'cursor', 'auto' );
                $("#sel-icon").removeClass('fa-minus');
                $("#sel-icon").addClass('fa-gear');
            }
        });
        
        $("#sel").click(function(e)
        {
            if(hovm == "sel"){
                hovm = "del";
                $(".time-table-m").css( 'cursor', 'auto' );
                $("#sel-icon").removeClass('fa-plus');
                $("#sel-icon").addClass('fa-minus');
            }
            else if(hovm == "none"){
                hovm = "sel";
                $(".time-table-m").css( 'cursor', 'cell' );
                $("#sel-icon").removeClass('fa-gear');
                $("#sel-icon").addClass('fa-plus');
            }
            else
            {
                hovm = "none";
                $(".time-table-m").css( 'cursor', 'auto' );
                $("#sel-icon").removeClass('fa-minus');
                $("#sel-icon").addClass('fa-gear');
            }
        });
                      
        $(".time-table").mouseover(
            function(e)
            {
               
                if(hovm=="sel"){
                    $(this).attr('data-selected',1);
                    $(this).css('background','#d2d2d2');
                }
                else if(hovm == "del"){
                    $(this).attr('data-selected',0);
                    $(this).css('background','#fff');
                }
               
                
            }                   
        );
        
        
        
        
        $("#save-classlist").click(function(e){
           e.preventDefault();
            var classlists = Array();
            $('#loaded-classlist option').each(function(i, selected){ 
                classlists[i] = $(selected).val();
            });
            
            var days = Array();
            $('#day-selected option').each(function(i, selected){ 
                days[i] = $(selected).val();
            });
            
            var fid = $("#faculty-id").val();
            var ay = $("#active-sem").val();
            
            data = {'classlists':classlists,'facultyID':fid,'ay':ay,'days':days};
            
            
            $.ajax({
                'url':'<?php echo base_url(); ?>department/submit_loaded_classlist/',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                   alert("saved"); 
                }
            });
            
        });
        
        //Classlists
        
        $("#load-classlist").click(function(e){ 
                e.preventDefault();
                $('#classlist-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#loaded-classlist").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#classlist-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-classlist").click(function(e){ 
                e.preventDefault();
                $('#loaded-classlist :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#classlist-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#loaded-classlist option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        //DAYS
         $("#load-days").click(function(e){ 
                e.preventDefault();
                $('#day-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#day-selected").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#day-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-days").click(function(e){ 
                e.preventDefault();
                $('#day-selected :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#day-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#day-selected option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
    });
</script>