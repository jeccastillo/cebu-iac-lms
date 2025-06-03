<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
    $('#subjects-table').dataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>index.php/datatables/data_tables_ajax/tb_mas_scholarships",
            "aoColumnDefs":[
                {
                    "aTargets":[8],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function(data, type, row, meta) {
                    return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>scholarship/view/'
                        + row[0]
                        +'">Details</a></li></ul></div>';
                    }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                }
            ],
            "aaSorting": [[1,'asc']],
            "fnDrawCallback": function () {                  
            
            },
        } );
        
    });

</script>