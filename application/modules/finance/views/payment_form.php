<html>
<head>
    <title>Secure Acceptance - Payment Form</title>
    <link rel="stylesheet" type="text/css" href="payment.css"/>
    <script src="https://code.jquery.com/jquery-1.7.min.js"></script>    
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
            <span>amount:</span><input type="text" name="amount" size="25"><br/>
            <span>currency:</span><input type="text" name="currency" size="25"><br/>
        </div>
    </fieldset>
    <input type="submit" id="submit" name="submit" value="Submit"/>
    <script type="text/javascript" src="payment_form.js"></script>
</form>
</body>
<script type="text/javascript">
    $(function () {
        payment_form = $('form').attr('id');
        addLinkToSetDefaults();

        axios.get(this.base_url + 'unity/online_payment_data/<?php echo $id ?>/<?php echo $sem; ?>')
        .then((data) => {  
            if(data.data.success){                                                                                           
                this.registration = data.data.registration;            
                this.registration_status = data.data.registration.intROG;
                this.reg_status = data.data.reg_status;
                this.student = data.data.student;         
                this.slug = this.student.slug;                           
                this.advanced_privilages = data.data.advanced_privilages;       
                this.tuition = data.data.tuition;
                this.tuition_data = data.data.tuition_data;          
                this.payment_type = this.registration.paymentType;
                this.remaining_amount = data.data.tuition_data.total;
                if(this.payment_type == "partial")                       
                    this.remaining_amount = data.data.tuition_data.total_installment;
                                
                    

                axios.get(api_url + 'finance/transactions/<?php echo $slug ?>/<?php echo $sem; ?>')
                .then((data) => {                                                 
                    this.payments = data.data.data;
                    for(i in this.payments){
                        if(this.payments[i].status == "Paid"){                              
                            this.remaining_amount = this.remaining_amount - this.payments[i].subtotal_order;
                            this.amount_paid = this.amount_paid + this.payments[i].subtotal_order;                                    
                        }
                    }                        
                    
                    
                    
                    this.other_payments = data.data.other;
                            
                                                    

                    axios.get(api_url + 'finance/reservation/<?php echo $slug ?>/<?php echo $sem; ?>')
                    .then((data) => {
                        this.reservation_payment = data.data.data;    
                        this.application_payment = data.data.application;
                        
                        if(this.reservation_payment.status == "Paid" && data.data.student_sy == this.sem){
                                this.remaining_amount = this.remaining_amount - this.reservation_payment.subtotal_order;                                                                                            
                                this.amount_paid = this.amount_paid + this.reservation_payment.subtotal_order;                                        
                        }                                
                        this.remaining_amount = (this.remaining_amount < 0.02) ? 0 : this.remaining_amount;
                        this.remaining_amount = Math.round(this.remaining_amount * 100) / 100;
                        this.remaining_amount_formatted = this.remaining_amount.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                        this.amount_paid_formatted = this.amount_paid.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');                                
                        this.item_details.price = this.remaining_amount;
                        this.loader_spinner = false;

                        let down_payment = (this.tuition_data.down_payment <= this.amount_paid) ? 0 : ( this.tuition_data.down_payment - this.amount_paid );
                        
                        if(this.registration.downpayment == 1 || down_payment == 0){
                            this.has_down = true;
                            console.log(this.tuition_data.installment_fee);
                            //installment amounts                                                                    
                            var temp = (this.tuition_data.installment_fee * 5) - parseFloat(this.remaining_amount);
                            console.log(temp);
                            for(i=0; i < 5; i++){
                                if(this.tuition_data.installment_fee > temp){
                                    val = this.tuition_data.installment_fee - temp;                                            
                                    this.item_details.price = val;
                                    break;
                                }     
                                else{
                                    temp = temp - this.tuition_data.installment_fee;
                                }                                                                       
                            }
                            
                            
                        }
                        else if(this.payment_type == "partial"){
                            
                            this.item_details.price = down_payment;
                        }                            
                        else{
                            
                            this.item_details.price = this.remaining_amount;
                        }      
                        axios.get(api_url + 'admissions/student-info/<?php echo $slug ?>')
                        .then((data) => {
                            this.student_api_data = data.data.data;
                            Swal.close();
                        })
                        .catch((error) => {
                            console.log(error);
                        })
                        
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                })
                .catch((error) => {
                    console.log(error);
                })      
            }
            else{
                document.location = this.base_url + 'users/login';
            }
                            
        })
        .catch((error) => {
            console.log(error);
        })
    });


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
</html>
