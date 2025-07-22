<script type="text/javascript">
    $(document).ready(function(){
        
    load_subjects(); 
       var data_sub = {};
    //    var additional_elective = [];
       data_sub['subjects-loaded'] = [];
       data_sub['additional_elective'] = [];
    //    data_sub['additional_elective'] = 0;

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
                    // additional_elective[indexElective] = $(this).val();
                    // if ($(this).is(':checked')) {
                        // data_sub['additional_elective'][indexElective] = $(this).val();
                    // }
                    // indexElective++;
                }
                else if($(this).attr('name') != undefined){                    
                    data_sub[$(this).attr('name')] = $(this).val();
                }

             
                

            }).promise().done( function(){ 

                console.log(data_sub);

                // if (additional_elective.includes('1')) {                               
                //     data_sub['additional_elective'] = 1
                // }            
                
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
    });
</script>