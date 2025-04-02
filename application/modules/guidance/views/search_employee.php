<aside class="right-side">
<section class="content-header">
                    <h1>
                    Deficiencies
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i>Deficiencies</a></li>
                        <li class="active">Search Employee</li>
                    </ol>
                </section>
<div class="content">
    <div class="span10 box box-primary">
        <div class="box-header">
                <h3 class="box-title">Search Employee</h3>
                
        </div>
       
        <div class="box box-solid">
            <div class="box-body">                               
             <form id="advise-student" action="<?php echo base_url(); ?>clinic/guidance_records_employee" method="post" role="form">
                 <p>Search Employee</p>
                 <div class="row">
                     <div class="col-sm-6">
                        <input type="text" id="select-student-id" name="studentID" placeholder="Enter Name of Employee" class="form-control" />
                     </div>
                 </div>
                 <br />
                 <input class="btn btn-info btn-flat" type="submit" />
                 
            </form>
            </div>
        </div>
       
        </div>
</aside>