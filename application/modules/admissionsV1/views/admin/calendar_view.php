<div class="content-wrapper " id="applicant-container">
    <section class="content-header container ">
        <h1>
            FI Schedules
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>First Interview Schedules </a></li>
            <li class="active">Details</li>
        </ol>
    </section>
    <div class="content container">
    
    </div>
    
</div>




<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>

new Vue({
    el: '#applicant-container',
    data: {
        events: [],
        tags: ['foo', 'bar'],          
    },

    mounted() {
        



    },

    methods: {
      


        },
        toggleModal: function() {
            document.getElementById('modal').classList.toggle('hidden')
        },

    }

})
</script>