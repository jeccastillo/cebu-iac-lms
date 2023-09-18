<script type="text/javascript">
    $(document).ready(function(){
        
       load_subjects(); 
       var data_sub = {};
       data_sub['subjects-loaded'] = [];

      
        $(this).attr('disabled','disabled');
        
        index = 0;
        $("#validate-student :input").each(function(){
            
            if($(this).attr('name') == "subjects-loaded[]"){
                data_sub['subjects-loaded'][index] = $(this).val();
                index++;
            }
            else if($(this).attr('name') != undefined){                    
                data_sub[$(this).attr('name')] = $(this).val();
            }
            

        }).promise().done( function(){ 

            console.log(data_sub);
            
            $.ajax({
                'url':'<?php echo base_url(); ?>registrar/submit_registration_old',
                'method':'post',
                'data':data_sub,
                'dataType':'json',
                'success':function(ret){
                    btn.removeAttr('disabled');                        
                    document.location = '<?php echo base_url(); ?>registrar/advising_done';
                }
            
            });
                
            
            

           

           
        
       });
    });
</script>