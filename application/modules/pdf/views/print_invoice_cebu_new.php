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
    border-collapse: collapse;
    border: 2px solid rgb(140 140 140);
    font-family: Arial, sans-serif;
}

#table-item {
    width: 100%;
}

#table-payment-form {
    width: 30%
}

th,
td {
    border: 1px solid black;
}

th,
tr,
td {
    font-weight: 400;
    font-size: 12px;
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

h1,
p {
    margin: 0;
    text-align: center;
}

ul>li {
    margin-bottom: 8px;
    font-size: 11px
}

hr {
    border-bottom-width: 0;
}

.item-description {
    width: 60%;
}

.quantity {
    width: 10%;
}

.unit-price {
    width: 15%;
}

.amount {
    width: 15%;
}

.text-center {
    text-align: center;
}

.italic-bold {
    font-style: italic;
    font-weight: 900;
}

.td-payment {
    padding: 3px 0px 3px 8px
}

.grid-container {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px 10px;
    margin-left: 10px;
}

.vat-value {
    font-size: 12px;
    margin-left: 10px;
}

.invoice {
    font-family: 'Times New Roman', Times, serif;
}
</style>

<body>
    <div class="sheet-outer A4">
        <section class="sheet padding-5mm">
            <div>
                <img style="position: absolute;width: 80; left: 145;top: 25;"
                    src="<?php echo $img_dir; ?>receipt-logo.jpg" alt="">
                <h1>iACADEMY, INC.</h1>
                <p><small>5th Floor Filinvest Cebu Cyberzone Tower Two, Salinas Drive cor. W.
                        Geonzon St.,</small></p>
                <p><small>Cebu IT Park, Apas 6000 City of Cebu, Cebu Philippines</small></p>
                <p><small>VAT REG TIN: 214-749-003-00003</small></p>
            </div>
            <div>
                <div style=" position:absolute; top: 140px; left: 50px; width: 200px; height:
                20px;font-size:1.6em;font-weight:800;">
                    <input type="checkbox" style="position:relative;left:24;" disabled>
                    <?php echo $cashCharge == true ? "&#10003;" : "&nbsp;&nbsp;";?> <span
                        style="font-size: 13px;font-weight: 400;position:relative;left:0">Cash
                        Sales</span>
                </div>
            </div>
            <div>
                <div
                    style="position:absolute; top: 140px; left: 162px; width: 200px; height: 20px;font-size:1.6em;font-weight:800;">
                    <input type="checkbox" style="position:relative;left:24;" disabled>
                    <?php echo $cashCharge != true ? "&#10003;" : "&nbsp;&nbsp;";?> <span
                        style="font-size: 13px;font-weight: 400;position:relative;left:0">Charge
                        Sales</span>
                </div>
            </div>
            <div style="position:absolute; top: 140px; left: 520px; width: 200px; height: 20px;">
                Invoice No: <?php echo $invoice_number; ?> </div>
            <div
                style="position: absolute;top: 100px;left: 647px;font-size: 19px;font-weight: bolder;">
                INVOICE </div>
            <div class="invoice"
                style="position:absolute; top: 135px; left: 640px; width: 200px; height: 20px;font-size: 17;font-weight: 600;">
                No. <span style="font-size:20px"> <?php echo $invoice_number; ?></span> </div>
            <!---DATE--->
            <div>
                <div
                    style="position:absolute; top: 175px; right: 145px;font-style: italic; font-size: 13px;">
                    Date:</div>
                <div
                    style="position:absolute; top: 175px; right: -60px; width: 200px; height: 20px;">
                    <?php echo $transaction_date; ?>
                    <hr style="margin:0; width:50%">
                </div>
            </div> <?php if(isset($payee)): ?>
            <!---TIN--->
            <div>
                <div
                    style="position:absolute; top: 200px; right: 145px;font-style: italic; font-size: 13px;">
                    TIN:</div>
                <div
                    style="position:absolute; top: 200px; right: -60px; width: 200px; height: 20px;">
                    <?php echo $payee['tin']; ?>
                    <hr style="margin:0; width:50%">
                </div>
            </div> <?php else: ?> <div>
                <div
                    style="position:absolute; top: 200px; right: 145px;font-style: italic; font-size: 13px;">
                    TIN:</div>
                <div
                    style="position:absolute; top: 213px; right: -60px; width: 200px; height: 20px;">
                    <hr style="margin:0; width:50%">
                </div>
            </div> <?php endif; ?>
            <!-- NAME -->
            <div>
                <div
                    style="position:absolute; top: 175px; left: 40px; font-style: italic; font-size: 13px;">
                    Sold to:</div>
                <div style="position:absolute; top: 175px; left: 85px; width: 500px; height: 20px;">
                    <?php if($student_id != 'undefined' && $student_id != ''): ?>
                    <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?> <?php endif; ?>
                    &nbsp; <?php echo $student_name; ?>
                    <hr style="margin:0;">
                </div>
            </div>
            <!-- ADDRESS -->
            <div>
                <div
                    style="position:absolute; top: 195px; left: 40px; font-style: italic; font-size: 13px;">
                    Address:</div>
                <div style="position:absolute; top: 197px; left: 90px; width: 500px; height: 20px;">
                    <?php echo $student_address; ?>
                    <hr style="margin:0;">
                </div>
            </div>
            <!-- TABLE start -->
            <div style="position:relative">
                <table id="table-item" style="position:absolute; top: 136">
                    <thead>
                        <tr>
                            <th class="item-description">ITEM DESCRIPTION</th>
                            <th class="quantity">Quantity</th>
                            <th class="unit-price">Unit Cost/Price</th>
                            <th class="amount">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding-left:28px"><?php echo $type; 
                 if(!isset($payee)): 
                    echo " /  ".$term['enumSem']." ".$term['term_label']." ".$term['strYearStart']."-".$term['strYearEnd'];
                endif; ?> </td>
                            <td class="text-center">1</td>
                            <td class="text-center"><?php echo $full_assessment; ?></td>
                            <td class="text-center"><?php echo $full_assessment; ?></td>
                        </tr> <?php if ($request['description'] == 'Reservation Payment' ): ?> <tr>
                            <td style="padding-left:28px">NON REFUNDABLE AND NON TRANSFERABLE</td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr> <?php else: ?> <tr>
                            <td style="padding: 8px"><?php echo $reservation_description; ?></td>
                            <td><?php echo $reservation_amount != 0 ? 1 : ""; ?> </td>
                            <td><?php echo $reservation_amount != 0 ? "-".$reservation_amount : ""; ?>
                            </td>
                            <td><?php echo $reservation_amount != 0 ? "-".$reservation_amount : ""; ?>
                            </td>
                        </tr> <?php endif; ?> <tr>
                            <?php if ($request['description'] == 'Reservation Payment' ): ?> <td
                                style="padding-left:28px"> NAME: </td> <?php else: ?> <td
                                style="padding: 8px"> </td> <?php endif; ?> <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr> <?php if ($request['description'] == 'Reservation Payment' ): ?> <td
                                style="padding-left:28px"> SIGNATURE: </td> <?php else: ?> <td
                                style="padding: 8px"> <?php echo $reservation_description; ?></td>
                            <?php endif; ?> <td>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!---Payment Left Amount-->
            <div style="position: relative; ">
                <table id="table-payment-form" style="position:absolute; top: 232">
                    <thead>
                        <tr>
                            <th colspan="3" style="padding: 4px">PAYMENT IN FORM OF:</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="td-payment" style="width:45%">Cash</td>
                            <td class="td-payment">
                                <?php  echo $is_cash == 1 ? $total_amount_due : ''; ?></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">Check</td>
                            <td class="td-payment">
                                <?php  echo $is_cash == 0 ? $total_amount_due : ''; ?></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">Bank</td>
                            <td></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">Check No.</td>
                            <td class="td-payment" style="max-width:71px;overflow-x:auto">
                                <?php  echo $is_cash == 0 &&  $request['type'] != 'ns_payment'? $remarks : ''; ?>
                            </td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">Others</td>
                            <td class="td-payment">
                                <?php  echo $is_cash == 2 || $is_cash == 3 ? $total_amount_due : ''; ?>
                            </td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%;max-width:87px;overflow-x:auto">
                                <?php  echo $is_cash != 0 ? $remarks : '&nbsp;'; ?>
                                <?php  echo $request['type'] == 'ns_payment'? $remarks : '&nbsp;'; ?>
                            </td>
                            <td></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">&nbsp;</td>
                            <td></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">&nbsp;</td>
                            <td></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">&nbsp;</td>
                            <td></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">&nbsp;</td>
                            <td></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <td class="td-payment" style="width:40%">&nbsp;</td>
                            <td></td>
                            <td style="width:20%"></td>
                        </tr>
                        <tr>
                            <!--Total Amount received-->
                            <td class="td-payment" style="width:40%">Total Payment</td>
                            <td class="td-payment">
                                <?php  echo $total_amount_due == 0 ? "" : $total_amount_due; ?></td>
                            <td style="width:20%"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div style="position: absolute;width: 64%;top: 336;left: 250;">
                <!-- TOTAL SALES TEST -->
                <div class="grid-container">
                    <ul style="padding:0; margin:0;">
                        <li style="position: relative;overflow-x: clip;">Total Sales (Vat Included)
                            <hr style="position: absolute;width: 100%;left: 125;top: 8;">
                        </li>
                        <li style="position: relative;overflow-x: clip;">Less VAT
                            <hr style="position: absolute;width: 100%;left: 47px;top: 8;">
                        </li>
                        <li style="position: relative;overflow-x: clip;">Amount Net of VAT
                            <hr style="position: absolute;width: 100%;left:92;top: 8;">
                        </li>
                        <li style="position: relative;overflow-x: clip;">Less SC/PWD Discount
                            <hr style="position: absolute;width: 100%;left: 117;top: 8;">
                        </li>
                        <li style="position: relative;overflow-x: clip;">Add VAT
                            <hr style="position: absolute;width: 100%;left: 44;top: 8;">
                        </li>
                        <li style="position: relative;overflow-x: clip;">Less Withholding Tax
                            <hr style="position: absolute;width: 100%;left: 104;top: 8;">
                        </li>
                        <li style="position: relative;overflow-x: clip;">Total Amount Due
                            <hr style="position: absolute;width: 100%;left: 87;top: 8;">
                        </li>
                        <!-- Vatable -->
                        <li style="position: relative;overflow-x: clip;">Vatable <span
                                style="font-size: 12px;margin-left: 10px;"><?php  echo $amount_less_vat != 0 ? $amount_less_vat : ""; ?></span>
                            <hr style="position: absolute;width: 100%;left: 37px;top: 8;">
                        </li>
                        <!-- Vat Exempt Sale -->
                        <li style="position: relative;overflow-x: clip;">Vat Exempt Sale <span
                                style="font-size: 12px;margin-left: 10px;">
                                <?php if($vat_exempt != 0 && $less_vat != 0): ?>
                                <?php  echo $vat_exempt; ?>
                                <?php elseif($vat_exempt == 0 && $less_vat != 0): ?>
                                <?php  echo ""; ?> <?php else: ?> <?php  echo $total_assessment; ?>
                                <?php endif; ?> </span>
                            <hr style="position: absolute;width: 100%;left: 82;top: 8;">
                        </li>
                    </ul>
                    <ul style="padding:0;margin:0;list-style-type: none;">
                        <!-- VAT ZERO RATED -->
                        <li style="position: relative;overflow-x: clip;">Vat Zero Rated Sale <span
                                style="font-size: 12px;margin-left: 10px;">
                                <?php echo $vat_zero_rated_sale != 0 ? $vat_zero_rated_sale : "" ; ?></span>
                            <hr style="position: absolute;width: 100%;left: 100;top: 8;">
                        </li>
                        <!-- TOTAL SALE -->
                        <li style="position: relative;overflow-x: clip;">Total Sale <span
                                style="font-size: 12px;margin-left: 10px;"><?php  echo $total_sale_taxed == 0 ? $total_assessment : $total_sale_taxed; ?></span>
                            <hr style="position: absolute;width: 100%;left: 50;top: 8;">
                        </li>
                        <!-- VAT -->
                        <li style="position: relative;overflow-x: clip;">Value Added tax <span
                                style="font-size: 12px;margin-left: 10px;"><?php echo $less_vat != 0 ? $less_vat : "" ; ?></span>
                            <hr style="position: absolute;width: 100%;left: 80;top: 8;">
                        </li>
                        <!-- LESS EWT -->
                        <li style="position: relative;overflow-x: clip;">Less EWT <span
                                style="font-size: 12px;margin-left: 10px;"><?php echo $less_ewt != 0 ? $less_ewt : "" ; ?></span>
                            <hr style="position: absolute;width: 100%;left: 51;top: 8;">
                        </li>
                        <!-- TOTAL PAYMENT  -->
                        <li style="position: relative;overflow-x: clip;">Total Payment <span
                                style="font-size: 12px;margin-left: 10px;"><?php  echo $total_assessment; ?></span>
                            <hr style="position: absolute;width: 100%;left: 70;top: 8;">
                        </li>
                        <li class="italic-bold" style="position: relative;overflow-x: clip;">OSCA /
                            PWD ID NO.
                            <hr style="position: absolute;width: 100%;left: 100;top: 8;">
                        </li>
                        <li class="italic-bold" style="position: relative;overflow-x: clip;">SC /
                            PWD TIN:
                            <hr style="position: absolute;width: 100%;left: 72;top: 8;">
                        </li>
                        <li class="italic-bold" style="position: relative;overflow-x: clip;">Solo
                            Parent I.D No.
                            <hr style="position: absolute;width: 100%;left: 92;top: 8;">
                        </li>
                        <li class="italic-bold" style="position: relative;overflow-x: clip;">SC /
                            PWD SIGNATURE
                            <hr style="position: absolute;width: 100%;left: 118;top: 8;">
                        </li>
                        <li style="font-weight: bold;text-align:center">iACADEMY, Inc.</li>
                    </ul>
                    <div></div>
                    <div style="text-align: center;"><span style="font-size:15px">
                            <?php echo $cashier_name; ?> </span>
                        <hr style="margin:0">
                        <i>Authorized Signature</i>
                    </div>
                </div>
            </div>
            <!-- <div style="position:absolute; top: 537px; left: 608px;font-weight: bold;">iACADEMY,
                Inc.</div> -->
            <!-- <div
                style="position:absolute; top: 565px; left: 600px; width: 200px; height: 20px;font-size:15px">
                <span><?php echo $cashier_name; ?> </span>
            </div>
            <hr style="position:absolute; top: 575px; left: 514px; width: 252px;">
            <i style="position:absolute; top: 585px; left: 594px;">Authorized Signature</i> -->
        </section>
    </div>
</body>