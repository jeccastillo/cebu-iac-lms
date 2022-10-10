<script src="<?php echo $js_dir; ?>jquery.tokeninput.js" type="text/javascript"></script>
    <script type="text/javascript">
      $(function () {
        
        $("#select-faculty-id").tokenInput("<?php echo base_url(); ?>unity/userTokenFaculty/id",{"theme":"facebook","tokenLimit":1,"hintText":'Enter Name of Faculty'});
          
      });
    </script>