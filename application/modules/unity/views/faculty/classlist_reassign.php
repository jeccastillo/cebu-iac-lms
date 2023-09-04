
<aside class="right-side">
<section class="content-header">
                    <h1>
                        Classlist
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url(); ?>unity/view_classlist"><i class="ion ion-ios7-locked"></i> Admin</a></li>
                        <li class="active">Reassign Class</li>
                    </ol>
                </section>
<section class="content">
    <div class="large-10 small-10 medium-10 columns">
        <div class="span10 box box-primary">
            <div class="box-header">
                 <h3 class="box-title">Reassign Class</h3>
            </div>
            <form action="<?php echo base_url(); ?>unity/reassign_class" method="post" role="form">
                
                <input type="hidden" value="<?php echo $classlist['intID']; ?>" name="intID">
               
                
            <div class="box-body">
                <div class="form-group">
                    <label for="intSubjectID">Instructor Assigned</label>
                    <select class="form-control select2" name="intFacultyID" >
                        <?php foreach($teacher as $t): ?>
                            <option <?php echo ($classlist['intFacultyID'] == $t['intID'])?'selected':''; ?> value="<?php echo $t['intID'] ?>"><?php echo $t['strLastname']." ".$t['strFirstname']; ?></option> 
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                        <label for="strClassName">Section</label>
                        <h4><?php echo $classlist['strClassName']; ?></h4>
                    </div>
                <div class="form-group">
                        <label for="strAcademicYear">Term/Sem</label>
                        <h4><?php echo $classlist['strAcademicYear']; ?></h4>
                    </div>
                <div class="form-group">
                    <label for="intSubjectID">Subjects</label>
                    <select disabled class="form-control" name="intSubjectID" >
                        <?php foreach($subjects as $s): ?>
                            <option <?php echo ($classlist['intSubjectID'] == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID'] ?>"><?php echo $s['strCode']; ?></option> 
                        <?php endforeach; ?>
                    </select>
                </div>
                
                
                
               
                
    <hr />
                
                <input type="submit" value="update" class="btn btn-default  btn-flat">
            </form>
            </div>
        </div>
    </div>
</section>
</aside>