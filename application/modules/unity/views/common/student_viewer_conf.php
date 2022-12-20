<script type="text/javascript">
    $(document).ready(function(){
        
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
            $("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
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

        $(".view-curriculum-pdf").click(function(e){
            e.preventDefault();
            var csid = $(this).attr('rel');
            var data = {'id':csid};
            $.ajax({
                'url':'<?php echo base_url()."pdf/get_curriculum_for_printing/".$student['slug'] ?>',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    console.log(ret);
                    //Send to laravel API
                }
            });        
        });        
                        
        
    });
</script>