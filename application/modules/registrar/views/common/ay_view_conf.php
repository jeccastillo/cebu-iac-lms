<script type="text/javascript">
    $(document).ready(function(){
        $("#cut-off-registration").click(function(e){
            Swal.fire({
            title: 'Cut Off Registration',
            text: "Are you sure you want continue with cut off?",
            showCancelButton: true,
            confirmButtonText: "Yes",
            imageWidth: 100,
            icon: "question",
            cancelButtonText: "No, cancel!",
            showCloseButton: true,
            showLoaderOnConfirm: true,
                preConfirm: (login) => {       
                    Swal.fire({
                        title: 'Cut Off Registration',
                        text: "Are you absolutely sure you want continue with cut off? Enter today's date in yyyy-mm-dd format.",                            
                        showCancelButton: true,
                        input:"text",
                        confirmButtonText: "Yes",
                        imageWidth: 100,
                        icon: "question",
                        cancelButtonText: "No, cancel!",
                        showCloseButton: true,
                        showLoaderOnConfirm: true,
                        preConfirm: (inputValue) => {  
                            var sem = $(this).attr('rel');
                            var data = {'date':inputValue};
                            $.ajax({
                                'url':'<?php echo base_url(); ?>/registrar/cut_off_registration/'+sem,
                                'method':'post',
                                'data':data,
                                'dataType':'json',
                                'success':function(ret){
                                    if(ret.success){
                                        Swal.fire({
                                            title: "Success",
                                            text: data.data.message,
                                            icon: "success"
                                        }).then(function() {
                                            location.reload();
                                        });      
                                    }
                                    else{
                                        Swal.fire({
                                            title: "Failed",
                                            text: data.data.message,
                                            icon: "error"
                                        }).then(function() {                                            
                                        });      
                                    }                                    
                                }
                            });
                        }
                    });
                }
            });
        });
        
        
    });
</script>