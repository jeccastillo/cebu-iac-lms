<script type="text/javascript">
    
    $(document).ready(function(){
        
        $(".section-update").change(function(e){
            var classlist = $(this).val(); 
            var clid = $(this).attr('rel');
            
            var data = {'intCSID':clid,'intClassListID':classlist};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_section_ajax',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    alert("updated");
                }
            });
            
        });
        
        
    });
    
</script>