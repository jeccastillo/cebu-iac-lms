<aside class="right-side">
<section class="content-header">
                    <h1>
                    Sections
                    </h1>
                    <ol class="breadcrumb">
                        <li class="active">Generate Sections</li>
                    </ol>
                </section>
<section class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Generate Sections for <?php echo $curriculum['strName']; ?></h3>
        </div>
       
            
            <form action="<?php echo base_url(); ?>unity/submit_generate_class" method="post" role="form">
                
                 <div class="box-body">
                    
                    
                
                     <div class="row">
                         
                         <div class="form-group col-sm-4">
                            <label for="strSection">Year:</label>
                            <select type="text" name="year" class="form-control" id="year" >
                                <option value="1">1st</option> 
                                <option value="2">2nd</option> 
                                <option value="3">3rd</option> 
                                <option value="4">4th</option> 
                                <option value="5">5th</option> 
                            </select>
                         </div>
                         <div class="form-group col-sm-4">
                            <label for="num_sections">Number of Sections:</label>
                            <select type="text" name="num_sections" class="form-control" id="year" >
                                <option value="1">1</option> 
                                <option value="2">2</option> 
                                <option value="3">3</option> 
                                <option value="4">4</option> 
                                <option value="5">5</option>
                                <option value="6">6</option> 
                                <option value="7">7</option> 
                                <option value="8">8</option> 
                                <option value="9">9</option> 
                                <option value="10">10</option>
                                <option value="11">11</option> 
                                <option value="12">12</option> 
                                <option value="13">13</option> 
                                <option value="14">14</option> 
                                <option value="15">15</option>
                                <option value="16">16</option> 
                                <option value="17">17</option> 
                                <option value="18">18</option> 
                                <option value="19">19</option> 
                                <option value="20">20</option>
                            </select>
                         </div>
                    </div>
                     
                    <input type="hidden" value="<?php echo $curriculum['intID']; ?>" name="curriculum">
                    <div class="form-group">
                        <label for="strAcademicYear">Select Term:</label>
                        <select class="form-control" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option <?php echo ($selected_ay == $s['intID'])?'selected':''; ?> value="<?php echo $s['intID']; ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd']; ?></option>
                            <?php endforeach; ?>
                        </select>
                       <!--<select class="form-control" name="strAcademicYear">
                            <?php foreach($sy as $s): ?>
                                <option value="<?php echo $s['intID'] ?>"><?php echo $s['enumSem']." ".$term_type." ".$s['strYearStart']."-".$s['strYearEnd'];  ?></option>
                            <?php endforeach; ?>
                        </select>-->
                    </div>
                    
                <hr />
                <input type="submit" value="generate" class="btn btn-default  btn-flat">
            </form>
       
        </section>
</aside>
    
