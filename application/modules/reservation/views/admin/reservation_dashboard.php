<div class="content-wrapper" id="reservationDashboard">
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
        <!-- Loading Indicator -->
        <div v-if="loading" class="text-center">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
            <p>Loading reservations...</p>
        </div>

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
                                View All ({{ myReservations.length }})
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
                        <span class="info-box-number">{{ todaysReservations.length }}</span>
                    </div>
                </div>
            </div>
            
            <div v-if="isAdmin" class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box bg-red">
                    <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">PENDING APPROVAL</span>
                        <span class="info-box-number">{{ pendingReservations.length }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Reservations -->
        <div v-if="todaysReservations.length > 0" class="box box-primary">
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
                            <tr v-for="reservation in todaysReservations" :key="reservation.intReservationID">
                                <td>{{ formatTime(reservation.dteStartTime) }} - {{ formatTime(reservation.dteEndTime) }}</td>
                                <td>{{ reservation.strRoomCode }}</td>
                                <td>{{ reservation.strFirstname }} {{ reservation.strLastname }}</td>
                                <td>{{ reservation.strPurpose }}</td>
                                <td>
                                    <span class="label label-success">Approved</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pending Reservations (Admin/Registrar only) -->
        <div v-if="isAdmin && pendingReservations.length > 0" class="box box-warning">
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
                            <tr v-for="reservation in pendingReservations" :key="reservation.intReservationID">
                                <td>{{ formatDate(reservation.dteReservationDate) }}</td>
                                <td>{{ formatTime(reservation.dteStartTime) }} - {{ formatTime(reservation.dteEndTime) }}</td>
                                <td>{{ reservation.strRoomCode }}</td>
                                <td>{{ reservation.strFirstname }} {{ reservation.strLastname }}</td>
                                <td>{{ reservation.strPurpose }}</td>
                                <td>
                                    <button @click="approveReservation(reservation.intReservationID)" 
                                            class="btn btn-success btn-xs" 
                                            :disabled="processing">
                                        <i class="fa fa-check"></i> Approve
                                    </button>
                                    <button @click="rejectReservation(reservation.intReservationID)" 
                                            class="btn btn-danger btn-xs" 
                                            :disabled="processing">
                                        <i class="fa fa-times"></i> Reject
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- My Recent Reservations -->
        <div v-if="myReservations.length > 0" class="box box-info">
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
                            <tr v-for="reservation in recentReservations" :key="reservation.intReservationID">
                                <td>{{ formatDate(reservation.dteReservationDate) }}</td>
                                <td>{{ formatTime(reservation.dteStartTime) }} - {{ formatTime(reservation.dteEndTime) }}</td>
                                <td>{{ reservation.strRoomCode }}</td>
                                <td>{{ reservation.strPurpose }}</td>
                                <td>
                                    <span :class="getStatusClass(reservation.enumStatus)">
                                        {{ reservation.enumStatus.charAt(0).toUpperCase() + reservation.enumStatus.slice(1) }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- No Data Message -->
        <div v-if="!loading && todaysReservations.length === 0 && myReservations.length === 0" class="box box-default">
            <div class="box-body text-center">
                <i class="fa fa-calendar fa-3x text-muted"></i>
                <h4>No Reservations Found</h4>
                <p>You haven't made any reservations yet. Click the "Reserve Room" button to get started.</p>
                <a href="<?php echo base_url(); ?>reservation/add_reservation" class="btn btn-primary">
                    <i class="fa fa-plus"></i> Make Your First Reservation
                </a>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
<script>
new Vue({
    el: '#reservationDashboard',
    data: {
        loading: true,
        processing: false,
        todaysReservations: <?php echo json_encode($todays_reservations ?? []); ?>,
        pendingReservations: <?php echo json_encode($pending_reservations ?? []); ?>,
        myReservations: <?php echo json_encode($my_reservations ?? []); ?>,
        isAdmin: <?php echo json_encode(isset($pending_reservations)); ?>,
        baseUrl: '<?php echo base_url(); ?>'
    },
    computed: {
        recentReservations() {
            return this.myReservations.slice(0, 5);
        }
    },
    mounted() {
        this.loading = false;
    },
    methods: {
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'short', 
                day: 'numeric' 
            });
        },
        formatTime(timeString) {
            const time = new Date('2000-01-01 ' + timeString);
            return time.toLocaleTimeString('en-US', { 
                hour: 'numeric', 
                minute: '2-digit',
                hour12: true 
            });
        },
        getStatusClass(status) {
            const classes = {
                'approved': 'label label-success',
                'pending': 'label label-warning',
                'rejected': 'label label-danger',
                'cancelled': 'label label-default'
            };
            return classes[status] || 'label label-default';
        },
        async approveReservation(reservationId) {
            if (!confirm('Are you sure you want to approve this reservation?')) {
                return;
            }
            
            this.processing = true;
            try {
                const response = await fetch(this.baseUrl + 'reservation/approve_reservation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: reservationId,
                        status: 'approved'
                    })
                });
                
                const data = await response.json();
                if (data.message === 'success') {
                    // Remove from pending list
                    this.pendingReservations = this.pendingReservations.filter(
                        r => r.intReservationID !== reservationId
                    );
                    this.showAlert('Reservation approved successfully!', 'success');
                } else {
                    this.showAlert('Error approving reservation', 'error');
                }
            } catch (error) {
                this.showAlert('Error approving reservation', 'error');
            } finally {
                this.processing = false;
            }
        },
        async rejectReservation(reservationId) {
            const remarks = prompt('Please enter rejection reason (optional):');
            if (!confirm('Are you sure you want to reject this reservation?')) {
                return;
            }
            
            this.processing = true;
            try {
                const response = await fetch(this.baseUrl + 'reservation/approve_reservation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: reservationId,
                        status: 'rejected',
                        remarks: remarks || ''
                    })
                });
                
                const data = await response.json();
                if (data.message === 'success') {
                    // Remove from pending list
                    this.pendingReservations = this.pendingReservations.filter(
                        r => r.intReservationID !== reservationId
                    );
                    this.showAlert('Reservation rejected successfully!', 'success');
                } else {
                    this.showAlert('Error rejecting reservation', 'error');
                }
            } catch (error) {
                this.showAlert('Error rejecting reservation', 'error');
            } finally {
                this.processing = false;
            }
        },
        showAlert(message, type) {
            // Simple alert for now - could be enhanced with a toast notification system
            if (type === 'success') {
                alert('✓ ' + message);
            } else {
                alert('✗ ' + message);
            }
        }
    }
});
</script>
