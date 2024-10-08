<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
    $('#blocksection-table').dataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>index.php/datatables/data_tables_ajax/tb_mas_block_sections",
            "aoColumnDefs":[
                {
                    "aTargets":[4],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>blocksection/block_section/'+row[0]+'">Edit</a></li><li><a href="#" rel="'+row[0]+'" class="trash-item">Delete</a></li><li><a href="<?php echo base_url(); ?>blocksection/block_section_viewer/'+row[0]+'">View</a></li></ul></div>'; }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                }
            ],
            "aaSorting": [[1,'asc']],
            "fnDrawCallback": function () {  
                $(".trash-item").click(function(e){
                    conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent().parent().parent().parent();
                        var data = {'id':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/blocksection/delete_blocksection',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message == "failed"){
                                    $(".alert").show();
                                    setTimeout(function() {
                                        $(".alert").hide('fade', {}, 500)
                                    }, 3000);
                                }
                                else
                                    parent.hide();

                                $(".loading-img").hide();
                                $(".overlay").hide();
                        }
                    });
                    }
                });
            
            },
        } );
        
    });

</script>