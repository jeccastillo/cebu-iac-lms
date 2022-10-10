<!-- iCheck 1.0.1 -->
<script src="<?php echo base_url(); ?>assets/lib/adminlte/js/plugins/iCheck/icheck.min.js" type="text/javascript"></script>
<script type="text/javascript">
    
    $(document).ready(function(){
        
    $('.recover-message').click(function(e){
        var objects = [];
        $(".message-check:checked").each(function(e)
        {
            //alert($(this).attr("rel"));
            objects.push($(this).attr("rel"));
        });
      
        if(objects.length > 0 ){
                    
            var data = {'ids':objects};
            $.ajax({
                'url':'<?php echo base_url(); ?>messages/trashMessages/0',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    
                        document.location = "<?php echo base_url(); ?>messages/view_messages";
                    }
                });
            
        }

        //alert(data.toString());
    });
        
   
        
    //Enable check and uncheck all functionality
        $(".checkbox-toggle").click(function () {
          var clicks = $(this).data('clicks');
          if (clicks) {
            //Uncheck all checkboxes
            $("#messages_table input[type='checkbox']").iCheck("uncheck");
            $(".fa", this).removeClass("fa-check-square-o").addClass('fa-square-o');
          } else {
            //Check all checkboxes
            $("#messages_table input[type='checkbox']").iCheck("check");
            $(".fa", this).removeClass("fa-square-o").addClass('fa-check-square-o');
          }          
          $(this).data("clicks", !clicks);
        });
    $('#messages_table').dataTable( {
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>datatables/data_tables_ajax/tb_mas_message_user/<?php echo $user['intID']; ?>/1",
            "aoColumnDefs":[
                {
                    "aTargets":[5],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo dropdown_menu_open(); ?><li><a href="<?php echo base_url(); ?>messages/view_message/'+row[0]+'">Read</a></li><li><a href="#" rel="'+row[0]+'" class="trash-message">Delete</a></li><li><a href="#" rel="'+row[0]+'" class="recover-message">Recover</a></li><li><a href="<?php echo base_url(); ?>messages/mark_unread/'+row[0]+'">Mark as Unread</a><li><?php echo dropdown_menu_close(); ?>'; }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                },
                {
                    "aTargets":[4],
                    "bVisible": false 
                }
            ],
            "oLanguage": {"sZeroRecords": "No Messages in Trash", "sEmptyTable": "No Messages in Inbox"},
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
                        var data = {'table':'tb_mas_message_user','id':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>messages/deleteItem/intMessageUserID',
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
                                    document.location = "<?php echo base_url(); ?>messages/view_trash";

                                $(".loading-img").hide();
                                $(".overlay").hide();
                        }
                    });
                    }
                });
                
                $('.recover-message').click(function(e){
                    var objects = [];
                   
                    //alert($(this).attr("rel"));
                    objects.push($(this).attr("rel"));
                   

                    if(objects.length > 0 ){

                        var data = {'ids':objects};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>messages/trashMessages/0',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                    
                                    document.location = "<?php echo base_url(); ?>messages/view_messages";
                                }
                            });

                    }

                    //alert(data.toString());
                });
                
                $('.delete-message').click(function(e){
                    conf = confirm("Are you sure you want to permanently delete?");
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
                                'url':'<?php echo base_url(); ?>messages/deleteMessages',
                                'method':'post',
                                'data':data,
                                'dataType':'json',
                                'success':function(ret){

                                        document.location = "<?php echo base_url(); ?>messages/view_trash";
                                    }
                                });
                        }
                    }
                    //alert(data.toString());
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