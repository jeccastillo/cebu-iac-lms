<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
    $('#subjects-table').dataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>index.php/datatables/data_tables_ajax/tb_mas_grading",
            "aoColumnDefs":[
                {
                    "aTargets":[2],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>grading/edit_grading/'+row[0]+'">Edit</a></li><li><a href="#" rel="'+row[0]+'" class="trash-item">Delete</a></li></ul></div>'; }
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
                        var code = parent.children(':first-child').html();
                        var data = {'id':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/grading/delete_grading_system',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message != "success"){
                                    Swal.fire(
                                        'Failed!',
                                        ret.message,
                                        'error'
                                    )
                                }
                                else{
                                    Swal.fire(
                                        'Deleted!',
                                        'Successfully Deleted',
                                        'success'
                                    ).then(function(){
                                        parent.hide();
                                    });
                                }
                                    

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