<style>
body {
    margin: 0;
    font-family: Arial, Sans-Serif;
    font-size: 12px;
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
    border-collapse: collapse;
    margin-bottom: 10px;
}

table, th, td {
    border: 1px solid #000;
}

th, td {
    padding: 5px;
    text-align: left;
    vertical-align: top;
    font-size: 10px;
}

th {
    background-color: #f0f0f0;
    font-weight: bold;
    text-align: center;
}

/* .student-info {
    margin-bottom: 15px;
} */

.student-info table {
    border: none;
}

.student-info td {
    border: none;
    padding: 2px 5px;
}

.amount {
    text-align: right;
}

.total-row {
    font-weight: bold;
    background-color: #f5f5f5;
}

.section-header {
    background-color: #d0d0d0;
    font-weight: bold;
    text-align: center;
}

@media screen {
    body {
        background: #e0e0e0
    }

    /* .sheet {
        background: white;
        box-shadow: 0 .5mm 2mm rgba(0, 0, 0, .3);
        margin: 5mm auto;
        width: 210mm;
        min-height: 296mm;
    } */
}

@page {
    size: A4;
    margin: 0;
}

@media print {
    .sheet {
        width: 210mm;
        min-height: 296mm;
    }
}
</style>

<body>
    <div class="sheet-outer">
        <section class="sheet">
            <!-- Header -->
            <div style="text-align:center">
                <h3>iACADEMY Inc.</h3>
                <h5><?php echo $campus_address; ?></h5>
            </div>

            <!-- Student Information -->
            <div class="student-info">
                <table>
                    <tr>
                        <td><strong>Student Name:</strong></td>
                        <td><?php echo strtoupper($student['strLastname'] . ', ' . $student['strFirstname'] . ' ' . $student['strMiddlename']); ?></td>
                        <td></td>
                        <td><strong>Student Number:</strong></td>
                        <td><?php echo $student['strStudentNumber']; ?></td>
                    </tr>
                    <tr>
                        <td><strong>Program:</strong></td>
                        <td><?php echo $student['strProgramDescription']; ?></td>
                    </tr>
                </table>
            </div>

            <?php if(!empty($ledger)): ?>
                <?php foreach($ledger as $term_data): ?>
                    <?php if(!empty($term_data['ledger_items'])): ?>
                        
                        <!-- Tuition Section -->
                        <table>
                            <thead>
                                <tr class="section-header">
                                    <th colspan="13">Tuition</th>
                                </tr>
                                <tr>
                                    <th style="border: 1px solid #000;">School Year</th>
                                    <th style="border: 1px solid #000;">Term/Semester</th>
                                    <th style="border: 1px solid #000;">Scholarship</th>
                                    <th style="border: 1px solid #000;">Payment Description</th>
                                    <th style="border: 1px solid #000;">O.R. Date</th>
                                    <th style="border: 1px solid #000;">O.R. Number</th>
                                    <th style="border: 1px solid #000;">Invoice Number</th>
                                    <th style="border: 1px solid #000;">Remarks</th>
                                    <th style="border: 1px solid #000;">Assessment</th>
                                    <th style="border: 1px solid #000;">Payment</th>
                                    <th style="border: 1px solid #000;">Balance</th>
                                    <th style="border: 1px solid #000;">Added/Changed By</th>
                                    <th style="border: 1px solid #000;">Cashier/Appointer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($term_data['ledger_items'] as $index => $item): ?>
                                    <tr>
                                        <td style="border: 1px solid #000;"><?php echo $item['strYearStart'] . ' - ' . $item['strYearEnd']; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['enumSem'] . ' ' . $item['term_label']; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['scholarship_name'] ?: ''; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['name']; ?></td>
                                        <td style="border: 1px solid #000;"><?php 
                                            // O.R. Date can be null for first item (Tuition assessment)
                                            if($index == 0) {
                                                echo '';
                                            } else {
                                                echo $item['date'] ?: '';
                                            }
                                        ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['or_number'] ?: ''; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['invoice_number'] ?: ''; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['remarks'] ?: ''; ?></td>
                                        <td style="border: 1px solid #000;" class="amount"><?php 
                                            // Assessment column: can be null for first item, use conditions for others
                                            if($index == 0) {
                                                echo $item['amount'] ?: '';
                                            } else {
                                                $amount_val = floatval(str_replace(',', '', $item['amount']));
                                                echo (($item['type'] != 'payment' && $item['type'] != 'balance') || ($item['type'] == 'balance' && $amount_val >= 0)) ?  number_format($item['amount'], 2) : '-';
                                            }
                                        ?></td>
                                        <td style="border: 1px solid #000;" class="amount"><?php 
                                            // Payment column: can be null for first item, use conditions for others
                                            if($index == 0) {
                                                echo '';
                                            } else {
                                                $amount_val = floatval(str_replace(',', '', $item['amount']));
                                                if($item['type'] == 'payment' || ($item['type'] == 'balance' && $amount_val < 0)) {
                                                    echo ($amount_val < 0) ? number_format($amount_val * -1, 2) :  number_format($item['amount'], 2);
                                                } else {
                                                    echo '-';
                                                }
                                            }
                                        ?></td>
                                        <td style="border: 1px solid #000;" class="amount"><?php 
                                            // Balance column: item.balance < 0 ?"(" + numberWithCommas(item.balance * -1) + ")": numberWithCommas(item.balance)
                                            $balance_val = floatval(str_replace(',', '', $item['balance']));
                                            echo ($balance_val < 0) ? '(' . number_format($balance_val * -1, 2) . ')' :  number_format($item['balance'], 2);
                                        ?></td>
                                        <td style="border: 1px solid #000;"><?php echo ($item['added_by'] != 0) ? 'Manually Generated' : 'System Generated'; ?></td>
                                        <td style="border: 1px solid #000;"><?php 
                                            // Cashier can be null for first item (Tuition assessment)
                                            if($index == 0) {
                                                echo '';
                                            } else {
                                                if($item['added_by'] == 0) {
                                                    echo isset($cashier_names[$item['cashier']]) ? $cashier_names[$item['cashier']] : $item['cashier'];
                                                } else {
                                                    echo isset($cashier_names[$item['added_by']]) ? $cashier_names[$item['added_by']] : $item['added_by'];
                                                }
                                            }
                                        ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="11" class="amount"><strong>Term Balance/Refund: <?php echo number_format($term_data['balance'], 2); ?></strong></td>
                                    <td colspan="3"></td>
                                </tr>
                            </tbody>
                        </table>

                    <?php endif; ?>
                <?php endforeach; ?>
                
                <!-- Grand Total -->
                <table style="margin-top: 15px;">
                    <tr class="total-row">
                        <td colspan="11" class="amount"><strong>Grand Total Balance/Refund: <?php echo number_format($running_balance, 2); ?></strong></td>
                        <td colspan="2"></td>
                    </tr>
                </table>
            <?php endif; ?>
            <br><br>

            <?php if(!empty($other)): ?>
                <?php foreach($other as $term_data): ?>
                    <?php if(!empty($term_data['ledger_items'])): ?>
                        
                        <!-- Other Section -->
                        <table style="margin-top: 15px;">
                            <thead>
                                <tr class="section-header">
                                    <th colspan="11">Other</th>
                                </tr>
                                <tr>
                                    <th style="border: 1px solid #000;">School Year</th>
                                    <th style="border: 1px solid #000;">Term/Semester</th>
                                    <th style="border: 1px solid #000;">Payment Description</th>
                                    <th style="border: 1px solid #000;">O.R. Date</th>
                                    <th style="border: 1px solid #000;">O.R. Number</th>
                                    <th style="border: 1px solid #000;">Invoice Number</th>
                                    <th style="border: 1px solid #000;">Remarks</th>
                                    <th style="border: 1px solid #000;">Assessment</th>
                                    <th style="border: 1px solid #000;">Payment</th>
                                    <th style="border: 1px solid #000;">Added/Changed By</th>
                                    <th style="border: 1px solid #000;">Cashier</th>
                                    <th style="border: 1px solid #000;">Delete</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($term_data['ledger_items'] as $item): ?>
                                    <tr>
                                        <td style="border: 1px solid #000;"><?php echo $item['strYearStart'] . ' - ' . $item['strYearEnd']; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['enumSem'] . ' ' . $item['term_label']; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['name']; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['date']; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['or_number'] ?: ''; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['invoice_number'] ?: ''; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo $item['remarks'] ?: ''; ?></td>
                                        <td style="border: 1px solid #000;" class="amount"><?php echo ($item['type'] != 'payment') ? $item['amount'] : '-'; ?></td>
                                        <td style="border: 1px solid #000;" class="amount"><?php echo ($item['type'] == 'payment') ? $item['amount'] : '-'; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo ($item['added_by'] != 0) ? ($item['strLastname'] . ' ' . $item['strFirstname']) : 'System Generated'; ?></td>
                                        <td style="border: 1px solid #000;"><?php echo isset($cashier_names[$item['cashier']]) ? $cashier_names[$item['cashier']] : $item['cashier']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if(empty($ledger) && empty($other)): ?>
                <p style="text-align: center; margin-top: 50px; font-style: italic;">No ledger data available for this student.</p>
            <?php endif; ?>

            <!-- Footer -->
            <div style="margin-top: 30px; text-align: center; font-size: 10px;">
                <p>Generated on <?php echo date('F j, Y g:i A'); ?></p>
            </div>
        </section>
    </div>
</body>
