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
            <div style="position:absolute; top: 130px; right: 60px; width: 200px; height: 20px;">
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
            <div style="position:absolute; top: 240px; left:0px; width: 500px; height: 20px;">
                <?php echo $type." /  ".$term['enumSem']." ".$term['term_label']." ".$term['strYearStart']."-".$term['strYearEnd']; ?>
            </div>
            <div style="position:absolute; top: 240px; left: 500px; width: 500px; height: 20px;">
                1
            </div>
            <div style="position:absolute; top: 240px; left: 550px; width: 200px; height: 20px;">
                89,880.82
            </div>
            <div style="position:absolute; top:  240px; left: 650px; width: 200px; height: 20px;">
                89,880.82
            </div>
            <div style="position:absolute; top: 255px; left:0px; width: 500px; height: 20px;">
                Inv 00001 - Reservation Fee
            </div>
            <div style="position:absolute; top: 255px; left: 500px; width: 500px; height: 20px;">
                1
            </div>
            <div style="position:absolute; top: 255px; left: 550px; width: 200px; height: 20px;">
                (10,000.00)
            </div>
            <div style="position:absolute; top:  255px; left: 650px; width: 200px; height: 20px;">
            (10,000.00)
            </div>
            <div style="position:absolute; top:  355px; left: 650px; width: 200px; height: 20px;">
            79,880.82
            </div>
            <div style="position:absolute; top:  390px; left: 100px; width: 200px; height: 20px;">
            15,000.00
            </div>
            <div style="position:absolute; top:  405px; left: 650px; width: 200px; height: 20px;">
            79,880.82
            </div>
            <div style="position:absolute; top:  455px; left: 0px; width: 200px; height: 20px;">
            BDO 12456
            </div>
            <div style="position:absolute; top:  485px; left: 300px; width: 200px; height: 20px;">
            79,880.82
            </div>    
            <div style="position:absolute; top:  565px; left: 50px; width: 200px; height: 20px;">
            15,000.00
            </div>        
            <div style="position:absolute; top: 550px; right: 20px; width: 200px; height: 20px;font-size:15px">
            Pinky D. Omayao
            </div>
        </section>
    </div>
</body>