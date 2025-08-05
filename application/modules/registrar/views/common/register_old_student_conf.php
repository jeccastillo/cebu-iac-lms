<script type="text/javascript">
    $(document).ready(function(){
        
    load_subjects();
       var data_sub = {};
       data_sub['subjects-loaded'] = [];
       data_sub['additional_elective'] = [];

       $("#submit-button").click(function(e){
            e.preventDefault();            
            var btn = $(this);
            $(this).attr('disabled','disabled');
           
            index = 0;
            indexElective = 0;

            $("#validate-student :input").each(function(){
                
                if($(this).attr('name') == "subjects-loaded[]"){
                    data_sub['subjects-loaded'][index] = $(this).val();
                    index++;
                }
                else if($(this).attr('name') == "additional_elective[]"){
                    if ($(this).prop('checked')) {
                        data_sub['additional_elective'].push($(this).val());
                    }
                    
                }
                else if($(this).attr('name') != undefined){                    
                    data_sub[$(this).attr('name')] = $(this).val();
                }

            }).promise().done( function(){ 
                console.log($("#tuitionContainer").html());
                // $.ajax({
                //     'url':'<?php echo base_url(); ?>registrar/submit_registration_old',
                //     'method':'post',
                //     'data':data_sub,
                //     'dataType':'json',
                //     'success':function(ret){
                //         btn.removeAttr('disabled');                        
                //         //Add API to send email with the amount                        
                //         document.location = '<?php echo base_url(); ?>registrar/advising_done';
                //     }
                
                // });
                
            });                       
           
        
       });
    });
</script>