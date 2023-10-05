<script type="text/javascript">
    $(document).ready(function(){
        $(".cut-off-registration").click(function(e){
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
                        html:'Are you absolutely sure you want continue with cut off?<br /><div class="form-group"><label>Enter Enlistment Cut-off Date</label></div><input type="date" class="form-control" autofocus></div>',
                        showCancelButton: true,                        
                        confirmButtonText: "Continue",
                        imageWidth: 100,
                        icon: "question",
                        cancelButtonText: "No, cancel!",
                        showCloseButton: true,
                        showLoaderOnConfirm: true,
                        preConfirm: (inputValue) => {  
                            var sem = $(this).attr('rel');
                            var data = {'date':inputValue};
                            $.ajax({
                                'url':'<?php echo base_url(); ?>registrar/cut_off_registration/'+sem,
                                'method':'post',
                                'data':data,
                                'dataType':'json',
                                'success':function(ret){
                                    if(ret.success){
                                        Swal.fire({
                                            title: "Success",
                                            text: ret.message,
                                            icon: "success"
                                        }).then(function() {
                                            location.reload();
                                        });      
                                    }
                                    else{
                                        Swal.fire({
                                            title: "Failed",
                                            text: ret.message,
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