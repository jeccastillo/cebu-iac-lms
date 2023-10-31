<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
    $('#subjects-table').dataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>index.php/datatables/data_tables_ajax/tb_mas_curriculum",
            "aoColumnDefs":[
                {
                    "aTargets":[3],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>unity/edit_curriculum/'+row[0]+'">Edit/View</a></li><li><a target="_blank" href="<?php echo base_url(); ?>pdf/print_curriculum_subjects/'+row[0]+'">Print Curriculum</a></li><li><a href="<?php echo base_url(); ?>unity/generate_classlists/'+row[0]+'">Generate Sections</a></li><li><a href="#" rel="'+row[0]+'" class="duplicate-curriculum">Duplicate</a></li><li><a href="#" rel="'+row[0]+'" class="trash-item">Delete</a></li></ul></div>'; }
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
                        var data = {'id':id,'code':code};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/unity/delete_curriculum',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message == "failed"){
                                    $("#alert-text").html('<b>Alert! '+code+'</b> cannot be deleted it is connected to students.')
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

                $(".duplicate-curriculum").click(function(e){
                    Swal.fire({
                        title: 'Add Credits?',
                        text: "Continue adding credits?",
                        showCancelButton: true,
                        confirmButtonText: "Yes",
                        imageWidth: 100,
                        icon: "question",
                        cancelButtonText: "No, cancel!",
                        showCloseButton: true,
                        showLoaderOnConfirm: true,
                        preConfirm: (login) => {
                            $(".loading-img").show();
                                var id = $(this).attr('rel');                        
                                var data = {'id':id};
                                $.ajax({
                                    'url':'<?php echo base_url(); ?>index.php/unity/duplicate_curriculum',
                                    'method':'post',
                                    'data':data,
                                    'dataType':'json',
                                    'success':function(ret){
                                        if(ret.success){
                                            Swal.fire({
                                                title: "Success",
                                                text: data.data.message,
                                                icon: "success"
                                            }).then(function() {
                                                location.reload();
                                            });
                                        }
                                        else
                                        {
                                            Swal.fire({
                                                title: "Failed",
                                                text: data.data.message,
                                                icon: "error"
                                            });
                                        }                
                                    }                            
                                });                                
                            },
                            allowOutsideClick: () => !Swal.isLoading()
                        }); 
                
                    },
                });
        
           });

</script>