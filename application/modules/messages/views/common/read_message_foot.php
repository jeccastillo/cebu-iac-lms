    <script src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <script src="<?php echo $js_dir; ?>jquery.tokeninput.js" type="text/javascript"></script>
    <script type="text/javascript">
      $(function () {
        //bootstrap WYSIHTML5 - text editor
        $(".textarea").wysihtml5();
        $("#user-message").tokenInput("<?php echo base_url(); ?>messages/userToken/",{"theme":"facebook"});
          $("#reply-button").click(function(e){
            message = $("#message-box").val();
            data = {'strReplyMessage':message,'intMessageID':$("#messageID").val(),'intFacultyIDSender':$("#sender").val()};
            
             $.ajax({
               type: "POST",
               data:data,
               url: "<?php echo base_url(); ?>messages/post_reply",
               dataType:'json',
               success: function(data){
                    document.location = '<?php echo current_url(); ?>';
               }
             });
             
        });
          
        $(".delete-reply").click(function(e){
            var id = $(this).attr("rel");
            var sender = $(this).attr("name");
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
              data = {'id':id,'sender':sender};
            
             $.ajax({
               type: "POST",
               data:data,
               url: "<?php echo base_url(); ?>messages/delete_reply",
               dataType:'json',
               success: function(data){
                     if(data.message == "failed"){
                        alert('something went terribly wrong');                                    
                        }
                        else
                            document.location = '<?php echo current_url(); ?>';
                   
               }
             });
            }
            
        });
          
      });
    </script>