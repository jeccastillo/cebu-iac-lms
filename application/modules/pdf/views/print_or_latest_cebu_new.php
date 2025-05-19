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
    font-size: 12px;
    border-collapse: collapse;
    border: 2px solid rgb(140 140 140);
    font-family: Arial, sans-serif;
}

#date-table {
    width: 80%;
    position: absolute;
    right: 0;
}

th,
td {
    border: 1px solid rgb(140 140 140);
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
    padding-top: 6mm;
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

h1,
p {
    margin: 0;
    text-align: center;
}

input[type=text] {
    width: 100%;
    border: 0 solid black;
    border-bottom: 1px solid black;
    padding: 0;
}

.heading-grid-container {
    display: grid;
    justify-items: left;
    grid-template-columns: max-content auto;
}

.date-grid-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    margin-top: 10px;
    margin-bottom: 2px;
}

.checkbox-grid-container {
    display: inline-grid;
    grid-template-columns: repeat(2, max-content);
    gap: 5px 10px;
    padding-top: 6px;
}

.times-new {
    font-family: 'Times New Roman', Times, serif;
}
</style>

<body>
    <div class="sheet-outer A4">
        <section class="sheet padding-5mm">
            <div>
                <div class="heading-grid-container">
                    <div style="position:relative;padding-inline-start: 70px;">
                        <img width="70px" height="80px" style="position: absolute;left: 0;top: -5;"
                            src="
                        <?php echo $img_dir; ?>receipt-logo.jpg" alt="">
                        <h1>iACADEMY, INC.</h1>
                        <p><small style="font-size: 10px">5th Floor Filinvest Cebu Cyberzone Tower
                                Two, Salinas Drive cor. W Geonzon St., Cebu IT Park,</small></p>
                        <p><small style="font-size: 10px">Apas 6000 City of Cebu, Cebu Philippines
                                VAT REG TIN: 214-749-003-00003</small></p>
                    </div>
                    <div style="justify-self:right;align-content: center;">
                        <p class="times-new" style="font-weight: 600;">OFFICIAL RECEIPT</p>
                        <p class="times-new"
                            style="text-align: left;padding-left: 10px;padding-top: 5px;">
                            <span style="font-size: 19px;">No.</span>
                            <b><?php echo $or_number; ?></b>
                        </p>
                    </div>
                </div>
                <div class="date-grid-container">
                    <div class="checkbox-grid-container">
                        <div style="position:relative;">
                            <input type="checkbox" disabled>
                            <label class="times-new" style="font-size:14px">CASH</label>
                            <div
                                style="position:absolute; top: -10px;left: 2px;font-size:1.6em;font-weight:800;">
                                <?php echo $is_cash == 1?"&#10003":""; ?> </div>
                        </div>
                        <div style="position:relative;">
                            <input type="checkbox" disabled>
                            <label class="times-new" style="font-size:14px">ONLINE TRANSFER</label>
                            <div
                                style="position:absolute; top: -10px;left: 2px;font-size:1.6em;font-weight:800;">
                                <?php echo $is_cash == 4?"&#10003":""; ?> </div>
                        </div>
                        <div style="position:relative;">
                            <input type="checkbox" disabled>
                            <label class="times-new" style="font-size:14px">CHECK</label>
                            <div
                                style="position:absolute;top: -12px;left: 2px;font-size:1.6em;font-weight:800;">
                                <?php echo $is_cash == 0?"&#10003":""; ?> </div>
                        </div>
                        <div style="position:relative;">
                            <input type="checkbox" disabled>
                            <label class="times-new" style="font-size:14px">OTHERS</label>
                            <div
                                style="position:absolute; top: -12px;left: 2px;font-size:1.6em;font-weight:800;">
                                <?php echo $is_cash == 2 || $is_cash == 3?"&#10003":""; ?> </div>
                        </div>
                    </div>
                    <div style="position:relative">
                        <table id="date-table">
                            <tbody>
                                <tr>
                                    <td class="times-new"
                                        style="width:48%;text-align:center;padding: 4px 5px;font-weight: 700;">
                                        Payment Date</td>
                                    <td style="width:55%;padding: 4px 15px;color:rgb(84, 84, 84)">
                                        <?php echo "  ".date("m/d/Y",strtotime($transaction_date)); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="times-new"
                                        style="width:45%;padding:4px 32px;font-weight: 700;">
                                        Account No.</td>
                                    <td style="width:55%;padding: 4px 15px;color:rgb(84, 84, 84)">
                                        <?php if($student_id != 'undefined' && $student_id != ''): ?>
                                        <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?>
                                        <?php endif; ?> </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div style="margin-bottom:2px">
                    <table>
                        <thead>
                            <tr>
                                <th class="times-new" style="text-align:left;padding:2px 6px;">
                                    RECEIVED FROM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding-left: 15px;padding-top: 5px;">
                                    <div
                                        style="display: flex;align-items: flex-end;margin-bottom: 5px">
                                        <label class="times-new" style="flex-basis:105px">Registered
                                            Name<span style="position:relative;left:2;">
                                                :</span></label>
                                        <input type="text" style="padding-left: 4px"
                                            value="<?php echo $student_name; ?>" disabled>
                                    </div>
                                    <div
                                        style="display: flex;align-items: flex-end;margin-bottom: 5px">
                                        <label class="times-new" style="flex-basis:105px"> TIN <span
                                                style="position:relative;left:65"> :</span></label>
                                        <input type="text" style="padding-left: 4px"
                                            value="<?php echo $tin; ?>" disabled>
                                    </div>
                                    <div
                                        style="display: flex;align-items: flex-end;margin-bottom: 5px">
                                        <label class="times-new" style="flex-basis:105px"> Business
                                            Address<span style="position:relative"> :</span></label>
                                        <input type="text" style="padding-left: 4px"
                                            value="<?php echo $student_address; ?>" disabled>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div>
                    <table>
                        <thead>
                            <tr>
                                <th class="times-new" style="width: 574px;padding:4px 0">ITEM
                                    DESCRIPTION / NATURE OF SERVICE</th>
                                <th class="times-new">AMOUNT</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="times-new" style="padding:4px 0;padding-left:15px;">
                                    PAYMENT FOR <span
                                        style="position:relative;left:25;font-family: Arial, sans-serif;color:rgb(84, 84, 84)"><?php echo $type." /  ".$term['enumSem']." ".$term['term_label']." ".$term['strYearStart']."-".$term['strYearEnd']; ?></span>
                                </td>
                                <td style="text-align: center;padding:4px 0;color:rgb(84, 84, 84)">
                                    <?php echo $total_amount_due; ?></td>
                            </tr>
                            <tr>
                                <td style="padding:12px 0;">
                                </td>
                                <td style="text-align: center;padding:4px 0">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="width:288px;margin:2px;">
                    <table style="position:relative;left:436px">
                        <thead>
                            <tr>
                                <th class="times-new"
                                    style="width:120px;font-size:11px;padding: 10px 8px;">TOTAL PAID
                                    AMOUNT</th>
                                <th style="font-weight:normal;color:rgb(84, 84, 84);">
                                    <?php echo $total_amount_due; ?> </th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div style="display:flex;width:288px;position:relative;left:436px;margin-top:10px">
                    <label class="times-new" style="flex-basis:135px;font-size:13px">Invoice Ref.
                        No.</label>
                    <input type="text" style="text-align:center"
                        value="<?php echo $invoice_number; ?>" />
                </div>
                <div style="position:relative;left:438px;margin-top:12px">
                    <i class="times-new"
                        style="font-size: 11px;padding: 4px 15px;border: 2px solid rgb(140 140 140);">This
                        Document is not valid for claim of Input Tax.</i>
                </div>
            </div>
            <!-- PAYMENT REMARKS -->
            <div
                style="position:absolute; top: 100px; left: 350px; width: 200px; height: 20px;font-size:12px;">
                <?php echo $is_cash != 1? $remarks:""; ?> </div>
            <div
                style="position:absolute; top: 55px; left: 555px; width: 200px; height: 20px; font-size:12px">
                <?php echo $or_number; ?> </div>
            <div
                style="position:absolute; top: 355px; left: 325px; width: 200px; height: 20px; font-size:12px">
                <?php echo $cashier_name; ?> </div>
        </section>
    </div>
</body>