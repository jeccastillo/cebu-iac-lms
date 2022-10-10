<script type="text/javascript">
    $(document).ready(function(){
        
       
            
        $("#generate-or-num").click(function(){
           
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/generate_or',
                'method':'post',
                'data':{'year':$(this).attr("rel")},
                'dataType':'json',
                'success':function(ret){
                    $("#intORNumber").val(ret.orNumber);
                }
            
        });
        });
        $(".view-or").click(function(){
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/get_transaction_ajax',
                'method':'post',
                'data':{'orNumber':$(this).attr("rel")},
                'dataType':'json',
                'success':function(ret){
                    $("#transactionsBody").html(ret.viewer);
                    $("#transactionsModal").modal('show');
                    
                }
            
        });
        });
        
        $("#ROGStatusChange").change(function(){
            $(".loading-img").show();
            $(".overlay").show();
            var regid = $(this).attr('rel');
            var regVal = $(this).val();
            var data = {'intRegistrationID':regid,'intROG':regVal};
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/update_rog_status',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    alert(ret.message);
                }
            });
        
        });
        $("#addTransactionField").click(function(e){
           e.preventDefault();
            $("#transaction-wrapper").append('<div class="transaction-group"><div class="form-group col-xs-12 col-lg-6">'+
                '<label for="section">Amount To Pay</label>'+
                '<input id="intAmountPaid" type="number" name="intAmountPaid[]" class="form-control">'+
            '</div>'+
            '<div class="form-group col-xs-12 col-lg-6">'+
                '<label for="section">Transaction Type</label>'+
                '<select class="form-control" id="strTransactionType" name="strTransactionType[]" >'+                      
                        '<option value="tuition">Tuition</option>'+
                        '<option value="misc">Miscellaneous</option>'+
                        '<option value="id fee">ID Fee</option>'+     
                        '<option value="athletic fee">Athletic Fee</option>'+
                        '<option value="srf">SRF</option>'+     
                        '<option value="sfdf">SFDF</option>'+
                        '<option value="lab fee">Lab Fee</option>'+    
                        '<option value="csg">CSG</option>'+
                    '</select>'+
            '</div></div>');
            
        });
        
        $("#addTransactionBtn").click(function(e){

            tuitionTotal = $("#tuitionTotal").val();
            totalPaid = $("#totalPaid").val();
            
            var intAmountPaid = $("input[id='intAmountPaid']")
              .map(function(){return $(this).val();}).get();
            
            var strTransactionType = $("select[id='strTransactionType']")
              .map(function(){return $(this).val();}).get();
            
            intRegistrationID = $("#intRegistrationID").val();
            intORNumber = $("#intORNumber").val();
            intAYID = $("#intAYID").val();
            if(parseInt(totalPaid)+parseInt(intAmountPaid) > tuitionTotal)
            {
                    $("#sched-alert").html('<b>Alert!</b> The payment cannot exceed the total amount due.');
                    $(".alert-modal").show();
                        setTimeout(function() {
                            $(".alert").hide('fade', {}, 500)
                        }, 3000);
                
            }
            else if(intAmountPaid == "" || intORNumber == "")
            {
                $("#sched-alert").html('<b>Alert!</b> Fill up all necessary fields  .');
                $(".alert-modal").show();
                    setTimeout(function() {
                        $(".alert").hide('fade', {}, 500)
                    }, 3000);
            }
            else{
                
                intRegistrationID = $("#intRegistrationID").val();
                intORNumber = $("#intORNumber").val();
                intAYID = $("#intAYID").val();

                var data = {'intRegistrationID':intRegistrationID,'intAmountPaid':intAmountPaid,'strTransactionType':strTransactionType,'intORNumber':intORNumber,'intAYID':intAYID};
                $.ajax({
                    'url':'<?php echo base_url(); ?>unity/submit_transaction_ajax',
                    'method':'post',
                    'data':data,
                    'dataType':'json',
                    'success':function(ret){
                        if(ret.message != "success"){
                            $("#sched-alert").html('<b>Alert!</b> '+ret.message);
                            $(".alert-modal").show();
                            setTimeout(function() {
                                $(".alert").hide('fade', {}, 500)
                            }, 3000);
                        }
                        else
                            document.location= "<?php echo current_url(); ?>";

                }
                });
            }
        });
        
        $(".trash-or").click(function(e){
            
            conf = confirm("Are you sure you want to delete?");
            if(conf)
            {
            id = $(this).attr('rel');

            var data = {'id':id };
            $.ajax({
                'url':'<?php echo base_url(); ?>unity/delete_transaction_or/',
                'method':'post',
                'data':data,
                'dataType':'json',
                'success':function(ret){
                    if(ret.message != "success"){
                       alert("failed to delete");
                    }
                    else
                        document.location = "<?php echo current_url(); ?>";

            }
            });
            }
        });
        
    });
</script>