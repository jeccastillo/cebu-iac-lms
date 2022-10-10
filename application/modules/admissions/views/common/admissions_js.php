<script type="text/javascript">
    $(document).ready(function(){
       
        $("#intExamScore").blur(function(e){
            var txt = $(this).val();
            var id = $("#appId").val();
            var data = {'intExamScore':txt,'intApplicationID':id};
            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/update_data/tb_mas_exam_info/intApplicationID',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                  
                }
            });
        });
        
         $("#strExamRemarks").blur(function(e){
            var txt = $(this).val();
            var id = $("#appId").val();
            var data = {'strExamRemarks':txt,'intApplicationID':id};
            $.ajax({
                'url':'<?php echo base_url(); ?>admissions/update_data/tb_mas_exam_info/intApplicationID',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                  
                }
            });
        });
        
    });

</script>