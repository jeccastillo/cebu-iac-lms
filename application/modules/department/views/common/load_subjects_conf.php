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
                    location.reload();
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
        //Sections
        $("#load-section").click(function(e){ 
                e.preventDefault();
                $('#section-selector :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#loaded-section").append($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#section-selector option[value='"+itemVal+"']").remove();
                    
                });
                
                
        });
        
        $("#unload-section").click(function(e){ 
                e.preventDefault();
                $('#loaded-section :selected').each(function(i, selected){ 
                    itemVal = $(selected).val(); 
                    itemText = $(selected).text();
                    $("#section-selector").prepend($('<option>', { 
                                    value: itemVal,
                                    text : itemText
                                }));
                        $("#loaded-section option[value='"+itemVal+"']").remove();
                    
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

        $(".Mon").each(function(){
            
            var st = $(this).val();
            var hourspan = $(this).attr('href'); 
            var text = $(this).attr('rel');
            var section = $(this).attr('data-section');
            $("#"+st+" :nth-child(2)").addClass("bg-teal");
            $("#"+st+" :nth-child(2)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(2)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
            nxt.next().children(":nth-child(2)").html("<div style='text-align:center;'>"+section+"</div>");
            for(i=1;i<hourspan;i++){
                nxt.next().children(":nth-child(2)").addClass("bg-teal");
                if(i==hourspan-1)
                nxt.next().children(":nth-child(2)").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                else
                    nxt.next().children(":nth-child(2)").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
                
                nxt = nxt.next();
            }
            $("#sched-table").val($("#sched-table-container").html());
            
        });
        
        $(".Tue").each(function(){
            
            var st = $(this).val();
            var hourspan = $(this).attr('href'); 
            var text = $(this).attr('rel');
            var section = $(this).attr('data-section');
            $("#"+st+" :nth-child(3)").addClass("bg-teal");
            $("#"+st+" :nth-child(3)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(3)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
            nxt.next().children(":nth-child(3)").html("<div style='text-align:center;'>"+section+"</div>");
            for(i=1;i<hourspan;i++){
                nxt.next().children(":nth-child(3)").addClass("bg-teal");
                if(i==hourspan-1)
                nxt.next().children(":nth-child(3)").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                else
                    nxt.next().children(":nth-child(3)").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
                
                nxt = nxt.next();
            }
            $("#sched-table").val($("#sched-table-container").html());
            
        });
        
        $(".Wed").each(function(){
            
            var st = $(this).val();
            var hourspan = $(this).attr('href'); 
            var text = $(this).attr('rel');
            var section = $(this).attr('data-section');
            $("#"+st+" :nth-child(4)").addClass("bg-teal");
            $("#"+st+" :nth-child(4)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(4)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
            nxt.next().children(":nth-child(4)").html("<div style='text-align:center;'>"+section+"</div>");
            for(i=1;i<hourspan;i++){
                nxt.next().children(":nth-child(4)").addClass("bg-teal");
                if(i==hourspan-1)
                nxt.next().children(":nth-child(4)").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                else
                    nxt.next().children(":nth-child(4)").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
                
                nxt = nxt.next();
            }
            $("#sched-table").val($("#sched-table-container").html());
            
        });
        
        $(".Thu").each(function(){
            
            var st = $(this).val();
            var hourspan = $(this).attr('href'); 
            var text = $(this).attr('rel');
            var section = $(this).attr('data-section');
            $("#"+st+" :nth-child(5)").addClass("bg-teal");
            $("#"+st+" :nth-child(5)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(5)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
            nxt.next().children(":nth-child(5)").html("<div style='text-align:center;'>"+section+"</div>");
            for(i=1;i<hourspan;i++){
                nxt.next().children(":nth-child(5)").addClass("bg-teal");
                if(i==hourspan-1)
                nxt.next().children(":nth-child(5)").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                else
                    nxt.next().children(":nth-child(5)").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
                
                nxt = nxt.next();
            }
            $("#sched-table").val($("#sched-table-container").html());
            
        });
        
        $(".Fri").each(function(){
            
            var st = $(this).val();
            var hourspan = $(this).attr('href'); 
            var text = $(this).attr('rel');
            var section = $(this).attr('data-section');
            $("#"+st+" :nth-child(6)").addClass("bg-teal");
            $("#"+st+" :nth-child(6)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(6)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
            nxt.next().children(":nth-child(6)").html("<div style='text-align:center;'>"+section+"</div>");
            for(i=1;i<hourspan;i++){
                nxt.next().children(":nth-child(6)").addClass("bg-teal");
                if(i==hourspan-1)
                nxt.next().children(":nth-child(6)").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                else
                    nxt.next().children(":nth-child(6)").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
                
                nxt = nxt.next();
            }
            $("#sched-table").val($("#sched-table-container").html());
            
        });
        
        $(".Sat").each(function(){
            
            var st = $(this).val();
            var hourspan = $(this).attr('href'); 
            var text = $(this).attr('rel');
            var section = $(this).attr('data-section');
            $("#"+st+" :nth-child(7)").addClass("bg-teal");
            $("#"+st+" :nth-child(7)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(7)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
            nxt.next().children(":nth-child(7)").html("<div style='text-align:center;'>"+section+"</div>");
            for(i=1;i<hourspan;i++){
                nxt.next().children(":nth-child(7)").addClass("bg-teal");
                if(i==hourspan-1)
                nxt.next().children(":nth-child(7)").css({'border-top':'none','border-bottom':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
                else
                    nxt.next().children(":nth-child(7)").css({'border-top':'none','border-left':'1px solid #999','border-right':'1px solid #999'});
                
                nxt = nxt.next();
            }
            $("#sched-table").val($("#sched-table-container").html());
            
        });
        
    });
</script>