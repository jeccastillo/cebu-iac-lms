<div class="content-wrapper" id="viewReservationsApp">
    <section class="content-header">
        <h1>
            View Reservations
            <small>Manage room reservations</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>unity/faculty_dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="<?php echo base_url(); ?>reservation">Room Reservations</a></li>
            <li class="active">View Reservations</li>
        </ol>
    </section>

    <section class="content">
        <!-- Filters -->
        <div class="box box-default collapsed-box">
            <div class="box-header with-border">
                <h3 class="box-title">Filters</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse">
                        <i class="fa fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Status</label>
                            <select v-model="filters.status" @change="applyFilters" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date From</label>
                            <input type="date" v-model="filters.dateFrom" @change="applyFilters" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Date To</label>
                            <input type="date" v-model="filters.dateTo" @change="applyFilters" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Search</label>
                            <input type="text" v-model="filters.search" @input="applyFilters" placeholder="Search purpose, room..." class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">All Reservations ({{ filteredReservations.length }})</h3>
                <div class="box-tools pull-right">
                    <a href="<?php echo base_url(); ?>reservation/add_reservation" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add New Reservation
                    </a>
                </div>
            </div>
            <div class="box-body">
                <!-- Loading indicator -->
                <div v-if="loading" class="text-center">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p>Loading reservations...</p>
                </div>

                <!-- Reservations table -->
                <div v-else class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th @click="sortBy('dteReservationDate')" class="sortable">
                                    Date 
                                    <i :class="getSortIcon('dteReservationDate')"></i>
                                </th>
                                <th @click="sortBy('dteStartTime')" class="sortable">
                                    Time 
                                    <i :class="getSortIcon('dteStartTime')"></i>
                                </th>
                                <th @click="sortBy('strRoomCode')" class="sortable">
                                    Room 
                                    <i :class="getSortIcon('strRoomCode')"></i>
                                </th>
                                <th @click="sortBy('strPurpose')" class="sortable">
                                    Purpose 
                                    <i :class="getSortIcon('strPurpose')"></i>
                                </th>
                                <th @click="sortBy('strFirstname')" class="sortable">
                                    Requested By 
                                    <i :class="getSortIcon('strFirstname')"></i>
                                </th>
                                <th @click="sortBy('enumStatus')" class="sortable">
                                    Status 
                                    <i :class="getSortIcon('enumStatus')"></i>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="filteredReservations.length === 0">
                                <td colspan="7" class="text-center">
                                    <i class="fa fa-calendar fa-2x text-muted"></i>
                                    <p>No reservations found matching your criteria.</p>
                                </td>
                            </tr>
                            <tr v-for="reservation in paginatedReservations" :key="reservation.intReservationID">
                                <td>{{ formatDate(reservation.dteReservationDate) }}</td>
                                <td>{{ formatTime(reservation.dteStartTime) }} - {{ formatTime(reservation.dteEndTime) }}</td>
                                <td>{{ reservation.strRoomCode }}</td>
                                <td>{{ reservation.strPurpose }}</td>
                                <td>{{ reservation.strFirstname }} {{ reservation.strLastname }}</td>
                                <td>
                                    <span :class="getStatusClass(reservation.enumStatus)">
                                        {{ reservation.enumStatus.charAt(0).toUpperCase() + reservation.enumStatus.slice(1) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown">
                                            Actions <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="#" @click="viewReservation(reservation)"><i class="fa fa-eye"></i> View Details</a></li>
                                            <li v-if="canEdit(reservation)">
                                                <a :href="baseUrl + 'reservation/edit_reservation/' + reservation.intReservationID">
                                                    <i class="fa fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li v-if="isAdmin && reservation.enumStatus === 'pending'">
                                                <a href="#" @click="approveReservation(reservation.intReservationID)">
                                                    <i class="fa fa-check text-green"></i> Approve
                                                </a>
                                            </li>
                                            <li v-if="isAdmin && reservation.enumStatus === 'pending'">
                                                <a href="#" @click="rejectReservation(reservation.intReservationID)">
                                                    <i class="fa fa-times text-red"></i> Reject
                                                </a>
                                            </li>
                                            <li class="divider" v-if="canDelete(reservation)"></li>
                                            <li v-if="canDelete(reservation)">
                                                <a href="#" @click="deleteReservation(reservation.intReservationID)" class="text-red">
                                                    <i class="fa fa-trash"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="totalPages > 1" class="text-center">
                    <nav>
                        <ul class="pagination">
                            <li :class="{ disabled: currentPage === 1 }">
                                <a href="#" @click.prevent="changePage(currentPage - 1)">
                                    <span>&laquo;</span>
                                </a>
                            </li>
                            <li v-for="page in visiblePages" :key="page" :class="{ active: page === currentPage }">
                                <a href="#" @click.prevent="changePage(page)">{{ page }}</a>
                            </li>
                            <li :class="{ disabled: currentPage === totalPages }">
                                <a href="#" @click.prevent="changePage(currentPage + 1)">
                                    <span>&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Reservation Details Modal -->
<div class="modal fade" id="reservationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Reservation Details</h4>
            </div>
            <div class="modal-body" v-if="selectedReservation">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>Date:</th>
                                <td>{{ formatDate(selectedReservation.dteReservationDate) }}</td>
                            </tr>
                            <tr>
                                <th>Time:</th>
                                <td>{{ formatTime(selectedReservation.dteStartTime) }} - {{ formatTime(selectedReservation.dteEndTime) }}</td>
                            </tr>
                            <tr>
                                <th>Room:</th>
                                <td>{{ selectedReservation.strRoomCode }}</td>
                            </tr>
                            <tr>
                                <th>Purpose:</th>
                                <td>{{ selectedReservation.strPurpose }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th>Requested By:</th>
                                <td>{{ selectedReservation.strFirstname }} {{ selectedReservation.strLastname }}</td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    <span :class="getStatusClass(selectedReservation.enumStatus)">
                                        {{ selectedReservation.enumStatus.charAt(0).toUpperCase() + selectedReservation.enumStatus.slice(1) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Created:</th>
                                <td>{{ formatDateTime(selectedReservation.dteCreated) }}</td>
                            </tr>
                            <tr v-if="selectedReservation.strDescription">
                                <th>Description:</th>
                                <td>{{ selectedReservation.strDescription }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.min.js"></script>
<script>
new Vue({
    el: '#viewReservationsApp',
    data: {
        reservations: <?php echo json_encode($reservations ?? []); ?>,
        filteredReservations: [],
        selectedReservation: null,
        loading: false,
        processing: false,
        currentPage: 1,
        itemsPerPage: 10,
        sortField: 'dteReservationDate',
        sortDirection: 'desc',
        filters: {
            status: '',
            dateFrom: '',
            dateTo: '',
            search: ''
        },
        isAdmin: <?php echo json_encode($this->is_admin() || $this->is_registrar()); ?>,
        currentUserId: <?php echo $this->session->userdata('intID'); ?>,
        baseUrl: '<?php echo base_url(); ?>'
    },
    computed: {
        paginatedReservations() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredReservations.slice(start, end);
        },
        totalPages() {
            return Math.ceil(this.filteredReservations.length / this.itemsPerPage);
        },
        visiblePages() {
            const pages = [];
            const start = Math.max(1, this.currentPage - 2);
            const end = Math.min(this.totalPages, this.currentPage + 2);
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        }
    },
    mounted() {
        this.filteredReservations = [...this.reservations];
        this.sortReservations();
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
        formatDateTime(dateTimeString) {
            const date = new Date(dateTimeString);
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
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
        getSortIcon(field) {
            if (this.sortField !== field) {
                return 'fa fa-sort text-muted';
            }
            return this.sortDirection === 'asc' ? 'fa fa-sort-up' : 'fa fa-sort-down';
        },
        sortBy(field) {
            if (this.sortField === field) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortField = field;
                this.sortDirection = 'asc';
            }
            this.sortReservations();
        },
        sortReservations() {
            this.filteredReservations.sort((a, b) => {
                let aVal = a[this.sortField];
                let bVal = b[this.sortField];
                
                if (typeof aVal === 'string') {
                    aVal = aVal.toLowerCase();
                    bVal = bVal.toLowerCase();
                }
                
                if (aVal < bVal) return this.sortDirection === 'asc' ? -1 : 1;
                if (aVal > bVal) return this.sortDirection === 'asc' ? 1 : -1;
                return 0;
            });
        },
        applyFilters() {
            this.filteredReservations = this.reservations.filter(reservation => {
                // Status filter
                if (this.filters.status && reservation.enumStatus !== this.filters.status) {
                    return false;
                }
                
                // Date range filter
                if (this.filters.dateFrom && reservation.dteReservationDate < this.filters.dateFrom) {
                    return false;
                }
                if (this.filters.dateTo && reservation.dteReservationDate > this.filters.dateTo) {
                    return false;
                }
                
                // Search filter
                if (this.filters.search) {
                    const searchTerm = this.filters.search.toLowerCase();
                    const searchFields = [
                        reservation.strPurpose,
                        reservation.strRoomCode,
                        reservation.strFirstname + ' ' + reservation.strLastname,
                        reservation.strDescription || ''
                    ];
                    
                    if (!searchFields.some(field => 
                        field.toLowerCase().includes(searchTerm)
                    )) {
                        return false;
                    }
                }
                
                return true;
            });
            
            this.sortReservations();
            this.currentPage = 1;
        },
        changePage(page) {
            if (page >= 1 && page <= this.totalPages) {
                this.currentPage = page;
            }
        },
        viewReservation(reservation) {
            this.selectedReservation = reservation;
            $('#reservationModal').modal('show');
        },
        canEdit(reservation) {
            return reservation.enumStatus === 'pending' && 
                   (this.isAdmin || reservation.intFacultyID === this.currentUserId);
        },
        canDelete(reservation) {
            return this.isAdmin || 
                   (reservation.intFacultyID === this.currentUserId && reservation.enumStatus === 'pending');
        },
        async approveReservation(reservationId) {
            if (!confirm('Are you sure you want to approve this reservation?')) {
                return;
            }
            
            await this.updateReservationStatus(reservationId, 'approved');
        },
        async rejectReservation(reservationId) {
            const remarks = prompt('Please enter rejection reason (optional):');
            if (!confirm('Are you sure you want to reject this reservation?')) {
                return;
            }
            
            await this.updateReservationStatus(reservationId, 'rejected', remarks);
        },
        async updateReservationStatus(reservationId, status, remarks = '') {
            this.processing = true;
            try {
                const response = await fetch(this.baseUrl + 'reservation/approve_reservation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: reservationId,
                        status: status,
                        remarks: remarks
                    })
                });
                
                const data = await response.json();
                if (data.message === 'success') {
                    // Update local data
                    const reservation = this.reservations.find(r => r.intReservationID === reservationId);
                    if (reservation) {
                        reservation.enumStatus = status;
                    }
                    this.applyFilters();
                    this.showAlert(`Reservation ${status} successfully!`, 'success');
                } else {
                    this.showAlert(`Error ${status === 'approved' ? 'approving' : 'rejecting'} reservation`, 'error');
                }
            } catch (error) {
                this.showAlert(`Error ${status === 'approved' ? 'approving' : 'rejecting'} reservation`, 'error');
            } finally {
                this.processing = false;
            }
        },
        async deleteReservation(reservationId) {
            if (!confirm('Are you sure you want to delete this reservation?')) {
                return;
            }
            
            this.processing = true;
            try {
                const response = await fetch(this.baseUrl + 'reservation/delete_reservation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        id: reservationId
                    })
                });
                
                const data = await response.json();
                if (data.message === 'success') {
                    // Remove from local data
                    this.reservations = this.reservations.filter(r => r.intReservationID !== reservationId);
                    this.applyFilters();
                    this.showAlert('Reservation deleted successfully!', 'success');
                } else {
                    this.showAlert('Error deleting reservation', 'error');
                }
            } catch (error) {
                this.showAlert('Error deleting reservation', 'error');
            } finally {
                this.processing = false;
            }
        },
        showAlert(message, type) {
            if (type === 'success') {
                alert('✓ ' + message);
            } else {
                alert('✗ ' + message);
            }
        }
    }
});
</script>

<style>
.sortable {
    cursor: pointer;
    user-select: none;
}

.sortable:hover {
    background-color: #f5f5f5;
}

.pagination {
    margin: 20px 0;
}
</style>
