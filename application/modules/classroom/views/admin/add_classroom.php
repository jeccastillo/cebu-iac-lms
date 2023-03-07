<aside class="right-side">
<section class="content-header">
                    <h1>
                        Classroom
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Classroom</a></li>
                        <li class="active">New Classroom</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">New Classroom</h3>
        </div>
       
            
            <form id="validate-classroom" action="<?php echo base_url(); ?>classroom/submit_classroom" method="post" role="form">
                <div class="box-body">
                         <div class="form-group col-xs-6">
                            <label for="strRoomCode">Classroom Code</label>
                            <input type="text" name="strRoomCode" class="form-control" id="strRoomCode" placeholder="Enter Classroom Number">
                        </div>
                        <div class="form-group col-xs-6">
                            <label for="strRoomCode">Description</label>
                            <input type="text" name="description" class="form-control" id="description" placeholder="Enter Classroom Description">
                        </div>
                         <div class="form-group col-xs-6">
                            <label for="enumType">Classroom Type</label>
                            <select name="enumType" class="form-control">
                                <?php foreach($crType as $cr): ?>
                                    <option value="<?php echo $cr ?>"><?php echo $cr ?></option>
                                <?php endforeach; ?>
                             </select>
                            </div>

                        <div class="form-group col-xs-12">
                            <input type="submit" value="add" class="btn btn-default  btn-flat">
                        </div>
                    <div style="clear:both"></div>
                </div>
            </form>
    </div>
    </div>
</aside>