<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
        
    //var table = $('#users_table').DataTable( {
    //$('#users_table').dataTable( {
    var table = $('#completion_table').DataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "autoWidth": true,
            "sAjaxSource": "<?php echo base_url(); ?>datatables/data_tables_completion/",
            "aoColumnDefs":[
                {
                    "aTargets":[10],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>registrar/approveCompletion/'+row[0]+'">Approve</a></li><li><a href="<?php echo base_url(); ?>pdf/student_completion_form_print/'+row[1]+'">Print</a></li></ul></div>'; }
                },
                {
                    "aTargets":[1],                   
                    "bVisible": false 
                   
                },
                {
                    "aTargets":[2],                   
                    "bSortable":false,
                    //"width":"15%"
                   
                },
                {
                    "aTargets":[3],                                       
                    //"width":"15%"
                   
                },
                {
                    "aTargets":[4],                                       
                    //"width":"8%"
                   
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                },
             
                {
                    "aTargets":[5],
                    //"width": "5%"
                },
                {
                    "aTargets":[6],                                       
                    //"width":"5%"
                   
                },
                {
                    "aTargets":[7],                                       
                    //"width":"20%"
                   
                },
                {
                    "aTargets":[8],                                       
                    //"width":"5%"
                },
             
                {
                    "aTargets":[9],                                       
                    //"width":"10%"
                    //"bVisible": false 
                },
                {
                    "aTargets":[10],                                       
                    //"width":"10%"
                    //"bVisible": false 
                }
            ],
            "aaSorting": [[2,'asc']],
            "fnDrawCallback": function () {  

            },
        } );

      
        
    });

</script>