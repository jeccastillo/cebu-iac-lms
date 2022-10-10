<aside class="right-side">
<section class="content-header">
  <h1>
    Read Mail
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
    <li class="active">Mailbox</li>
  </ol>
</section>
<div class="content">
    <div class="box box-primary">
        <div class="box-header">
          <h3 class="box-title">Read Mail</h3>
            
            <div class="box-tools pull-right">
                <form id="forward-form" method="post" action="<?php echo base_url() ?>messages/forwardMessage">
                    <input type="text" class="form-control" name="user-message" id="user-message" />
                    <input type="hidden" value="<?php echo $item['intMessageID']; ?>" class="form-control" name="intMessageID" id="user-message" />
                    <input type="hidden" value="<?php echo $item['intFacultyIDSender']; ?>" class="form-control" name="intFacultyIDSender" id="sender" />
                    <button onclick="submit_forward()" class="btn btn-default btn-sm forward-message" rel="<?php echo $item['intMessageID']; ?>" data-toggle="tooltip" title="Forward"><i class="fa fa-share"></i> Forward</button>
                </form>
            </div>
            <div style="clear:both;"></div>
        </div><!-- /.box-header -->
        <input type="hidden" value="<?php echo $item['intFacultyIDSender']; ?>" class="form-control" name="intFacultyIDSender" id="sender" />
        <div class="box-body no-padding" >
          <div class="mailbox-read-info">
             <input type="hidden" id="messageID" value="<?php echo $item['intMessageID'] ?>" />
            <h3>Subject: <?php echo $item['strSubject']; ?></h3>
            <h5>From: <?php echo $item['strFirstname']." ".$item['strLastname']; ?> <span class="mailbox-read-time"><?php echo date("j M. Y h:i A",strtotime($item['dteDate']))." (".time_lapsed($item['dteDate']).")"; ?></span></h5>
          </div><!-- /.mailbox-read-info -->
            
          
          <div class="mailbox-read-message">
            <p>
                <?php echo $item['strMessage']; ?>
            </p>
          </div><!-- /.mailbox-read-message -->
          
        </div><!-- /.box-body -->
        <div class="box-footer">
        </div>
        </div><!-- /. box -->
        <?php foreach($replies as $reply): ?>
        <div class="box">
            <div class='box-header'>
                  <h6 class='box-title'>From: <?php echo $reply['strFirstname']." ".$reply['strLastname']; ?> <span class="mailbox-read-time"><?php echo date("j M. Y h:i A",strtotime($reply['dteReplied']))." (".time_lapsed($reply['dteReplied']).")";; ?></span></h6>
                  <!-- tools box -->
                  <div class="pull-right box-tools">
                    <?php if($reply['intFacultyID'] == $user['intID']): ?>
                    <button class="btn btn-default btn-sm delete-reply" name="<?php echo $reply['intFacultyID']; ?>" rel="<?php echo $reply['intReplyThreadID']; ?>" data-toggle="tooltip" title="Delete"><i class="fa fa-trash-o"></i></button>
                    <?php endif; ?>
                    <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                  </div><!-- /. tools -->
                </div><!-- /.box-header -->
            <div class="box-body no-padding">
              <div class="mailbox-read-info">
                <h5></h5>
              </div><!-- /.mailbox-read-info -->

              <div class="mailbox-read-message">
                <p>
                    <?php echo $reply['strReplyMessage']; ?>
                </p>
              </div><!-- /.mailbox-read-message -->

            </div><!-- /.box-body -->
        </div>
        <?php endforeach; ?>
      
        <div class='box'>
                <div class='box-header'>
                  <h3 class='box-title'>Reply</h3>
                  <!-- tools box -->
                  <div class="pull-right box-tools">
                    <button class="btn btn-default btn-sm" data-widget='collapse' data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
                  </div><!-- /. tools -->
                </div><!-- /.box-header -->
                <div class='box-body pad'>
                  <form>
                    <textarea id="message-box" class="textarea" name="reply-message" placeholder="Reply to thread" style="width: 100%; height: 200px; font-size: 14px; line-height: 18px; border: 1px solid #dddddd; padding: 10px;"></textarea>
                  </form>
                </div>
                <div class="box-footer">
                  <div class="pull-right">
                    <button id="reply-button" class="btn btn-default"><i class="fa fa-reply"></i> Reply</button>
                  </div>
                </div><!-- /.box-footer -->
              </div>
    </div>
            
</aside>
