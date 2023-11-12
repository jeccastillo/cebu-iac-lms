<html>
<head>
    <title>Secure Acceptance - Payment Form</title>
    <style>
        a {
            font-size: 1.0em;
            text-decoration: none;
        }

        input[type=submit] {
            margin-top: 10px;
        }

        span {
            font-weight: bold;
            width: 350px;
            display: inline-block;
        }

        .fieldName {
            width: 400px;
            font-weight: bold;
            vertical-align: top;
        }

        .fieldValue {
            width: 400px;
            font-weight: normal;
            vertical-align: top;
        }
    </style>
    <script src="https://code.jquery.com/jquery-1.7.min.js"></script>  
    <script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>  
</head>
<body>
<form id="payment_form" action="payment_confirmation.php" method="post">
    <input type="hidden" name="access_key" value="<REPLACE WITH ACCESS KEY>">
    <input type="hidden" name="profile_id" value="<REPLACE WITH PROFILE ID>">
    <input type="hidden" name="transaction_uuid" value="<?php echo uniqid() ?>">
    <input type="hidden" name="signed_field_names" value="access_key,profile_id,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,locale,transaction_type,reference_number,amount,currency">
    <input type="hidden" name="unsigned_field_names">
    <input type="hidden" name="signed_date_time" value="<?php echo gmdate("Y-m-d\TH:i:s\Z"); ?>">
    <input type="hidden" name="locale" value="en">
    <fieldset>
        <legend>Payment Details</legend>
        <div id="paymentDetailsSection" class="section">
            <span>transaction_type:</span><input type="text" name="transaction_type" size="25"><br/>
            <span>reference_number:</span><input type="text" name="reference_number" size="25"><br/>
            <span>amount:</span><input type="hidden" id="amount" name="amount"><br/>
            <span>currency:</span><input type="text" name="currency" size="25"><br/>
        </div>
    </fieldset>
    <input type="submit" id="submit" name="submit" value="Submit"/>    
