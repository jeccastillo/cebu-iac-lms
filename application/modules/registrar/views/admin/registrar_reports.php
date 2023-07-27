<aside class="right-side" id="registration-container">    
    <section class="content-header">
        <h1>
            Registrar Reports
        </h1>                
        <hr />
    </section>
        <hr />
    <div class="content">
        <div class="row">
            <div class="col-md-4 col-sm-8 col-xs-12">
                 <!-- small box -->
                 <div class="small-box bg-yellow">
                     <div class="inner">
                         <h3>Enrollment List</h3>

                         <p>CHED Enrollment List Report for this Term</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-list"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>registrar/enrollment_report" class="small-box-footer">
                         View <i class="fa fa-arrow-circle-right"></i>
                     </a>
                     <!-- <a href="<?php echo base_url(); ?>excel/reports_enrollment" class="small-box-footer">
                         Excel <i class="fa fa-arrow-circle-right"></i>
                     </a> -->
                 </div>

                 <!-- small box -->
             </div>
             <div class="col-md-4 col-sm-8 col-xs-12">
                 <!-- small box -->
                 <div class="small-box bg-blue">
                     <div class="inner">
                         <h3>Enrollment Summary</h3>

                         <p>Summary of Enrollments this Term</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-list"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>registrar/enrollment_summary" class="small-box-footer">
                         View <i class="fa fa-arrow-circle-right"></i>
                     </a>
                     <!-- <a href="<?php echo base_url(); ?>excel/reports_enrollment" class="small-box-footer">
                         Excel <i class="fa fa-arrow-circle-right"></i>
                     </a> -->
                 </div>

                 <!-- small box -->
             </div>

             <div class="col-md-4 col-sm-8 col-xs-12">
                 <!-- small box -->
                 <div class="small-box bg-blue">
                     <div class="inner">
                         <h3>Enrollment</h3>

                         <p>Daily Enrollment Report</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-list"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>registrar/daily_enrollment_report" class="small-box-footer">
                         View <i class="fa fa-arrow-circle-right"></i>
                     </a>
                     <!-- <a href="<?php echo base_url(); ?>excel/reports_enrollment" class="small-box-footer">
                         Excel <i class="fa fa-arrow-circle-right"></i>
                     </a> -->
                 </div>

                 <!-- small box -->
             </div>             
             <div class="col-md-4 col-sm-8 col-xs-12">
                 <!-- small box -->
                 <div class="small-box bg-blue">
                     <div class="inner">
                         <h3>Reservation Summary</h3>

                         <p>Summary of Reservations this Term</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-list"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>registrar/reservation_summary" class="small-box-footer">
                         View <i class="fa fa-arrow-circle-right"></i>
                     </a>
                     <!-- <a href="<?php echo base_url(); ?>excel/reports_enrollment" class="small-box-footer">
                         Excel <i class="fa fa-arrow-circle-right"></i>
                     </a> -->
                 </div>

                 <!-- small box -->
             </div>
             <div class="col-md-4 col-sm-8 col-xs-12">
                 <!-- small box -->
                 <div class="small-box bg-green">
                     <div class="inner">
                         <h3>Enlisted Students</h3>

                         <p>Enlisted Students Report</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-list"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>registrar/enlistment_report" class="small-box-footer">
                         View <i class="fa fa-arrow-circle-right"></i>
                     </a>
                 </div>

                 <!-- small box -->
             </div>
             <div class="col-md-4 col-sm-8 col-xs-12">
                 <!-- small box -->
                 <div class="small-box bg-green">
                     <div class="inner">
                         <h3>Faculty</h3>

                         <p>Faculty Loading</p>
                     </div>
                     <div class="icon">
                         <i class="fa fa-list"></i>
                     </div>
                     <a href="<?php echo base_url(); ?>unity/view_classlist_archive_admin<?php echo $sem; ?>/0/0/1" class="small-box-footer">
                         View <i class="fa fa-arrow-circle-right"></i>
                     </a>
                 </div>

                 <!-- small box -->
             </div>
             
        </div>
    </div>
</aside>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#registration-container',
    data: {
        user: {}              
    },

    mounted() {       
        // axios.get(this.base_url + 'registrar/registrar_reports_data/' + this.id + '/' + this.sem)
        //         .then((data) => {
        //             this.user = data.data.user;
        //         });
    },

    methods: {      
        
    }

})
</script>

