<aside class="right-side">
<section class="content-header">
                    <h1>
                        Validate Sync
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Sync</a></li>
                        <li class="active">Validate Sync</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Validate Sync</h3>
        </div>
       
            
            <form id="validate-curriculum" action="<?php echo base_url(); ?>unity/sync_users" method="post" role="form">
                <div class="box-body">
                         
                            <input type="hidden" name="execute" class="form-control" value="go">
                            <div class="alert alert-warning alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <h4><i class="icon fa fa-warning"></i> Alert!</h4>
                                Warning This function may take a while please click the button when you are ready to proceed.
                              </div>
                            <div class="form-group col-xs-12">
                                <input type="submit" value="Execute" class="btn btn-default  btn-flat">
                            </div>
                    <div style="clear:both"></div>
                </div>
            </form>
    </div>
    </div>
</aside>