</form>
<script type="text/javascript">    
    
    var payments = [];
    var remaining_amount = 0;
    var amount_paid = 0;
    var reservation_payment = 0;
    var application_payment = 0;
    var remaining_amount_formatted = 0;
    var amount_paid_formatted = 0;
    var tuition_data = {};
    var registration = {};
    var student = {};
    var slug = undefined;
    var registration_status = undefined;
    var reg_status = undefined;
    var payment_type = undefined;
    var payments = [];
    var tuition = 0;
    var has_down = false;
    var item_details = {
        price: 0,
        hey: this.desc
    };

    $(function () {
        payment_form = $('form').attr('id');
        addLinkToSetDefaults();
        $.ajax({
            'url':'<?php echo base_url(); ?>unity/online_payment_data/<?php echo $id ?>/<?php echo $sem; ?>',
            'method':'get',            
            'dataType':'json',
            'success':function(data){                 
                if(data.success){     
                    payments = data.data;
                    tuition_data = data.tuition_data;
                    registration = data.registration;            
                    registration_status = data.registration.intROG;
                    reg_status = data.reg_status;
                    student = data.student;         
                    slug = student.slug;                           
                    advanced_privilages = data.advanced_privilages;       
                    tuition = data.tuition;
                    payment_type = registration.paymentType;
                    remaining_amount = data.tuition_data.total;
                    if(payment_type == "partial")                       
                        remaining_amount = data.tuition_data.total_installment;

                    $.ajax({
                        'url':api_url + 'finance/transactions/<?php echo $slug ?>/<?php echo $sem; ?>',
                        'method':'get',            
                        'dataType':'json',
                        'success':function(data){                            
                            for(i in payments){
                                if(payments[i].status == "Paid"){                              
                                    remaining_amount = remaining_amount - payments[i].subtotal_order;
                                    amount_paid = amount_paid + payments[i].subtotal_order;                                    
                                }
                            }                                                              

                            $.ajax({
                                'url':api_url + 'finance/reservation/<?php echo $slug ?>/<?php echo $sem; ?>',
                                'method':'get',            
                                'dataType':'json',
                                'success':function(data){   
                                    reservation_payment = data.data;    
                                    application_payment = data.application;
                                    
                                    if(reservation_payment.status == "Paid" && data.student_sy == <?php echo $sem; ?>){
                                            remaining_amount = remaining_amount - reservation_payment.subtotal_order;                                                                                            
                                            amount_paid = amount_paid + reservation_payment.subtotal_order;                                        
                                    }                                
                                    remaining_amount = (remaining_amount < 0.02) ? 0 : remaining_amount;
                                    remaining_amount = Math.round(remaining_amount * 100) / 100;
                                    remaining_amount_formatted = remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                                    amount_paid_formatted = amount_paid.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                                
                                    item_details.price = remaining_amount;                                                                          

                                    let down_payment = (tuition_data.down_payment <= amount_paid) ? 0 : ( tuition_data.down_payment - amount_paid );
                                    
                                    if(registration.downpayment == 1 || down_payment == 0){
                                        has_down = true;
                                        
                                        //installment amounts                                                                    
                                        var temp = (tuition_data.installment_fee * 5) - parseFloat(remaining_amount);
                                        
                                        for(i=0; i < 5; i++){
                                            if(tuition_data.installment_fee > temp){
                                                val = this.tuition_data.installment_fee - temp;                                            
                                                item_details.price = val;
                                                break;
                                            }     
                                            else{
                                                temp = temp - tuition_data.installment_fee;
                                            }                                                                       
                                        }
                                        
                                        
                                    }
                                    else if(payment_type == "partial"){
                                        
                                        item_details.price = down_payment;
                                    }                            
                                    else{
                                        
                                        item_details.price = remaining_amount;
                                    }                         
                                    $.ajax({
                                    'url':api_url + 'finance/transactions/<?php echo $slug ?>/<?php echo $sem; ?>',
                                    'method':'get',            
                                    'dataType':'json',
                                    'success':function(data){
                                        $.ajax({
                                            'url':api_url + 'admissions/student-info/<?php echo $slug ?>',
                                            'method':'get',            
                                            'dataType':'json',
                                            'success':function(data){ 
                                                $("#amount").val(remaining_amount);                                               
                                            }
                                        });
                                    }
                                });
                                }
                            });
                        }
                    });
                }
            }
        });
    });
    //     axios.get()
    //     .then((data) => {  
    //         if(data.data.success){                                                                                           
    //             this.registration = data.data.registration;            
    //             this.registration_status = data.data.registration.intROG;
    //             this.reg_status = data.data.reg_status;
    //             this.student = data.data.student;         
    //             this.slug = this.student.slug;                           
    //             this.advanced_privilages = data.data.advanced_privilages;       
    //             this.tuition = data.data.tuition;
    //             this.tuition_data = data.data.tuition_data;          
    //             this.payment_type = this.registration.paymentType;
    //             this.remaining_amount = data.data.tuition_data.total;
    //             if(this.payment_type == "partial")                       
    //                 this.remaining_amount = data.data.tuition_data.total_installment;
                                
                    

    //             axios.get(api_url + 'finance/transactions/<?php echo $slug ?>/<?php echo $sem; ?>')
    //             .then((data) => {                                                 
    //                 this.payments = data.data.data;
    //                 for(i in this.payments){
    //                     if(this.payments[i].status == "Paid"){                              
    //                         this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
    //                         this.amount_paid = this.amount_paid + this.payments[i].subtotal_order;                                    
    //                     }
    //                 }                        
                    
                    
                    
    //                 this.other_payments = data.data.other;
                            
                                                    

    //                 axios.get(api_url + 'finance/reservation/<?php echo $slug ?>/<?php echo $sem; ?>')
    //                 .then((data) => {
    //                     this.reservation_payment = data.data.data;    
    //                     this.application_payment = data.data.application;
                        
    //                     if(this.reservation_payment.status == "Paid" && data.data.student_sy == this.sem){
    //                             this.remaining_amount = this.remaining_amount - this.reservation_payment.subtotal_order;                                                                                            
    //                             this.amount_paid = this.amount_paid + this.reservation_payment.subtotal_order;                                        
    //                     }                                
    //                     this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;
    //                     this.remaining_amount = Math.round(this.remaining_amount * 100) / 100;
    //                     this.remaining_amount_formatted = this.remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    //                     this.amount_paid_formatted = this.amount_paid.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                                
    //                     this.item_details.price = this.remaining_amount;
    //                     this.loader_spinner = false;

    //                     let down_payment = (this.tuition_data.down_payment <= this.amount_paid) ? 0 : ( this.tuition_data.down_payment - this.amount_paid );
                        
    //                     if(this.registration.downpayment == 1 || down_payment == 0){
    //                         this.has_down = true;
    //                         console.log(this.tuition_data.installment_fee);
    //                         //installment amounts                                                                    
    //                         var temp = (this.tuition_data.installment_fee * 5) - parseFloat(this.remaining_amount);
    //                         console.log(temp);
    //                         for(i=0; i < 5; i++){
    //                             if(this.tuition_data.installment_fee > temp){
    //                                 val = this.tuition_data.installment_fee - temp;                                            
    //                                 this.item_details.price = val;
    //                                 break;
    //                             }     
    //                             else{
    //                                 temp = temp - this.tuition_data.installment_fee;
    //                             }                                                                       
    //                         }
                            
                            
    //                     }
    //                     else if(this.payment_type == "partial"){
                            
    //                         this.item_details.price = down_payment;
    //                     }                            
    //                     else{
                            
    //                         this.item_details.price = this.remaining_amount;
    //                     }      
    //                     axios.get(api_url + 'admissions/student-info/<?php echo $slug ?>')
    //                     .then((data) => {
    //                         this.student_api_data = data.data.data;
    //                         Swal.close();
    //                     })
    //                     .catch((error) => {
    //                         console.log(error);
    //                     })
                        
    //                 })
    //                 .catch((error) => {
    //                     console.log(error);
    //                 })
    //             })
    //             .catch((error) => {
    //                 console.log(error);
    //             })      
    //         }
    //         else{
    //             document.location = this.base_url + 'users/login';
    //         }
                            
    //     })
    //     .catch((error) => {
    //         console.log(error);
    //     })
    // });


    function setDefaultsForAll() {
            if (payment_form === "payment_confirmation"){
        setDefaultsForUnsignedDetailsSection();
    }
    else {
        setDefaultsForPaymentDetailsSection();
    } 
    }

    function addLinkToSetDefaults() {
        $(".section").prev().each(function (i) {
            legendText = $(this).text();
            $(this).text("");

            var setDefaultMethod = "setDefaultsFor" + capitalize($(this).next().attr("id")) + "()";

            newlink = $(document.createElement("a"));
            newlink.attr({
                id:'link-' + i, name:'link' + i, href:'#'
            });
            newlink.append(document.createTextNode(legendText));
            newlink.bind('click', function () {
                eval(setDefaultMethod);
            });

            $(this).append(newlink);
        });

        newbutton = $(document.createElement("input"));
        newbutton.attr({
            id:'defaultAll', value:'Default All', type:'button', onClick:'setDefaultsForAll()'
        });
        newbutton.bind('click', function() {
            setDefaultsForAll;
        });
        $("#"+payment_form).append(newbutton);
    }

    function capitalize(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }

    function setDefaultsForPaymentDetailsSection() {
        $("input[name='transaction_type']").val("authorization");
        $("input[name='reference_number']").val(new Date().getTime());
        $("input[name='amount']").val("100.00");
        $("input[name='currency']").val("USD");
    }

    function setDefaultsForUnsignedDetailsSection(){
        $("input[name='card_type']").val("001");
        $("input[name='card_number']").val("4242424242424242");
        $("input[name='card_expiry_date']").val("11-2020");
    }


</script>
</body>
</html>
