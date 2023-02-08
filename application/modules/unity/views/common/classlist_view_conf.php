<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
        
    $('#classlist-table-admin tfoot th').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="'+title+'" size="16" />' );
    });

     var table = $('#classlist-table-admin').DataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "autoWidth": false,
            "sAjaxSource": "<?php echo base_url(); ?>index.php/datatables/data_tables_ajax_cs/<?php echo $selected_ay; ?>",
            "aoColumnDefs":[
                {
                    "aTargets":[6],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>unity/classlist_viewer/'+row[0]+'">View Classlist</a></li><li><a href="<?php echo base_url(); ?>unity/edit_classlist/'+row[0]+'">Edit Classlist</a></li><li><a href="<?php echo base_url(); ?>unity/reassign_classlist/'+row[0]+'">Re-assign</a></li><li><a href="<?php echo base_url(); ?>unity/duplicate_classlist/'+row[0]+'">Duplicate</a></li><li><a href="#" rel="'+row[0]+'" class="finalizedOption">Change Status</a></li><li><a href="#" rel="'+row[0]+'" class="trash-classlist">Delete</a></li></ul></div>'; }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                }
            ],
            "aaSorting": [[1,'asc']],
            "fnDrawCallback": function () {  
               
                $(".finalizedOption").click(function(e){
                    $(".loading-img").show();
                    $(".overlay").show();
                    var id = $(this).attr('rel');
                    var data = {'intID':id};
                    $.ajax({
                        'url':'<?php echo base_url(); ?>unity/update_finalized',
                        'method':'post',
                        'data':data,
                        'dataType':'json',
                        'success':function(ret){
                            document.location = "<?php echo current_url(); ?>";
                        }
                    });

                });
                
                $(".trash-classlist").click(function(e){
                    conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent().parent().parent().parent();
                        //alert(parent.html());
                        var data = {'id':id};

                        $.ajax({
                            'url':'<?php echo base_url(); ?>unity/delete_classlist',
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
        

        
        // Apply the search
        table.columns().every( function () {
            var that = this;

            $( 'input', this.footer() ).on( 'keyup change', function () {
                if ( that.search() !== this.value ) {
                    that
                        .search( this.value )
                        .draw();
                }
            } );
        } );
        
    });

</script>