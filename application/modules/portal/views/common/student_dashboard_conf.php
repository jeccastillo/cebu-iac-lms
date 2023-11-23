<script>
    $(document).ready(function(e){
        <?php if($deficiencies_count > 1): ?>
            Swal.fire({
                title: 'You have <?php echo $deficiencies_count; ?> deficiencies',
                text: "View Deficiencies?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    document.location = "<?php echo base_url(); ?>portal/deficiencies";
                }
            });
        <?php endif; ?>

    });
</script>