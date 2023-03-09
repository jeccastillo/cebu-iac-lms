<aside class="right-side">
<section class="content-header">
                    <h1>
                        <small>
                                    <a class="btn btn-app" href="<?php echo base_url() ?>classroom/classroom_viewer/<?php echo $item['intID']; ?>" ><i class="ion ion-eye"></i>View</a> 
                               <a class="btn btn-app" href="<?php echo base_url() ?>classroom/view_classrooms" ><i class="ion ion-arrow-left-a"></i>View All</a> 
                            
                        </small>
                        <div class="pull-right">
                    </div>
                    </h1>   
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Classroom</a></li>
                        <li class="active">Edit Classroom</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Edit Classroom</h3>
        </div>
       
            
            <form id="validate-classroom" action="<?php echo base_url(); ?>classroom/submit_edit_classroom" method="post" role="form">
                <div class="box-body">
                        <input type="hidden" name="intID" value="<?php echo $item['intID']; ?>" />
                         <div class="form-group col-xs-6">
                            <label for="strRoomCode">Classroom Code</label>
                            <input type="text" value="<?php echo $item['strRoomCode']; ?>" name="strRoomCode" class="form-control" id="strRoomCode" placeholder="Enter Subject Code">
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="description">Description</label>
                            <input type="text"  value="<?php echo $item['description']; ?>" name="description" class="form-control" id="description" placeholder="Enter Classroom Description">
                        </div>
                         <div class="form-group col-xs-6">
                            <label for="enumType">Classroom Type</label>
                            <select name="enumType" class="form-control">
                                <?php foreach($crType as $cr): ?>
                                    <option <?php echo ($item['enumType']=='cr')?'selected':''; ?> value="<?php echo $cr ?>"><?php echo $cr ?></option>
                                <?php endforeach; ?>
                             </select>
                            </div>

                        <div class="form-group col-xs-12">
                            <input type="submit" value="update" class="btn btn-default  btn-flat">
                        </div>
                    <div style="clear:both"></div>
                </div>
            </form>
    </div>
    </div>
</aside>