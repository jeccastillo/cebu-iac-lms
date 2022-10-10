<aside class="right-side">
<section class="content-header">
                    <h1>
                        Messages
                        <small></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="#"><i class="fa fa-dashboard"></i> Messages</a></li>
                        <li class="active">Inbox</li>
                    </ol>
                </section>
    <div class="content">
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">Outbox</h3> &nbsp;
                

            </div><!-- /.box-header -->
            <hr />
            <div class="box-body no-padding">
                  <div class="mailbox-controls">
                    <!-- Check all button -->
                    
                    <div class="btn-group">
                        <button class="btn btn-default btn-sm checkbox-toggle"><i class="fa fa-square-o"></i></button>
                        <button type="button" class="btn btn-default btn-sm">With Selected</button>
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="#" class="delete-message"><i class="fa fa-trash-o"></i> Send to Trash</a></li>
                            <li><a href="#" class="mark-as-read"><i class="fa fa-check"></i> Mark as Read</a></li>
                            <li><a href="#" class="mark-as-unread"><i class="fa fa-check-square-o"></i> Mark as Unread</a></li>
                        </ul>
                    </div>
                    
                  </div>
            </div>
            <hr />
            <div class="box-body  table-responsive">
               <table id="messages_table" class="table table-hover">
                    <thead style="display:none;"><tr><th>id</th><th>Sender</th><th>Subject</th><th>Date</th><th>is Read</th><th>Actions</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div><!-- /.box-body -->
        </div><!-- /.box -->
    </div>
</aside>