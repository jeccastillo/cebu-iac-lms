<style>
body {
    margin: 0;
    font-family: Arial, Sans-Serif;
}

.sheet-outer {
    margin: 0;
}

.sheet {
    margin: 0;
    overflow: hidden;
    position: relative;
    box-sizing: border-box;
    page-break-after: always;
}

section {
    font-size: 12px
}

table {
    width: 100%;
}

table tr td {
    vertical-align: top;
}

@media screen {
    body {
        background: #e0e0e0
    }

    .sheet {
        background: white;
        box-shadow: 0 .5mm 2mm rgba(0, 0, 0, .3);
        margin: 5mm auto;
    }
}

.sheet-outer.A4 .sheet {
    width: 210mm;
    height: 296mm
}

.sheet.padding-5mm {
    padding-top: 10mm;
    padding-left: 8mm;
    padding-right: 10mm;
}

@page {
    size: A4;
    margin: 0
}

@media print {

    .sheet-outer.A4,
    .sheet-outer.A5.landscape {
        width: 210mm
    }
}
</style>

<body>
    <div class="sheet-outer A4">
        <section class="sheet padding-5mm"> <?php if($cashCharge): ?> <div
                style="position:absolute; top: 140px; left: 55px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                &#10003; </div> <?php else: ?> <div
                style="position:absolute; top: 140px; left: 162px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                &#10003; </div> <?php endif; ?> <div
                style="position:absolute; top: 140px; left: 520px; width: 200px; height: 20px;">
                Invoice No: <?php echo $invoice_number; ?> </div>
            <!---DATE--->
            <div style="position:absolute; top: 175px; right: -60px; width: 200px; height: 20px;">
                <?php echo $transaction_date; ?> </div>
            <!---TIN---> <?php if(isset($payee)): ?> <div
                style="position:absolute; top: 195px; right: -45px; width: 200px; height: 20px;">
                <?php echo $payee['tin']; ?> </div> <?php endif; ?> <div
                style="position:absolute; top: 175px; left: 85px; width: 500px; height: 20px;">
                <?php if($student_id != 'undefined' && $student_id != ''): ?>
                <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?> <?php endif; ?>
                &nbsp; <?php echo $student_name; ?> </div>
            <div style="position:absolute; top: 200px; left: 90px; width: 500px; height: 20px;">
                <?php echo $student_address; ?> </div>
            <div style="position:absolute; top: 250px; left:70px; width: 500px; height: 20px;"> <?php echo $type; 
                 if(!isset($payee)): 
                    echo " /  ".$term['enumSem']." ".$term['term_label']." ".$term['strYearStart']."-".$term['strYearEnd'];
                endif; ?> </div>
            <div style="position:absolute; top: 250px; left: 525px; width: 500px; height: 20px;"> 1
            </div>
            <div style="position:absolute; top: 250px; left: 595px; width: 200px; height: 20px;">
                <?php echo $full_assessment; ?> </div>
            <div style="position:absolute; top:  250px; left: 710px; width: 200px; height: 20px;">
                <?php echo $full_assessment; ?> </div>
            <div style="position:absolute; top: 270px; left:10px; width: 500px; height: 20px;">
                <?php echo $reservation_description; ?> </div>
            <div style="position:absolute; top: 270px; left: 500px; width: 500px; height: 20px;">
                <?php echo $reservation_amount != 0 ? 1 : ""; ?> </div>
            <div style="position:absolute; top: 270px; left: 550px; width: 200px; height: 20px;">
                <?php echo $reservation_amount != 0 ? "-".$reservation_amount : ""; ?> </div>
            <div style="position:absolute; top:  270px; left: 650px; width: 200px; height: 20px;">
                <?php echo $reservation_amount != 0 ? "-".$reservation_amount : ""; ?> </div>
            <!---VAT ZERO RATED--->
            <div style="position:absolute; top: 335px; left: 650px; width: 200px; height: 20px;">
                <?php echo $vat_zero_rated_sale != 0 ? $vat_zero_rated_sale : "" ; ?> </div>
            <!---TOTAL SALE--->
            <div style="position:absolute; top:  355px; left: 650px; width: 200px; height: 20px;">
                <?php  echo $total_sale_taxed == 0 ? $total_assessment : $total_sale_taxed; ?>
            </div>
            <!---- VAT--->
            <div style="position:absolute; top: 370px; left: 650px; width: 200px; height: 20px;">
                <?php echo $less_vat != 0 ? $less_vat : "" ; ?> </div>
            <!---- Less EWT--->
            <div style="position:absolute; top: 388px; left: 650px; width: 200px; height: 20px;">
                <?php echo $less_ewt != 0 ? $less_ewt : "" ; ?> </div>
            <!---- Total Payment--->
            <div style="position:absolute; top:  415px; left: 650px; width: 200px; height: 20px;">
                <?php  echo $total_assessment; ?> </div>
            <!---Payment Left Amount-->
            <div style="position:absolute; top:  370px; left: 130px; width: 200px; height: 20px;">
                <?php  echo $is_cash == 1 ? $total_amount_due : ''; ?> </div>
            <div style="position:absolute; top:  385px; left: 130px; width: 200px; height: 20px;">
                <?php  echo $is_cash == 0 ? $total_amount_due : ''; ?> </div>
            <div style="position:absolute; top: 420px; left: 130px; width: 200px; height: 20px;">
                <?php  echo $is_cash == 0 ? $remarks : ''; ?> </div>
            <div style="position:absolute; top: 445px; left: 130px; width: 200px; height: 20px;">
                <?php  echo $is_cash == 2 || $is_cash == 3 ? $total_amount_due : ''; ?> </div>
            <div style="position:absolute; top:  465px; left: 40px; width: 200px; height: 20px;">
                <?php  echo $is_cash != 0 ? $remarks : ''; ?> </div>
            <!--Vatable-->
            <div style="position:absolute; top:  462px; left: 305px; width: 200px; height: 20px;">
                <?php  echo $amount_less_vat != 0 ? $amount_less_vat : ""; ?> </div>
            <!--Vat Exempt Sale--> <?php if($vat_exempt != 0 && $less_vat != 0): ?> <div
                style="position:absolute; top:  490px; left: 340px; width: 200px; height: 20px;">
                <?php  echo $vat_exempt; ?> </div>
            <?php elseif($vat_exempt == 0 && $less_vat != 0): ?> <div
                style="position:absolute; top:  490px; left: 340px; width: 200px; height: 20px;">
                <?php  echo ""; ?> </div> <?php else: ?> <div
                style="position:absolute; top:  490px; left: 340px; width: 200px; height: 20px;">
                <?php  echo $total_assessment; ?> </div> <?php endif; ?>
            <!--Total Amount received-->
            <div style="position:absolute; top:585px; left: 130px; width: 200px; height: 20px;">
                <?php  echo $total_amount_due == 0 ? "" : $total_amount_due; ?> </div>
            <div
                style="position:absolute; top: 565px; left: 600px; width: 200px; height: 20px;font-size:15px">
                <?php echo $cashier_name; ?> </div>
        </section>
    </div>
</body>