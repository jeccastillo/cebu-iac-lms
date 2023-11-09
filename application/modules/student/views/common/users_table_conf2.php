<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
        
    $('#advanced-search').click(function(){
        var course = $("#intProgramID").val();
        var status = $("#academicStatus").val();
        var year = $("#intYearLevel").val();
        var gender = $("#gender").val();
        var graduate = $("#graduate").val();
        var sem = $("#sem").val();
        var scholarship = $("#scholarship").val();
        var registered = $("#registered").val();
        
        document.location = "<?php echo base_url(); ?>student/view_all_students2/"+course+"/"+status+"/"+year+"/"+gender+"/"+graduate+"/"+sem+"/"+scholarship+'/'+registered;
        
    });
    $('#users_table2').dataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>datatables/data_tables_ajax2/tb_mas_users/null/null/<?php echo $course."/".$postreg."/".$postyear."/".$gender."/".$graduate."/".$scholarship."/".$registered."/".$sem; ?>",
            "aoColumnDefs":[
                {
                    "aTargets":[7],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>student/edit_student/'+row[0]+'">Edit</a></li><li><a href="#" rel="'+row[0]+'" class="trash-item">Delete</a></li><li><a href="<?php echo base_url(); ?>unity/registration_viewer/'+row[0]+'">Finances</a></li></ul></div>'; }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                },
                {
                    "aTargets":[3],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<a href="<?php echo base_url(); ?>finance/student_ledger/'+row[0]+'">'+row[3]+'</a>'; }
                },
            ],
            "aaSorting": [[2,'asc']],
            "fnDrawCallback": function () {  
                $(".trash-item").click(function(e){
                    conf = confirm("Are you sure you want to delete?");
                    if(conf)
                    {
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr('rel');
                        var parent = $(this).parent().parent().parent().parent().parent();
                        var data = {'table':'tb_mas_users','id':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/student/delete_student',
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