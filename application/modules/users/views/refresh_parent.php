<body onload="javascript:init()">
</body>
<script type="text/javascript">
		function init()
		{
			opener.parent.location.href = '<?php echo base_url(); ?>';
            window.close();
			
		}
</script>