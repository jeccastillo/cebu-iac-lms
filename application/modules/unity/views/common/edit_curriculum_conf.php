<script type="text/javascript">
$(document).ready(function() {
    let equivalentSubjectID = document.getElementById('equivalent')
    $(function() {
        $('[data-toggle="popover"]').popover({
            trigger: 'hover'

        })
    });

    $("#type").on('change', function(e) {
        let isHidden = $(this).val() != 'Equivalent'
        equivalentSubjectID.hidden = isHidden
    });
});
</script>