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
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/daterangepicker/daterangepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.pie.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/flot/jquery.flot.categories.min.js"></script>
<script>
    
$(document).ready(function(){

    $(window).load(function() {
        var tf = $('#totalFinalized').val();
        var tu = $('#totalSub').val();
        var rs = $('#regStat').val();
        
        if (rs=="For Subject Enlistment" || rs=="For Registration" || rs=="Registered" ) {
            $('#modal-default').modal('show');
        }
        // else if(rs=="For Subject Loading") {
        //     $('#modal-default').modal('show'); 
        // }
        // else if(rs=="Registered") {
        //     $('#modal-default').modal('show'); 

        // }

        if(tf==tu){
            $("#gradeToggle").addClass('hide-grade');
            $(".g-content").hide();
            $(".g-header").hide();
            $("#gradeToggle").html("Show Breakdown");
        }
        else 
        {
            $("#gradeToggle").html("Hide Breakdown");
        }
       
    });

    $("#gradeToggle").click(function(){
        if($(this).hasClass('hide-grade'))
        {
            $(".g-content").show();
            $(".g-header").show();
            $(this).removeClass('hide-grade');
            $(this).html("Hide Breakdown");
        }
        else{
            $(this).addClass('hide-grade');
            $(".g-content").hide();
            $(".g-header").hide();
            $(this).html("Show Breakdown");
        }

    });
    
    
    
    
    $(".Mon").each(function(){
            
            var st = $(this).val();
            var hourspan = $(this).attr('href'); 
            var text = $(this).attr('rel');
            $("#"+st+" :nth-child(2)").addClass("bg-teal");
            $("#"+st+" :nth-child(2)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(2)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
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
            $("#"+st+" :nth-child(3)").addClass("bg-teal");
            $("#"+st+" :nth-child(3)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(3)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
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
            $("#"+st+" :nth-child(4)").addClass("bg-teal");
            $("#"+st+" :nth-child(4)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(4)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
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
            $("#"+st+" :nth-child(5)").addClass("bg-teal");
            $("#"+st+" :nth-child(5)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(5)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
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
            $("#"+st+" :nth-child(6)").addClass("bg-teal");
            $("#"+st+" :nth-child(6)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(6)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
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
            $("#"+st+" :nth-child(7)").addClass("bg-teal");
            $("#"+st+" :nth-child(7)").css({'border-top':'1px solid #999','border-left':'1px solid #999','border-right':'1px solid #999'});
            $("#"+st+" :nth-child(7)").html("<div style='text-align:center;'>"+text+"</div>");
            //$("#"+st+" :nth-child(3)").attr('rowspan',hourspan);
            nxt = $("#"+st);
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
    
     $("#select-sem").change(function(e){
            document.location = "<?php echo base_url(); ?>portal/select_sem/"+$(this).val()+"/"+$(this).find('option:selected').attr('rel');
        
        });
    
    
});
    

    
</script>
