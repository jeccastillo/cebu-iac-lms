<script type="text/javascript">
    $(document).ready(function(){
        
        
        $(".prelimInput").keyup(function(key) {
            
           
               if(key.keyCode == 13){
                   var next = $(this).parent().parent().next();
                    if(!next.find('.prelimInput').prop('disabled')){
                        next.find('.prelimInput').focus(); 
                        next.find('.prelimInput').select();
                        if(next.find('.prelimInput').val() == 0){                       
                            next.find('.prelimInput').val('');
                        }
                    }
                    else{
                        while(next.next().find('.prelimInput').prop('disabled')){
                            //console.log("line ");
                            next = next.next();
                        }
                        
                        next.next().find('.prelimInput').focus(); 
                        if(next.next().find('.prelimInput').val() == 0)                            
                            next.next().find('.prelimInput').val('');
                    }
                   
//                    $(this).parent().parent().next().find('.prelimInput').focus();  
//                    if($(this).parent().parent().next().find('.prelimInput').val() == 0 && !$(this).parent().parent().next().hasAttribute('disabled'))
//                        $(this).parent().parent().next().find('.prelimInput').val('');
                }

                if($(this).val().length > 3)
                    key.preventDefault();

                if(parseInt($(this).val()) >= 100)
                   $(this).val('100');
                
             
                
            });
           
        
        
        $(".midtermInput").keyup(function(key) {
            
           
               if(key.keyCode == 13){
                   var next = $(this).parent().parent().next();
                    if(!next.find('.midtermInput').prop('disabled')){
                        next.find('.midtermInput').focus();
                        next.find('.midtermInput').select(); 
                        if(next.find('.midtermInput').val() == 0){                       
                            next.find('.midtermInput').val('');
                        }
                    }
                    else{
                        while(next.next().find('.midtermInput').prop('disabled')){
                            //console.log("line ");
                            next = next.next();
                        }
                        
                        next.next().find('.midtermInput').focus(); 
                        if(next.next().find('.midtermInput').val() == 0)                            
                            next.next().find('.midtermInput').val('');
                    }
                   
//                    $(this).parent().parent().next().find('.midtermInput').focus();
//                    if($(this).parent().parent().next().find('.midtermInput').val() == 0 && !$(this).parent().parent().next().hasAttribute('disabled'))
//                         $(this).parent().parent().next().find('.midtermInput').val('');
                }

                if($(this).val().length > 3)
                    key.preventDefault();

                if(parseInt($(this).val()) >= 100)
                   $(this).val('100');
           
        });
        
        // $(".finalsInput").keyup(function(key) {
            
           
        //        if(key.keyCode == 13){
        //            var next = $(this).parent().parent().next();
        //             if(!next.find('.finalsInput').prop('disabled')){
        //                 next.find('.finalsInput').focus();
        //                 next.find('.finalsInput').select(); 
        //                 if(next.find('.finalsInput').val() == 0){                       
        //                     next.find('.finalsInput').val('');
        //                 }
        //             }
        //             else{
        //                 while(next.next().find('.finalsInput').prop('disabled')){
        //                     //console.log("line ");
        //                     next = next.next();
        //                 }
                        
        //                 next.next().find('.finalsInput').focus(); 
        //                 if(next.next().find('.finalsInput').val() == 0)                            
        //                     next.next().find('.finalsInput').val('');
                        
                        
        //             }
        //         }

        //         if($(this).val().length > 3)
        //             key.preventDefault();

        //         if(parseInt($(this).val()) >= 100)
        //            $(this).val('100');
                
            
           
        // });
        
    
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
        
        $("#transfer-classlist").click(function(){
            //alert($("#transfer-to").val());
            var students = new Array();
            $(".student-select:checked").each(function(){
               students.push($(this).val());
            });
           data = {'transferTo':$("#transfer-to").val(),'students':students,'classlistFrom':$("#intClasslistID").val()}
           
            conf = confirm("Are you sure you want to transfer? Warning: Transferring students will reset their grade and remarks.");
            if(conf)
            {
                $.ajax({
                    'url':'<?php echo base_url(); ?>index.php/unity/transfer_classlist',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        if(ret.message == "failed"){
                            alert("you are not allowed");
                        }
                        document.location = "<?php echo current_url(); ?>";
                    }
                       
            });
            }
           
        });
        
        $("#view-classlist").click(function(){
            document.location = "<?php echo base_url(); ?>unity/classlist_viewer/"+$("#transfer-to").val();
        }); 
        
    });
</script>