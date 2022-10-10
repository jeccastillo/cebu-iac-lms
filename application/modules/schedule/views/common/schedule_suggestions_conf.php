<script type="text/javascript">
    $(document).ready(function(){
       
        $('#schedule-table').dataTable({
             "aoColumnDefs": [
                          { "aTargets": [0]},
                          { "aTargets": [1]},
                          { "aTargets": [2]},
                          { "bSearchable":false,"bSortable" :false,"aTargets":[3]}
                      ]
         
         });
        
    });
</script>