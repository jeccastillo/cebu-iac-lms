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
                $.ajax({
                    'url':'<?php echo base_url(); ?>registrar/submit_registration_old',
                    'method':'post',
                    'data':data_sub,
                    'dataType':'json',
                    'success':function(ret){
                        btn.removeAttr('disabled');                        
                        //Send Email to Finance with this Assessment
                        var assessment_data = 
                        {
                            'assessment':$("#tuitionContainer").html(),
                            'student_name': "<?php echo $student['strLastname'].", ".$student['strFirstname'] ?>"

                        }
                        // $.ajax({
                        //     'url': 'api_url_goes_here',
                        //     'method':'post',
                        //     'data':assessment_data,
                        //     'dataType':'json',
                        //     'success':function(ret){
                        //         btn.removeAttr('disabled');                                                        
                                
                        //         document.location = '<?php echo base_url(); ?>registrar/advising_done';
                        //     }
                        
                        // });
                        console.log(api_url);
                        document.location = '<?php echo base_url(); ?>registrar/advising_done';
                    }
                
                });
                
            });                       
           
        
       });
    });
</script>