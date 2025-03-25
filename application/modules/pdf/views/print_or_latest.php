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
    <div class="sheet-outer A4" id="vue-container">
        <section class="sheet padding-5mm">
            <div style="position:absolute; top: 90px; left: 20px; width: 100px; height: 20px;">
                <!-- or number -->
            </div>
            <div style="position:absolute; top: 100px; left: 80px; width: 200px; height: 20px;">
                <?php echo $type; ?> </div>
            <div>
                <div style="position:absolute; top: 90px; left: 115px; width: 200px; height: 20px;">
                    <?php echo $total_amount_due; ?> </div>
                <div
                    style="display:none;position:absolute; top: 140px; left: 5px; width: 200px; height: 20px;">
                    <?php echo $description == "Reservation Payment" ? "NON REFUNDABLE AND NON TRANSFERABLE":""; ?><br />
                    <?php echo "SY ".$term['strYearStart']."-".$term['strYearEnd']." ".$term['enumSem']." ".$term['term_label']."<br />".$type; ?>
                </div>
                <div style="position:absolute; top: 320px; left: 95px; width: 200px; height: 20px;">
                    <!--  $total_amount_due -->
                </div>
                <div style="position:absolute; top: 330px; left: 15px; width: 350px; ">
                    <?php echo $remarks; ?> </div>
                <div
                    style="position:absolute; top: 355px; left: 115px; width: 200px; height: 20px;">
                    <?php echo $total_amount_due; ?> </div>
                <div style="position:absolute; top: 100; right: 60px; width: 200px; height: 20px;">
                    OR No: <?php echo $or_number; ?> </div>
                <div
                    style="position:absolute; top: 115px; right: -60px; width: 200px; height: 20px;">
                    <?php echo "  ".date("m/d/Y",strtotime($transaction_date)); ?> </div>
                <div
                    style="position:absolute; top: 140px; left: 336px; width: 500px; height: 20px;">
                    <?php if($student_id != 'undefined' && $student_id != ''): ?>
                    <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?> <?php endif; ?>
                    &nbsp; <?php echo $student_name == 'undefined' ? '' : $student_name ; ?> </div>
                <div
                    style="position:absolute; top: 170px; left: 310px; width: 500px; height: 20px;">
                    <?php echo $student_address; ?> </div>
                <div
                    style="position:absolute; top: 195px; left: 295px; width: 500px; height: 20px;">
                    <?php echo $tin; ?> </div>
                <div
                    style="position:absolute; top: 215px; left: 370px; width: 500px; height: 20px;">
                    <?php echo $total_amount_due_text; ?> </div>
                <div
                    style="position:absolute; top: 240px; left: 670px; width: 200px; height: 20px;">
                    <?php echo $total_amount_due; ?> </div>
                <div
                    style="position:absolute; top: 265px; left: 400px; width: 400px; height: 20px;">
                    <?php echo $type;?> </div>
                <div style="position:absolute; top: 310px; left: 575px; height: 20px;">
                    <?php echo $cashier_name; ?> </div>
        </section>
    </div>
</body>