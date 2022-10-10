<aside class="right-side">
    <section class="content-header">
        <h1>
            Messages
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Messages</a></li>
            <li class="active">Compose Message</li>
        </ol>
    </section>
    <div class="content">
        <div class="box">
            <form id="compose-form" method="post" action="<?php echo base_url() ?>messages/send_new_message">
            <div class="box-header">
                <h3 class="box-title">Compose Message</h3>
                    <h5>To:</h5>
                    <input placeholder="To" type="text" class="form-control" name="user-message" id="user-message" />
                    <input type="hidden" value="<?php echo $user['intID']; ?>" class="form-control" name="intFacultyIDSender" id="user-message" />


            </div><!-- /.box-header -->
            <div class="box-body">
                <h5>Subject:</h5>
                <input type="text" name="strSubject" class="form-control"  placeholder= "Subject" />
                <hr />
                <textarea id="message-box" class="textarea" name="strMessage" placeholder="Reply to thread" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
            </div><!-- /.box-body -->
            <div class="box-footer">
                <div class="pull-right">
                <button onclick="send_message()" class="btn btn-default btn-sm forward-message"  data-toggle="tooltip" title="Send Message"><i class="fa fa-share"></i> Send</button>
                    </div>
            </div>
        </div><!-- /.box -->
        </form>
        </div>
    </div>
</aside>