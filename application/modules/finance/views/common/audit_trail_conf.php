<script type="text/javascript">
$(document).ready(function() {
    $('#audit_trail_table').DataTable({
        "aLengthMenu": [10, 20, 50, 100, 250, 500, 750, 1000],
        "bProcessing": true,
        "serverSide": false,
        "ordering": false,
        "paging": true,
        ajax: {
            url: "<?php echo $api_url; ?>sms/audit-trail/<?php echo $campus; ?>",
            dataSrc: 'data'
        },
        columns: [{
            data: 'id',
            title: 'ID'
        }, {
            data: 'query',
            title: 'SQL Query'
        }, {
            data: 'user',
            title: 'USER'
        }, {
            data: 'timestamp',
            title: 'TIMESTAMP'
        }]
    });
});
</script>