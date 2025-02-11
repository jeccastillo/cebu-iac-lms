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
<!-- cebu -->

<body>
    <div class="sheet-outer A4">
        <section class="sheet padding-5mm">
            <div
                style="position:absolute; top: 80px; left: 30px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                <?php echo $is_cash == 1?"&#10003":""; ?> ; </div>
            <!-- <div
                style="position:absolute; top: 105px; left: 30px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                &#10003; </div> -->
            <!-- <div
                style="position:absolute; top: 80px; left: 120px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                &#10003; </div> -->
            <!-- <div
                style="position:absolute; top: 105px; left: 120px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                &#10003; </div> -->
            <div
                style="position:absolute; top: 60px; right: -80px; width: 200px; height: 20px; font-size:16px">
                <?php echo $or_number; ?> </div>
            <div
                style="position:absolute; top: 95px; right: -60px; width: 200px; height: 20px; font-size:12px">
                <?php echo "  ".date("m/d/Y",strtotime($transaction_date)); ?> </div>
            <div
                style="position:absolute; top: 120px; right: -60px; width: 200px; height: 20px; font-size:12px">
                <?php if($student_id != 'undefined' && $student_id != ''): ?>
                <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?> <?php endif; ?>
            </div>
            <div style="position:absolute; top: 170px; left: 150px; height: 20px; font-size:12px">
                <?php echo $student_name; ?> </div>
            <div
                style="position:absolute; top: 190px; left: 150px; width: 200px; height: 20px; font-size:12px">
                <?php echo $tin; ?> </div>
            <div style="position:absolute; top: 220px; left: 150px; height: 20px; font-size:12px">
                <?php echo $student_address; ?> </div>
            <div
                style="position:absolute; top: 270px; left: 160px; width: 200px; height: 20px; font-size:12px">
                <?php echo $type." /  ".$term['enumSem']." ".$term['term_label']." ".$term['strYearStart']."-".$term['strYearEnd']; ?>
                <br />
            </div>
            <div
                style="position:absolute; top: 270px; right: -100px; width: 200px; height: 20px; font-size:12px">
                <?php echo $total_amount_due; ?> </div>
            <div
                style="position:absolute; top: 325px; right: -100px; width: 200px; height: 20px; font-size:12px">
                <?php echo $total_amount_due; ?> </div>
            <div
                style="position:absolute; top: 80px; left: 320px; width: 200px; height: 20px; font-size:12px">
                <?php echo $invoice_number; ?> </div>
        </section>
    </div>
</body>