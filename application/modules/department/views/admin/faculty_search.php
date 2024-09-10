<aside class="right-side">
<section class="content-header">
                    <h1>
                        Faculty Loading
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Faculty Loading</a></li>
                        <li class="active">Faculty Search</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Faculty Loading</h3>
                
        </div>
       
        <div class="box box-solid">
            <div class="box-body">
                <?php if($error_message!=""): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <strong>Error</strong> <?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <hr />
            
             <form id="load-faculty" action="<?php echo base_url(); ?>department/faculty_load_subjects" method="post" role="form">
                 <p>Search Faculty</p>
                 <div class="row">
                     <div class="col-sm-6">
                        <input type="text" id="select-faculty-id" name="facultyID" placeholder="Select Faculty" class="form-control" />
                     </div>
                     <div class="col-sm-6">
                     <select id="sem" name="sem" class="form-control" >
                        <?php foreach($sy as $s): ?>
                            <option <?php echo ($sem == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['term_student_type']." ".$s['enumSem']." ".$s['term_label']." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                        <?php endforeach; ?>
                    </select>
                     </div>
                 </div>
                 <br />
                 <input class="btn btn-info btn-flat" type="submit" />
                 
            </form>
            </div>
        </div>
       
        </div>
</aside>