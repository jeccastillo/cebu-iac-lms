<aside class="right-side">
<section class="content-header">
                    <h1>
                        View Grading Extension
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="<?php echo base_url().'registrar/edit_ay/'.$item['syid']; ?>"><i class="fa fa-dashboard"></i> Term</a></li>
                        <li class="active">View Grading Extension</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
            <h3 class="box-title">View Grading Extension</h3>
        </div>
        <div class="box-body">
            <div class="row">   
                <div class="col-md-6">
                    Period: <?php echo $item['type']; ?>
                </div>
                <div class="col-md-6">
                    End of Extension: <?php echo date("M j,Y", strtotime($item['date'])); ?>
                </div>
            </div>            
            <hr />
            <form  action="<?php echo base_url(); ?>registrar/add_selected" method="post" role="form">
                <input type="hidden" name="id"  id="id" value="<?php echo $item['id']; ?>">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Subject/Section</th>
                                        <th>Faculty</th>                                                                                
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach($selected_faculty as $item): 
                                    $section = $item['strClassName'].$item['year'].$item['strSection']." ".$item['sub_section'];
                                    ?>
                                    <tr>
                                        <td><?php echo $item['strCode']." ".$section; ?></td>
                                        <td><?php echo $item['strLastname']." ".$item['strFirstname']; ?></td>
                                        <td><button rel="<?php echo $item['extnsion_faculty']; ?>" class="btn-danger btn delete-selected-faculty">Remove</btn>
                                    </tr>        
                                <?php endforeach; ?>
                                </tbody>
                            </table>                                
                        </div>
                        <div class="col-md-6">                        
                            <label for="classlist">Select Section to Extend</label>                                           
                            <select required name="classlist[]" multiple class="form-control" style="height: 300px;">                    
                            <?php foreach($non_selected_faculty as $item):
                                $section = $item['strClassName'].$item['year'].$item['strSection']." ".$item['sub_section'];
                                ?>
                                <option value="<?php echo $item['classlistID']; ?>"><?php echo $item['strCode']." ".$section." - ".$item['strLastname']." ".$item['strFirstname']; ?></option>
                            <?php endforeach; ?>
                            </select>       
                            <hr />             
                            <input type="submit" value="<< add faculty" class="btn btn-default btn-flat btn-lg">
                        </div>
                    </div>                
                    
                </div>
            </form>
        </div>
</aside>