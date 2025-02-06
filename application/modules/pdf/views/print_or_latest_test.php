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
                style="position:absolute; top: 170px; left: 40px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                &#10003; </div>
            <div
                style="position:absolute; top: 170px; left: 140px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                &#10003; </div>
            <div style="position:absolute; top: 150px; right: -60px; width: 200px; height: 20px; font-size:12px">
                <?php echo "  ".date("m/d/Y",strtotime($transaction_date)); ?> </div>
            <div style="position:absolute; top: 170px; right: -60px; width: 200px; height: 20px; font-size:12px">
                <?php echo "  ".date("m/d/Y",strtotime($transaction_date)); ?> </div>
            <div style="position:absolute; top: 200px; right: -60px; width: 200px; height: 20px; font-size:12px">
                Account No. </div>
            <div style="position:absolute; top: 260px; left: 70px; height: 20px; font-size:12px">
                <?php echo $student_name; ?> </div>
            <div style="position:absolute; top: 290px; left: 70px; width: 200px; height: 20px; font-size:12px">
                <?php //echo $tin; ?>
                TIN
            </div>
            <div style="position:absolute; top: 320px; left: 70px; height: 20px; font-size:12px">
                <?php echo $student_address; ?> </div>
            <div style="position:absolute; top: 380px; left: 70px; width: 200px; height: 20px; font-size:12px">
                <?php //echo $description == "Reservation Payment" ? "NON REFUNDABLE AND NON TRANSFERABLE":""; ?>
                PAYMENT FOR
                <br />
            </div>
            <div style="position:absolute; top: 380px; right: -70px; width: 200px; height: 20px; font-size:12px">
                <?php echo $total_amount_due; ?> </div>
            <div style="position:absolute; top: 450px; right: -70px; width: 200px; height: 20px; font-size:12px">
                <?php echo $total_amount_due; ?> </div>
            <div style="position:absolute; top: 520px; right: -70px; width: 200px; height: 20px; font-size:12px">
                Invoice Ref</div>
        </section>
    </div>
</body>