<script src="<?php echo $js_dir; ?>jquery.tokeninput.js" type="text/javascript"></script>
    <script type="text/javascript">
      $(function () {
        
        $("#select-student-id").tokenInput("<?php echo base_url(); ?>unity/userTokenEmployee/id",{"theme":"facebook","tokenLimit":1,"hintText":'Enter Name of Employee'});
          
      });
    </script>