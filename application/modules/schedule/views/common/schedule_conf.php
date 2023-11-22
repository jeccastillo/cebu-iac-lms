<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
        
        
    $("#select-sem-schedule").change(function(e){
            document.location = "<?php echo base_url(); ?>schedule/view_schedules/"+$(this).val()+"/"+$("#select-section-schedule").val();
        
    });
    $("#select-section-schedule").change(function(e){
        document.location = "<?php echo base_url(); ?>schedule/view_schedules/"+$("#select-sem-schedule").val()+"/"+$(this).val();
    
    });

    $('#users_table tfoot th').each( function () {
        var title = $(this).text();
        $(this).html( '<input type="text" placeholder="'+title+'" size="16" />' );
    });
        
    var table = $('#users_table').DataTable({
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>index.php/datatables/data_tables_ajax/tb_mas_room_schedule/null/null/0/0/0/0/0/0/0/<?php echo $sem; ?>/<?php echo $section; ?>",
            "aoColumnDefs":[
                {
                    "aTargets":[12],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<?php echo $d_open; ?><li><a href="<?php echo base_url(); ?>schedule/edit_schedule/'+row[0]+'">Edit</a></li><li><a href="#" rel="'+row[0]+'" class="trash-item">Delete</a></li></ul></div>'; }
                },
                {
                    "aTargets":[7],                    
                    "mRender": function (data,type,row,meta) { 
                        var day = "Monday";                        
                        switch(row[7]){
                            case "1":
                                day = "Monday";
                                break;
                            case "2":
                                day = "Tuesday";
                                break;
                            case "3":
                                day = "Wednesday";
                                break;
                            case "4":
                                day = "Thursday";
                                break;
                            case "5":
                                day = "Friday";
                                break;
                            case "6":
                                day = "Saturday";
                                break;
                            case "7":
                                day = "Sunday";
                                break;
                        }    

                        return day;
                    }
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
                        var data = {'table':'tb_mas_schedule','id':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/schedule/delete_schedule',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message != "success"){
                                    $(".alert").show();
                                    $("#alert-container").html(ret.message);
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
        
        $("#addSchedBtn").click(function(e){

            subject = $("#subject").val();
            section = $("#section").val();
            strDay = $("#strDay").val();
            intRoomID = $("#intRoomID").val();
            dteStart = $("#dteStart").val();
            dteEnd = $("#dteEnd").val();
            enumClassType = $("#enumClassType").val();
            if(section!=''){
            var data = {'subject':subject,'section':section,'strDay':strDay,'intRoomID':intRoomID,'dteStart':dteStart,'dteEnd':dteEnd,'enumClassType':enumClassType};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/submit_schedule_ajax',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message != "success"){
                        $("#sched-alert").html('<b>Alert!</b> '+ret.message);
                        $(".alert-modal").show();
                        setTimeout(function() {
                            $(".alert").hide('fade', {}, 500)
                        }, 3000);
                    }
                    else
                        document.location= "<?php echo current_url(); ?>";

            }
            });
            }
            else
            {
                alert("enter a value for section");
            }
        });

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