<aside class="right-side">
<section class="content-header">
                    <h1>
                        School Term
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Terms</a></li>
                        <li class="active">View All Terms</li>
                    </ol>
                </section>
    <div class="content">
        <div class="col-xs-12">
            <div class="alert alert-danger" style="display:none;">
                <i class="fa fa-ban"></i>
                <b>Alert!</b> Only admins can delete student records.
            </div>
                            <div class="box box-solid box-danger">
                                <div class="box-header">
                                    <h3 class="box-title">List of Academic Terms</h3>
                                    <div class="box-tools">
                                        
                                    </div>
                                </div><!-- /.box-header -->
                                <div class="box-body table-responsive">                                        
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <label for="cutoff">Start Date Registration Cut Off:</label>  
                                            <input type="date" value="<?php echo date("Y-m-d"); ?>" id="cutoff" class="form-control" />
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="cutoff">End Date for Registration Cut Off:</label>  
                                            <input type="date" value="<?php echo date("Y-m-d"); ?>" id="cutoffend" class="form-control" />
                                        </div>
                                    </div>
                                    <hr />
                                    <table id="ay-table" class="table">
                                        <thead><tr>
                                            <th>Term/Sem</th>
                                            <th>Year</th>                                            
                                            <th>Finalized</th>                                            
                                            <th>Type</th>
                                            <th>Actions</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach($academic_years as $ay): ?>
                                            <tr>
                                                
                                                <td><?php echo $ay['enumSem']; ?></td>
                                                <td><?php echo $ay['strYearStart']." - ".$ay['strYearEnd']; ?></td>                                                
                                                <td><?php echo $ay['enumFinalized']; ?></td>
                                                <td><?php echo $ay['term_student_type']; ?></td>                                                                                                
                                                <td>
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default">Actions</button>
                                                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                                        <span class="caret"></span>
                                                        <span class="sr-only">Toggle Dropdown</span>
                                                    </button>
                                                    <ul class="dropdown-menu" role="menu">
                                                        <li><a href="<?php echo base_url() ?>registrar/edit_ay/<?php echo $ay['intID']; ?>"><i class="fi-widget"></i> Edit</a></li>
                                  <li><a href="<?php echo base_url() ?>registrar/ay_viewer/<?php echo $ay['intID']; ?>"><i class="fi-results"></i> View</a></li>
                                    <li>
                                        <a href="#" class="cut-off-registration" rel="<?php echo $ay['intID']; ?>">Registration Cut-off</a>                        
                                    </li>
                                  <li> <a href="#" class="trash-sy-record" rel="<?php echo $ay['intID']; ?>"><i class="fi-trash"></i> Delete</a></li>
                                                    </ul>
                                                    
                                            </div>
                                        </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                    </tbody></table>
                                </div><!-- /.box-body -->
                            </div><!-- /.box -->
                        </div>
    </div>
</aside>