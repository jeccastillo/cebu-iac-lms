<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/jsqrcode-combined.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/js/html5-qrcode.min.js"></script>

<script type="text/javascript">
    $('#reader').html5_qrcode(function(data){
         document.location = data.toString();
    },
    function(error){
        //show read errors 
    }, function(videoError){
        //the video stream could be opened
    }
    );
</script>