<aside class="right-side">
    <section class="content-header">
                    <h1>
                        Classlist
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-android-book"></i> Classlist</a></li>
                        <li class="active">View Classlists</li>
                    </ol>
                </section>
    <section class="content">
        <div class="alert alert-danger" style="display:none;">
            <i class="fa fa-ban"></i>
            <b>Alert!</b> Classlist is already finalized and cannot be deleted.
        </div>
        <div class="box box-solid box-danger">
        <div class="overlay" style="display:none;"></div>
        <div class="loading-img" style="display:none;"></div>
        <div class="box-header">
                <h3 class="box-title" style="padding-left:27px;">My Classlists</h3>
                
        </div>
        
        <div class="box-body">    
        
        <?php foreach($classlist as $class): ?>
            <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box <?php 
                        
                        if ($class['intFinalized']==0)
                        {
                            echo 'bg-green'; 
                        }
                        else if ($class['intFinalized']==1)
                        {
                            echo 'bg-yellow'; 
                        }
                        else if ($class['intFinalized']==2)
                        {
                            echo 'bg-blue'; 
                        }
                        else {
                            echo 'bg-red'; 
                        }
                        
                        ?>">
                <div class="box-tools">
                 <div class="btn-group">
                    <button type="button" class="btn <?php echo ($class['intFinalized']==0)?'btn-success':'btn-danger'; ?>"><i class="ion ion-android-settings"></i></button>
                    <button type="button" class="btn <?php echo ($class['intFinalized']==0)?'btn-success':'btn-danger'; ?> dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="<?php echo base_url() ?>unity/edit_classlist/<?php echo $class['intID']; ?>"><i class="ion ion ion-ios7-compose"></i> Edit</a></li>
                    </ul>
                </div>               
                 </div>
                <div class="inner">
                    <h3>
                        <?php echo $class['strCode']; ?>
                    </h3>
                    <p>
                        <?php 
                        $section = $class['strClassName'].$class['year'].$class['strSection']." ".$class['sub_section'];
                        echo $section; ?><br />                        
                    </p>
                    <p>
                        <small><?php echo $class['enumSem']." ".$term_type." ".$class['strYearStart']."-".$class['strYearEnd']; ?></small>
                    </p>
                    <p>
                        
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-book"></i>
                </div>
                <a href="<?php echo base_url(); ?>unity/classlist_viewer/<?php echo $class['intID']; ?>" class="small-box-footer">
                    View <i class="fa fa-arrow-circle-right"></i>
                </a>
                
            </div>
        </div>
            
        
            
        <?php endforeach; ?>
        <?php foreach($advised as $class): ?>
            <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-blue" >
                <div class="box-tools">
                 <div class="btn-group">
                    <button type="button" class="btn <?php echo ($class['intFinalized']==0)?'btn-success':'btn-danger'; ?>"><i class="ion ion-android-settings"></i></button>
                    <button type="button" class="btn <?php echo ($class['intFinalized']==0)?'btn-success':'btn-danger'; ?> dropdown-toggle" data-toggle="dropdown">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        <li><a href="<?php echo base_url() ?>unity/view_section/<?php echo $class['intID']; ?>"><i class="ion ion ion-ios7-compose"></i> View</a></li>
                    </ul>
                </div>               
                 </div>
                <div class="inner">
                    <h3>
                        <?php echo $class['name']; ?>
                    </h3>                   
                </div>
                <div class="icon">
                    <i class="ion ion-android-book"></i>
                </div>
                <a href="<?php echo base_url(); ?>unity/view_section/<?php echo $class['intID']; ?>" class="small-box-footer">
                    View <i class="fa fa-arrow-circle-right"></i>
                </a>                
            </div>
        </div>
            
        
            
        <?php endforeach; ?>
        <hr style="clear:both;" />
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>
                        Archive
                    </h3>
                    <p>
                        View all my previous classes.
                    </p>
                </div>
                <div class="icon">
                    <i class="ion ion-android-folder"></i>
                </div>
                <a href="<?php echo base_url() ?>unity/view_classlist_archive" class="small-box-footer">
                    View <i class="fa fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
        <div style="clear:both"></div>
        </div>
        </div>
    </section>
</aside>