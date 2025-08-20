<div class="content-wrapper" id="addReservationApp">
    <section class="content-header">
        <h1>
            Add Room Reservation
            <small>Reserve a room</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>unity/faculty_dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo base_url(); ?>reservation">Room Reservations</a></li>
            <li class="active">Add Reservation</li>
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
                <h3 class="box-title">Reservation Details</h3>
            </div>
            
            <form @submit.prevent="submitReservation" id="reservationForm">
                <div class="box-body">
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
                                    placeholder="Enter any additional details or requirements">
                                </textarea>
                            </div>
                        </div>
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
                </div>

                <div class="box-footer">
                    <button 
                        type="button" 
                        @click="checkAvailability" 
                        class="btn btn-info"
                        :disabled="checking || !canCheckAvailability">
                        <i :class="checking ? 'fa fa-spinner fa-spin' : 'fa fa-search'"></i>
                        {{ checking ? 'Checking...' : 'Check Availability' }}
                    </button>
                    <button 
                        type="submit" 
                        class="btn btn-primary" 
                        :disabled="submitting || !canSubmit">
                        <i :class="submitting ? 'fa fa-spinner fa-spin' : 'fa fa-save'"></i>
                        {{ submitting ? 'Submitting...' : 'Submit Reservation' }}
                    </button>
                    <a href="<?php echo base_url(); ?>reservation" class="btn btn-default">
                        <i class="fa fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
<script>
new Vue({
    el: '#addReservationApp',
    data: {
        reservation: {
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
        canCheckAvailability() {
            return this.reservation.dteReservationDate && 
                   this.reservation.dteStartTime && 
                   this.reservation.dteEndTime && 
                   this.reservation.intRoomID;
        },
        canSubmit() {
            return this.canCheckAvailability && 
                   this.reservation.strPurpose && 
                   this.availabilityCheck.available;
        }
    },
    methods: {
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
                        intRoomID: this.reservation.intRoomID
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
            // Auto-check availability when all required fields are filled
            if (this.canCheckAvailability) {
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
                this.showError('Please ensure all fields are filled and room is available.');
                return;
            }

            this.submitting = true;
            this.errors = {};

            try {
                const formData = new FormData();
                Object.keys(this.reservation).forEach(key => {
                    formData.append(key, this.reservation[key]);
                });

                const response = await fetch(this.baseUrl + 'reservation/submit_reservation', {
                    method: 'POST',
                    body: formData
                });

                if (response.ok) {
                    // Redirect to dashboard on success
                    window.location.href = this.baseUrl + 'reservation';
                } else {
                    this.showError('Error submitting reservation. Please try again.');
                }

            } catch (error) {
                this.showError('Error submitting reservation. Please try again.');
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
    },
    mounted() {
        // Set default date to today
        if (!this.reservation.dteReservationDate) {
            this.reservation.dteReservationDate = this.minDate;
        }
    }
});
</script>
