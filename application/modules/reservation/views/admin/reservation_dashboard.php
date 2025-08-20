<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Room Reservations
            <small>Dashboard</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>unity/faculty_dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Room Reservations</li>
        </ol>
    </section>

    <section class="content">
        <!-- Quick Actions Row -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-green">
                    <span class="info-box-icon"><i class="fa fa-plus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">NEW RESERVATION</span>
                        <span class="info-box-number">
                            <a href="<?php echo base_url(); ?>reservation/add_reservation" class="text-white">
                                Reserve Room
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-blue">
                    <span class="info-box-icon"><i class="fa fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">MY RESERVATIONS</span>
                        <span class="info-box-number">
                            <a href="<?php echo base_url(); ?>reservation/view_reservations" class="text-white">
                                View All
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-yellow">
                    <span class="info-box-icon"><i class="fa fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">TODAY'S RESERVATIONS</span>
                        <span class="info-box-number"><?php echo count($todays_reservations); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if(isset($pending_reservations)): ?>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">PENDING APPROVAL</span>
                        <span class="info-box-number"><?php echo count($pending_reservations); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Today's Reservations -->
        <?php if(!empty($todays_reservations)): ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Today's Reservations</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Reserved By</th>
                                <th>Purpose</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($todays_reservations as $reservation): ?>
                            <tr>
                                <td><?php echo date('g:i A', strtotime($reservation['dteStartTime'])); ?> - <?php echo date('g:i A', strtotime($reservation['dteEndTime'])); ?></td>
                                <td><?php echo $reservation['strRoomCode']; ?></td>
                                <td><?php echo $reservation['strFirstname'] . ' ' . $reservation['strLastname']; ?></td>
                                <td><?php echo $reservation['strPurpose']; ?></td>
                                <td>
                                    <span class="label label-success">Approved</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Pending Reservations (Admin/Registrar only) -->
        <?php if(isset($pending_reservations) && !empty($pending_reservations)): ?>
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Pending Reservations</h3>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Requested By</th>
                                <th>Purpose</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($pending_reservations as $reservation): ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($reservation['dteReservationDate'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($reservation['dteStartTime'])); ?> - <?php echo date('g:i A', strtotime($reservation['dteEndTime'])); ?></td>
                                <td><?php echo $reservation['strRoomCode']; ?></td>
                                <td><?php echo $reservation['strFirstname'] . ' ' . $reservation['strLastname']; ?></td>
                                <td><?php echo $reservation['strPurpose']; ?></td>
                                <td>
                                    <button class="btn btn-success btn-xs approve-reservation" data-id="<?php echo $reservation['intReservationID']; ?>">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-xs reject-reservation" data-id="<?php echo $reservation['intReservationID']; ?>">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- My Recent Reservations -->
        <?php if(!empty($my_reservations)): ?>
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">My Recent Reservations</h3>
                <div class="box-tools pull-right">
                    <a href="<?php echo base_url(); ?>reservation/view_reservations" class="btn btn-box-tool">
                        <i class="fa fa-external-link"></i> View All
                    </a>
                </div>
            </div>
            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Purpose</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recent_reservations = array_slice($my_reservations, 0, 5);
                            foreach($recent_reservations as $reservation): 
                            ?>
                            <tr>
                                <td><?php echo date('M j, Y', strtotime($reservation['dteReservationDate'])); ?></td>
                                <td><?php echo date('g:i A', strtotime($reservation['dteStartTime'])); ?> - <?php echo date('g:i A', strtotime($reservation['dteEndTime'])); ?></td>
                                <td><?php echo $reservation['strRoomCode']; ?></td>
                                <td><?php echo $reservation['strPurpose']; ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    switch($reservation['enumStatus']) {
                                        case 'approved':
                                            $status_class = 'label-success';
                                            break;
                                        case 'pending':
                                            $status_class = 'label-warning';
                                            break;
                                        case 'rejected':
                                            $status_class = 'label-danger';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'label-default';
                                            break;
                                    }
                                    ?>
                                    <span class="label <?php echo $status_class; ?>"><?php echo ucfirst($reservation['enumStatus']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>
</div>

<script>
$(document).ready(function() {
    // Approve reservation
    $('.approve-reservation').click(function() {
        var reservationId = $(this).data('id');
        if(confirm('Are you sure you want to approve this reservation?')) {
            $.post('<?php echo base_url(); ?>reservation/approve_reservation', {
                id: reservationId,
                status: 'approved'
            }, function(data) {
                if(data.message == 'success') {
                    location.reload();
                } else {
                    alert('Error approving reservation');
                }
            }, 'json');
        }
    });

    // Reject reservation
    $('.reject-reservation').click(function() {
        var reservationId = $(this).data('id');
        var remarks = prompt('Please enter rejection reason (optional):');
        if(confirm('Are you sure you want to reject this reservation?')) {
            $.post('<?php echo base_url(); ?>reservation/approve_reservation', {
                id: reservationId,
                status: 'rejected',
                remarks: remarks
            }, function(data) {
                if(data.message == 'success') {
                    location.reload();
                } else {
                    alert('Error rejecting reservation');
                }
            }, 'json');
        }
    });
});
</script>
