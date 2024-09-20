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
        <section class="sheet padding-5mm">
            <?php if($cashCharge): ?>
                <div style="position:absolute; top: 140px; left: 40px; width: 200px; height: 20px;font-size:1.6em;">
                    &#10003;
                </div>
            <?php else: ?>
                <div style="position:absolute; top: 140px; left: 142px; width: 200px; height: 20px;font-size:1.6em;">
                    &#10003;
                </div>
            <?php endif; ?>
            <div style="position:absolute; top: 125px; right: 60px; width: 200px; height: 20px;">
                Invoice No: <?php echo $invoice_number; ?>
            </div>
            <div style="position:absolute; top: 170px; right: -60px; width: 200px; height: 20px;">
                <?php echo $transaction_date; ?>
            </div>
            <div style="position:absolute; top: 170px; left: 60px; width: 500px; height: 20px;">
                <?php if($student_id != 'undefined' && $student_id != ''): ?>
                <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?>
                <?php endif; ?>
                &nbsp; <?php echo $student_name; ?>
            </div>
            <div style="position:absolute; top: 190px; left: 60px; width: 500px; height: 20px;">
                <?php echo $student_address; ?>
            </div>
            <div style="position:absolute; top: 245px; left:0px; width: 500px; height: 20px;">
                <?php echo $type." /  ".$term['enumSem']." ".$term['term_label']." ".$term['strYearStart']."-".$term['strYearEnd']; ?>
            </div>
            <div style="position:absolute; top: 245px; left: 500px; width: 500px; height: 20px;">
                1
            </div>
            <div style="position:absolute; top: 245px; left: 550px; width: 200px; height: 20px;">
                <?php echo $full_assessment; ?>
            </div>
            <div style="position:absolute; top:  245px; left: 650px; width: 200px; height: 20px;">
                <?php echo $full_assessment; ?>
            </div>
            <div style="position:absolute; top: 270px; left:0px; width: 500px; height: 20px;">
                <?php echo $reservation_description; ?>
            </div>
            <div style="position:absolute; top: 270px; left: 500px; width: 500px; height: 20px;">
               <?php echo $reservation_amount != 0 ? 1 : ""; ?>
            </div>
            <div style="position:absolute; top: 270px; left: 550px; width: 200px; height: 20px;">
                <?php echo $reservation_amount != 0 ? "-".$reservation_amount : ""; ?>
            </div>
            <div style="position:absolute; top:  270px; left: 650px; width: 200px; height: 20px;">
                <?php echo $reservation_amount != 0 ? "-".$reservation_amount : ""; ?>
            </div>
            <div style="position:absolute; top:  355px; left: 650px; width: 200px; height: 20px;">
                <?php  echo $total_assessment; ?>
            </div>
            <div style="position:absolute; top:  380px; left: 100px; width: 200px; height: 20px;">
                <?php  echo $total_amount_due; ?>
            </div>
            <div style="position:absolute; top:  405px; left: 650px; width: 200px; height: 20px;">
                <?php  echo $total_assessment; ?>
            </div>
            <div style="position:absolute; top:  460px; left: 0px; width: 200px; height: 20px;">
                <?php  echo $remarks; ?>
            </div>
            <div style="position:absolute; top:  485px; left: 300px; width: 200px; height: 20px;">
                <?php  echo $total_assessment; ?>
            </div>
            <div style="position:absolute; top:  565px; left: 50px; width: 200px; height: 20px;">
                <?php  echo $total_amount_due; ?>
            </div>
            <div style="position:absolute; top: 550px; right: 20px; width: 200px; height: 20px;font-size:15px">
                <?php echo $cashier_name; ?>
            </div>
        </section>
    </div>
</body>