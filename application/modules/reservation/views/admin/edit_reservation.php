<div class="content-wrapper" id="editReservationApp">
    <section class="content-header">
        <h1>
            Edit Room Reservation
            <small>Modify reservation details</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>unity/faculty_dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo base_url(); ?>reservation">Room Reservations</a></li>
            <li class="active">Edit Reservation</li>
        </ol>
    </section>

    <section class="content">
        <!-- Flash Messages -->
        <div v-if="flashMessage.show" :class="'alert alert-' + flashMessage.type + ' alert-dismissible'">
            <button type="button" class="close" @click="flashMessage.show = false">&times;</button>
            <h4><i :class="flashMessage.icon"></i> {{ flashMessage.title }}</h4>
            {{ flashMessage.message }}
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Edit Reservation Details</h3>
                <div class="box-tools pull-right">
                    <span :class="getStatusClass(originalReservation.enumStatus)" v-if="originalReservation">
                        Current Status: {{ originalReservation.enumStatus.charAt(0).toUpperCase() + originalReservation.enumStatus.slice(1) }}
                    </span>
                </div>
            </div>
            
            <form @submit.prevent="submitReservation" id="editReservationForm">
                <div class="box-body">
                    <!-- Original Reservation Info -->
                    <div v-if="originalReservation" class="alert alert-info">
                        <h4><i class="fa fa-info-circle"></i> Original Reservation</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Date:</strong> {{ formatDate(originalReservation.dteReservationDate) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Time:</strong> {{ formatTime(originalReservation.dteStartTime) }} - {{ formatTime(originalReservation.dteEndTime) }}
                            </div>
                            <div class="col-md-3">
                                <strong>Room:</strong> {{ originalReservation.strRoomCode }}
                            </div>
                            <div class="col-md-3">
                                <strong>Purpose:</strong> {{ originalReservation.strPurpose }}
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" :class="{ 'has-error': errors.dteReservationDate }">
                                <label for="dteReservationDate">Reservation Date *</label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="dteReservationDate" 
                                    v-model="reservation.dteReservationDate" 
                                    :min="minDate"
                                    @change="checkAvailabilityAuto"
                                    :disabled="!canEdit"
                                    required>
                                <span v-if="errors.dteReservationDate" class="help-block">{{ errors.dteReservationDate }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group" :class="{ 'has-error': errors.dteStartTime }">
                                <label for="dteStartTime">Start Time *</label>
                                <input 
                                    type="time" 
                                    class="form-control" 
                                    id="dteStartTime" 
                                    v-model="reservation.dteStartTime"
                                    @change="checkAvailabilityAuto"
                                    :disabled="!canEdit"
                                    required>
                                <span v-if="errors.dteStartTime" class="help-block">{{ errors.dteStartTime }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group" :class="{ 'has-error': errors.dteEndTime }">
                                <label for="dteEndTime">End Time *</label>
                                <input 
                                    type="time" 
                                    class="form-control" 
                                    id="dteEndTime" 
                                    v-model="reservation.dteEndTime"
                                    @change="checkAvailabilityAuto"
                                    :disabled="!canEdit"
                                    required>
                                <span v-if="errors.dteEndTime" class="help-block">{{ errors.dteEndTime }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group" :class="{ 'has-error': errors.intRoomID }">
                                <label for="intRoomID">Room *</label>
                                <select 
                                    class="form-control" 
                                    id="intRoomID" 
                                    v-model="reservation.intRoomID"
                                    @change="checkAvailabilityAuto"
                                    :disabled="!canEdit"
                                    required>
                                    <option value="">Select Room</option>
                                    <option v-for="room in classrooms" :key="room.intID" :value="room.intID">
                                        {{ room.strRoomCode }} - {{ room.strDescription }}
                                    </option>
                                </select>
                                <span v-if="errors.intRoomID" class="help-block">{{ errors.intRoomID }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group" :class="{ 'has-error': errors.strPurpose }">
                                <label for="strPurpose">Purpose *</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="strPurpose" 
                                    v-model="reservation.strPurpose"
                                    placeholder="Enter purpose of reservation"
                                    :disabled="!canEdit"
                                    required>
                                <span v-if="errors.strPurpose" class="help-block">{{ errors.strPurpose }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="strDescription">Additional Details</label>
                                <textarea 
                                    class="form-control" 
                                    id="strDescription" 
                                    v-model="reservation.strDescription"
                                    rows="3" 
                                    placeholder="Enter any additional details or requirements"
                                    :disabled="!canEdit">
                                </textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Changes Summary -->
                    <div v-if="hasChanges" class="alert alert-warning">
                        <h4><i class="fa fa-exclamation-triangle"></i> Changes Detected</h4>
                        <ul>
                            <li v-for="change in changesSummary" :key="change.field">
                                <strong>{{ change.label }}:</strong> 
                                <span class="text-muted">{{ change.from }}</span> 
                                <i class="fa fa-arrow-right"></i> 
                                <span class="text-primary">{{ change.to }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Availability Check Results -->
                    <div v-if="availabilityCheck.show" class="row">
                        <div class="col-md-12">
                            <div :class="'alert ' + (availabilityCheck.available ? 'alert-success' : 'alert-danger')">
                                <h4>
                                    <i :class="availabilityCheck.available ? 'fa fa-check' : 'fa fa-times'"></i>
                                    {{ availabilityCheck.available ? 'Room Available' : 'Room Not Available' }}
                                </h4>
                                <div v-if="!availabilityCheck.available && availabilityCheck.conflicts.length > 0">
                                    <p><strong>Conflicts detected:</strong></p>
                                    <ul>
                                        <li v-for="conflict in availabilityCheck.conflicts" :key="conflict.type + conflict.details.intID">
                                            {{ conflict.message }}
                                        </li>
                                    </ul>
                                </div>
                                <p v-else-if="availabilityCheck.available">
                                    The selected room is available for the specified time slot.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Available Rooms Suggestion -->
                    <div v-if="availableRooms.length > 0 && !availabilityCheck.available" class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <h4><i class="fa fa-lightbulb-o"></i> Alternative Rooms Available</h4>
                                <p>The following rooms are available for your selected time:</p>
                                <div class="row">
                                    <div v-for="room in availableRooms" :key="room.intID" class="col-md-4">
                                        <button 
                                            type="button" 
                                            class="btn btn-info btn-block"
                                            @click="selectAlternativeRoom(room)">
                                            {{ room.strRoomCode }} - {{ room.strDescription }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Read-only message -->
                    <div v-if="!canEdit" class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        This reservation cannot be edited because it has been {{ originalReservation.enumStatus }}.
                        Only pending reservations can be modified.
                    </div>
                </div>

                <div class="box-footer">
                    <button 
                        v-if="canEdit"
                        type="button" 
                        @click="checkAvailability" 
                        class="btn btn-info"
                        :disabled="checking || !canCheckAvailability">
                        <i :class="checking ? 'fa fa-spinner fa-spin' : 'fa fa-search'"></i>
                        {{ checking ? 'Checking...' : 'Check Availability' }}
                    </button>
                    <button 
                        v-if="canEdit"
                        type="submit" 
                        class="btn btn-primary" 
                        :disabled="submitting || !canSubmit">
                        <i :class="submitting ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i>
                        {{ submitting ? 'Updating...' : 'Update Reservation' }}
                    </button>
                    <a href="<?php echo base_url(); ?>reservation/view_reservations" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to Reservations
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
<script>
new Vue({
    el: '#editReservationApp',
    data: {
        originalReservation: <?php echo json_encode($reservation ?? null); ?>,
        reservation: {
            intReservationID: '',
            dteReservationDate: '',
            dteStartTime: '',
            dteEndTime: '',
            intRoomID: '',
            strPurpose: '',
            strDescription: ''
        },
        classrooms: <?php echo json_encode($classrooms ?? []); ?>,
        errors: {},
        checking: false,
        submitting: false,
        availabilityCheck: {
            show: false,
            available: false,
            conflicts: []
        },
        availableRooms: [],
        flashMessage: {
            show: <?php echo $this->session->flashdata('error') || $this->session->flashdata('success') ? 'true' : 'false'; ?>,
            type: '<?php echo $this->session->flashdata('error') ? 'danger' : 'success'; ?>',
            title: '<?php echo $this->session->flashdata('error') ? 'Error!' : 'Success!'; ?>',
            message: '<?php echo $this->session->flashdata('error') ?: $this->session->flashdata('success'); ?>',
            icon: '<?php echo $this->session->flashdata('error') ? 'fa fa-ban' : 'fa fa-check'; ?>'
        },
        baseUrl: '<?php echo base_url(); ?>'
    },
    computed: {
        minDate() {
            return new Date().toISOString().split('T')[0];
        },
        canEdit() {
            return this.originalReservation && this.originalReservation.enumStatus === 'pending';
        },
        canCheckAvailability() {
            return this.reservation.dteReservationDate && 
                   this.reservation.dteStartTime && 
                   this.reservation.dteEndTime && 
                   this.reservation.intRoomID;
        },
        canSubmit() {
            return this.canEdit &&
                   this.canCheckAvailability && 
                   this.reservation.strPurpose && 
                   this.hasChanges &&
                   (this.availabilityCheck.available || !this.availabilityCheck.show);
        },
        hasChanges() {
            if (!this.originalReservation) return false;
            
            return this.reservation.dteReservationDate !== this.originalReservation.dteReservationDate ||
                   this.reservation.dteStartTime !== this.originalReservation.dteStartTime ||
                   this.reservation.dteEndTime !== this.originalReservation.dteEndTime ||
                   this.reservation.intRoomID !== this.originalReservation.intRoomID ||
                   this.reservation.strPurpose !== this.originalReservation.strPurpose ||
                   this.reservation.strDescription !== (this.originalReservation.strDescription || '');
        },
        changesSummary() {
            if (!this.originalReservation) return [];
            
            const changes = [];
            
            if (this.reservation.dteReservationDate !== this.originalReservation.dteReservationDate) {
                changes.push({
                    field: 'date',
                    label: 'Date',
                    from: this.formatDate(this.originalReservation.dteReservationDate),
                    to: this.formatDate(this.reservation.dteReservationDate)
                });
            }
            
            if (this.reservation.dteStartTime !== this.originalReservation.dteStartTime) {
                changes.push({
                    field: 'startTime',
                    label: 'Start Time',
                    from: this.formatTime(this.originalReservation.dteStartTime),
                    to: this.formatTime(this.reservation.dteStartTime)
                });
            }
            
            if (this.reservation.dteEndTime !== this.originalReservation.dteEndTime) {
                changes.push({
                    field: 'endTime',
                    label: 'End Time',
                    from: this.formatTime(this.originalReservation.dteEndTime),
                    to: this.formatTime(this.reservation.dteEndTime)
                });
            }
            
            if (this.reservation.intRoomID !== this.originalReservation.intRoomID) {
                const originalRoom = this.classrooms.find(r => r.intID == this.originalReservation.intRoomID);
                const newRoom = this.classrooms.find(r => r.intID == this.reservation.intRoomID);
                changes.push({
                    field: 'room',
                    label: 'Room',
                    from: originalRoom ? originalRoom.strRoomCode : 'Unknown',
                    to: newRoom ? newRoom.strRoomCode : 'Unknown'
                });
            }
            
            if (this.reservation.strPurpose !== this.originalReservation.strPurpose) {
                changes.push({
                    field: 'purpose',
                    label: 'Purpose',
                    from: this.originalReservation.strPurpose,
                    to: this.reservation.strPurpose
                });
            }
            
            return changes;
        }
    },
    mounted() {
        if (this.originalReservation) {
            // Copy original data to editable form
            this.reservation = {
                intReservationID: this.originalReservation.intReservationID,
                dteReservationDate: this.originalReservation.dteReservationDate,
                dteStartTime: this.originalReservation.dteStartTime,
                dteEndTime: this.originalReservation.dteEndTime,
                intRoomID: this.originalReservation.intRoomID,
                strPurpose: this.originalReservation.strPurpose,
                strDescription: this.originalReservation.strDescription || ''
            };
        }
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
        async checkAvailability() {
            if (!this.canCheckAvailability) {
                this.showError('Please fill in all required fields before checking availability.');
                return;
            }

            if (!this.validateTimeRange()) {
                return;
            }

            this.checking = true;
            this.errors = {};

            try {
                const response = await fetch(this.baseUrl + 'reservation/check_availability', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        dteReservationDate: this.reservation.dteReservationDate,
                        dteStartTime: this.reservation.dteStartTime,
                        dteEndTime: this.reservation.dteEndTime,
                        intRoomID: this.reservation.intRoomID,
                        excludeReservationId: this.reservation.intReservationID
                    })
                });

                const data = await response.json();
                
                this.availabilityCheck = {
                    show: true,
                    available: data.available,
                    conflicts: data.conflicts || []
                };

                // If not available, get alternative rooms
                if (!data.available) {
                    await this.getAvailableRooms();
                }

            } catch (error) {
                this.showError('Error checking availability. Please try again.');
            } finally {
                this.checking = false;
            }
        },
        async checkAvailabilityAuto() {
            // Auto-check availability when all required fields are filled and there are changes
            if (this.canCheckAvailability && this.hasChanges) {
                await this.checkAvailability();
            } else {
                this.availabilityCheck.show = false;
                this.availableRooms = [];
            }
        },
        async getAvailableRooms() {
            try {
                const response = await fetch(this.baseUrl + 'reservation/get_available_rooms', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        date: this.reservation.dteReservationDate,
                        start_time: this.reservation.dteStartTime,
                        end_time: this.reservation.dteEndTime
                    })
                });

                this.availableRooms = await response.json();
            } catch (error) {
                console.error('Error getting available rooms:', error);
            }
        },
        selectAlternativeRoom(room) {
            this.reservation.intRoomID = room.intID;
            this.checkAvailability();
        },
        validateTimeRange() {
            if (this.reservation.dteStartTime >= this.reservation.dteEndTime) {
                this.errors.dteEndTime = 'End time must be after start time';
                return false;
            }

            // Check if reservation is at least 30 minutes
            const start = new Date('2000-01-01 ' + this.reservation.dteStartTime);
            const end = new Date('2000-01-01 ' + this.reservation.dteEndTime);
            const diffMinutes = (end - start) / (1000 * 60);

            if (diffMinutes < 30) {
                this.errors.dteEndTime = 'Reservation must be at least 30 minutes long';
                return false;
            }

            return true;
        },
        async submitReservation() {
            if (!this.canSubmit) {
                this.showError('Please ensure all fields are filled and there are changes to save.');
                return;
            }

            this.submitting = true;
            this.errors = {};

            try {
                const formData = new FormData();
                Object.keys(this.reservation).forEach(key => {
                    formData.append(key, this.reservation[key]);
                });

                const response = await fetch(this.baseUrl + 'reservation/submit_edit_reservation', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    // Redirect to reservations list on success
                    window.location.href = this.baseUrl + 'reservation/view_reservations';
                } else {
                    this.showError('Error updating reservation. Please try again.');
                }

            } catch (error) {
                this.showError('Error updating reservation. Please try again.');
            } finally {
                this.submitting = false;
            }
        },
        showError(message) {
            this.flashMessage = {
                show: true,
                type: 'danger',
                title: 'Error!',
                message: message,
                icon: 'fa fa-ban'
            };
        },
        showSuccess(message) {
            this.flashMessage = {
                show: true,
                type: 'success',
                title: 'Success!',
                message: message,
                icon: 'fa fa-check'
            };
        }
    }
});
</script>

<style>
.text-muted {
    text-decoration: line-through;
}

.text-primary {
    font-weight: bold;
}

.fa-arrow-right {
    margin: 0 5px;
    color: #666;
}
</style>
