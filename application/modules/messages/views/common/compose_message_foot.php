    <script src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js" type="text/javascript"></script>
    <script src="<?php echo $js_dir; ?>jquery.tokeninput.js" type="text/javascript"></script>
    <script type="text/javascript">
      $(function () {
        //bootstrap WYSIHTML5 - text editor
        $(".textarea").wysihtml5();
          
        <?php if(!empty($receiver)): ?>
        $("#user-message").tokenInput("<?php echo base_url(); ?>messages/userToken/",{"theme":"facebook",prePopulate:[{id: <?php echo $receiver->intID; ?>, name: "<?php echo $receiver->strFirstname.' '.$receiver->strLastname; ?>"}]});
        <?php else: ?>
          $("#user-message").tokenInput("<?php echo base_url(); ?>messages/userToken/",{"theme":"facebook"});
        <?php endif; ?>
      });
    </script>