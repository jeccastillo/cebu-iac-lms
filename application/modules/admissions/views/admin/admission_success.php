<div class="container">
       <div class="content">
           <div class="header">
            <div class="box box-primary">   
              <img class="img-responsive" src="<?php echo $img_dir; ?>admission_header5.jpg" />
            </div>
<!--                <h3 class="main-title">APPLICATION FOR ADMISSION</h3>-->
            </div>
        <div class="box box-primary">
            <div class="box-header text-center">
                    <h3 class="box-title"><b>Admission Application Successfully Submitted!</b></h3>
            </div>

            <div class="box-body">
                <div class="row">
                    <div class="form-group col-sm-8 text-center col-sm-offset-2">
                        
                        <p>Your application has been submitted and will be evaluated.</p>
                        <p>Please take note of your name, application number and google mail address listed below:</p>
                        <table class="table">
                            <tr>
                                <td><p class="admission-info">Applicant's Name:</td>
                                <td>{appLName}, {appFName}, {appMName}</td>
                            </tr>
                            <tr>
                                <td><p class="admission-info">Application Number:</td>
                                <td>{appNumber}</td>
                            </tr>
                            <tr>
                                <td><p class="admission-info">Google Mail Address:</td>
                                <td>{email}</td>
                            </tr>
<!--
                            <tr>
                                <td><p class="admission-info">Examination Date:</td>
                                <td>{examDate}</td>
                            </tr>
-->
                        </table>
                        <p>We will send the link of your admission information in your registered gmail once your application has been evaluated.</p>
                    </div>
                </div>
            </div>

         </div>

    </div>
</div>
