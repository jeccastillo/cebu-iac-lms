<table>
    <tr style="text-align:center;">
        <th colspan="3" style="border:1px solid #555;"><strong>SUMMARY DISTRIBUTION</strong></th>
    </tr>
    <tr style="text-align:center;">
        <th style="border:1px solid #555;">Grading Equivalent</th>
        <th style="border:1px solid #555;">Number of Students</th>
        <th style="border:1px solid #555;">Percentage</th>
    </tr>
    <tr style="text-align:center;">
        <td style="border:1px solid #555;">1.00 - 1.75</td>
        <td style="border:1px solid #555;"><?php echo $lineOfOne; ?></td>
        <td style="border:1px solid #555;">
            <?php 
                $lineOfOnePE = $total == 0 ? '0' : round(($lineOfOne / $total) * 100, 2); 
                echo $lineOfOnePE . " %"; 
            ?>
        </td>

    </tr>
    <tr style="text-align:center;">
        <td style="border:1px solid #555;">2.00 - 2.75</td>
        <td style="border:1px solid #555;"><?php echo $lineOfTwo; ?></td>
        <td style="border:1px solid #555;">
            <?php $lineOfTwoPE = $total == 0 ? '0' : round(($lineOfTwo / $total) * 100, 2); 
                echo $lineOfTwoPE . " %"; 
            ?>
        </td>
    </tr>
    <tr style="text-align:center;">
        <td style="border:1px solid #555;">3.00</td>
        <td style="border:1px solid #555;"><?php echo $lineOfThree; ?></td>
        <td style="border:1px solid #555;">
            <?php $lineOfThreePE = $total == 0 ? '0' : round(($lineOfThree / $total) * 100, 2); 
                echo $lineOfThreePE . " %"; 
            ?>
        </td>
    </tr>
    <tr style="text-align:center;">
        <td style="border:1px solid #555;">5.00</td>
        <td style="border:1px solid #555;"><?php echo $totalFailed; ?></td>
        <td style="border:1px solid #555;">
            <?php $lineOfFivePE = $total == 0 ? '0' : round(($totalFailed / $total) * 100, 2); 
                echo $lineOfFivePE . " %"; 
            ?>

        </td>
    </tr>

    <tr style="text-align:center;">
        <td style="border:1px solid #555;">INCOMPLETE</td>
        <td style="border:1px solid #555;"><?php echo $incomplete; ?></td>
        <td style="border:1px solid #555;">
            <?php $incPE = $total == 0 ? '0' : round(($incomplete / $total) * 100, 2); 
                echo $incPE . " %"; 
            ?>
        </td>
    </tr>
    <tr style="text-align:center;">
        <td style="border:1px solid #555;">UNOFFICIALLY DROPPED</td>
        <td style="border:1px solid #555;"><?php echo $totalUD; ?></td>
        <td style="border:1px solid #555;">
            <?php $udPE = $total == 0 ? '0' : round(($totalUD / $total) * 100, 2); 
                echo $udPE . " %"; 
            ?>
        </td>
    </tr>
    <tr style="text-align:center;">
        <td style="border:1px solid #555;">OFFICIALLY DROPPED</td>
        <td style="border:1px solid #555;"><?php echo $od; ?></td>
        <td style="border:1px solid #555;">
            <?php $odPE = $total == 0 ? '0' : round(($od / $total) * 100, 2); 
                echo $odPE . " %"; 
            ?>
        </td>
    </tr>
    <tr style="text-align:center;">
        <td style="border:1px solid #555;"><strong>TOTAL</strong></td>
        <td style="border:1px solid #555;">
            <?php 
                echo $total; 
            ?></td>
        <td style="border:1px solid #555;">
        <?php echo round(($lineOfOnePE + $lineOfTwoPE + $lineOfThreePE + $lineOfFivePE + $incPE + $udPE + $odPE), 0) . "%"; ?>
        </td>
    </tr>
</table>
<table>
    <tr style="line-height:30px;">
        <td></td>
    </tr>
</table>
<table>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center">Prepared By:<br /><br /><?php echo $faculty['strFirstname']." ".$faculty['strLastname']; ?><br />Instructor </td>
    </tr>
     <tr style="line-height:30px;">
        <td></td>
    </tr>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center">Checked By: <br /><br /><?php echo strtoupper($classlist['strSignatory1Name']); ?><br /><?php echo $classlist['strSignatory1Title']; ?></td>
    </tr>
    <tr style="line-height:30px;">
        <td></td>
    </tr>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center">Noted By: <br /><br /><?php echo strtoupper($classlist['strSignatory2Name']); ?><br /><?php echo $classlist['strSignatory2Title']; ?></td>
    </tr>
    <tr style="line-height:30px;">
        <td></td>
    </tr>
    <tr style="line-height:20px;">
        <td style="font-size:10px;text-align:center">Date Prepared:<br /><?php echo date("M j, Y"); ?></td>
    </tr>
     
</table>