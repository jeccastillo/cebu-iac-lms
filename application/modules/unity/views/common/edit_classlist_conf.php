<?php $d_open = '<div class="btn-group"><button type="button" class="btn btn-default">Actions</button><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu" role="menu">';
?>
<script type="text/javascript">
    
    $(document).ready(function(){
        
    $("#subject-lock").click(function(){
            rel = $(this).attr('rel');
            
            if(rel == "locked")
            {
                conf = confirm("Are you sure you want to unlock?");
                if(conf){
                    $(this).attr('rel','unlocked');
                    $(this).find('i').removeClass('ion-locked');
                    $(this).find('i').addClass('ion-unlocked');
                    $("#subjects").removeAttr('disabled');
                }
            }
            else
            {
                $(this).attr('rel','locked');
                $(this).find('i').removeClass('ion-unlocked');
                $(this).find('i').addClass('ion-locked');
                $("#subjects").attr('disabled','disabled');
            }
        });
        

        $("#sub-section-lock").click(function(){
            rel = $(this).attr('rel');
            
            if(rel == "locked")
            {
                conf = confirm("Are you sure you want to unlock?");
                if(conf){
                    $(this).attr('rel','unlocked');
                    $(this).find('i').removeClass('ion-locked');
                    $(this).find('i').addClass('ion-unlocked');
                    $("#sub_section").removeAttr('disabled');
                }
            }
            else
            {
                $(this).attr('rel','locked');
                $(this).find('i').removeClass('ion-unlocked');
                $(this).find('i').addClass('ion-locked');
                $("#sub_section").attr('disabled','disabled');
            }
        });

        $("#section-lock").click(function(){
            rel = $(this).attr('rel');
            
            if(rel == "locked")
            {
                conf = confirm("Are you sure you want to unlock?");
                if(conf){
                    $(this).attr('rel','unlocked');
                    $(this).find('i').removeClass('ion-locked');
                    $(this).find('i').addClass('ion-unlocked');
                    $("#section").removeAttr('disabled');
                }
            }
            else
            {
                $(this).attr('rel','locked');
                $(this).find('i').removeClass('ion-unlocked');
                $(this).find('i').addClass('ion-locked');
                $("#section").attr('disabled','disabled');
            }
        });
        
    $('#student-chooser').dataTable( {
            "aLengthMenu":  [10, 20,50,100, 250, 500, 750, 1000],
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": "<?php echo base_url(); ?>datatables/data_tables_ajax/tb_mas_users",
            "aoColumnDefs":[
                {
                    "aTargets":[5],
                    "mData": null,
                    "bSortable":false,
                    "mRender": function (data,type,row,meta) { return '<input type="button" class="add-to-classlist" name="students[]" value="add" rel="'+row[0]+'">'; }
                },
                {
                    "aTargets":[0],
                    "bVisible": false 
                },
                {
                    "aTargets":[1],
                    "bVisible": false 
                }
            ],
            "aaSorting": [[2,'asc']],
            "fnDrawCallback": function () {  
                $(".add-to-classlist").click(function(e){
                   
                    
                        $(".loading-img").show();
                        $(".overlay").show();
                        var id = $(this).attr("rel");
                        var data = {'intClassListID':'<?php echo $classlist['intID'] ?>','intStudentID':id};
                        $.ajax({
                            'url':'<?php echo base_url(); ?>index.php/unity/add_to_classlist',
                            'method':'post',
                            'data':data,
                            'dataType':'json',
                            'success':function(ret){
                                if(ret.message == "failed"){
                                    alert("Student already in classlist");
                                    
                                }
                                else if(ret.message == "failed2"){
                                    alert("Classlist already finalized");
                                }
                                else if(ret.message == "failed3"){
                                    alert("Insufficient access");
                                }
                                else if(ret.message != "success"){
                                    alert(ret.message+" Delete from enlisted section before adding to avoid conflict");
                                }
                                else
                                    alert("added");
                                

                                $(".loading-img").hide();
                                $(".overlay").hide();
                        }
                    });
                    
                    
                });
            
            },
        } );
        
    });

</script>