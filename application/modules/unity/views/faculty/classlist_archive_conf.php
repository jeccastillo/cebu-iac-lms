<!-- iCheck 1.0.1 -->
<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
<script type="text/javascript">
    
    $(document).ready(function(){
        

    
    $('.download-classlist').click(function(e){
        var objects = [];
        $(".message-check:checked").each(function(e)
        {
            //alert($(this).attr("rel"));
            objects.push($(this).attr("rel"));
        });

        if(objects.length > 0 ){
//            var data = {'ids':objects};
//            $.ajax({
//                'url':'<?php echo base_url(); ?>excel/download_classlists_archive',
//                'method':'post',
//                'data':data,
//                'dataType':'json',
//                'success':function(ret){
//
//                        alert(ret);
//                    }
//                });
            $("#download-archive").submit();
        }
        //alert(data.toString());
    });
        
    $('.delete-classlist').click(function(e){
        conf = confirm("Are you sure you want to delete?");
        if(conf)
        {
            var objects = [];
            $(".message-check:checked").each(function(e)
            {
                //alert($(this).attr("rel"));
                objects.push($(this).attr("rel"));
            });

            if(objects.length > 0 ){
                var data = {'ids':objects};
                $.ajax({
                    'url':'<?php echo base_url(); ?>messages/trashMessages',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){

                            document.location = "<?php echo base_url(); ?>messages/view_messages";
                        }
                    });
            }
        }
        //alert(data.toString());
    });
        
    //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function () {
          var clicks = $(this).data('clicks');
          if (clicks) {
            //Uncheck all checkboxes
            $("#classlist-archive-table input[type='checkbox']").iCheck("uncheck");
            $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
          } else {
            //Check all checkboxes
            $("#classlist-archive-table input[type='checkbox']").iCheck("check");
            $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
          }          
          $(this).data("clicks", !clicks);
        });
        
        
   $('#classlist-archive-table').dataTable({
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>index.php/datatables/data_tables_ajax_cs_faculty/<?php echo $selected_ay; ?>",
            "aoColumnDefs":[
                {
                    "aTargets":[4],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>unity/classlist_viewer/'+row[0]+'">View Classlist</a></li><li><a href="<?php echo base_url(); ?>unity/edit_classlist/'+row[0]+'">Edit Classlist</a></li>'; }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                },
               /*/ {
                    "aTargets":[4],
                    "bVisible": false 
                }*/
            ],
            "aaSorting": [[4,'desc']],
            "fnDrawCallback": function () {  
                
                //iCheck for checkbox and radio inputs
                $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
                  checkboxClass: 'icheckbox_flat-blue',
                  radioClass: 'iradio_minimal-blue'
                });
                $(".trash-message").click(function(e){
                    conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent().parent().parent().parent();
                        var data = {'table':'tb_mas_classlist','id':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>messages/trashMessage',
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
                                    document.location = "<?php echo base_url(); ?>messages/view_messages";

                                $(".loading-img").hide();
                                $(".overlay").hide();
                        }
                    });
                    }
                });
            
            },
            "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                    if(aData[4] != 0)
                    {
                        $(nRow).css('background', '#f9f9f9')
                    }
                }
        } );
        
    });

</script>