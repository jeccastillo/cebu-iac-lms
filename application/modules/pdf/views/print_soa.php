<table border="0" cellspacing="0" cellpadding="0" style="color:#333;">   
    <tr style="font-weight:bold;">        
        <td width="100%" align="center" style="text-align:center;vertical-align: middle;">
        
        <?php if($campus == 'Makati'): ?>   
            <img src= "https://i.ibb.co/XW1DRVT/iacademy-logo.png"  width="150" height="44"/>
        <?php elseif($campus == 'Cebu'): ?>
            <img src= "https://i.ibb.co/9hgbYNB/seal.png"  width="100" height="100"/>
        <?php endif; ?>
        </td>
    </tr><br>
    <tr>
        <td width="100%" align="center" style="text-align:center;vertical-align: middle; font-size:14px; font-weight:bold">
            INFORMATION & COMMUNICATIONS TECHNOLOGY ACADEMY
        </td>
    </tr>
    <tr>
        <td width="100%" align="center" style="text-align:center;vertical-align: middle; font-size:11px">
            <?php 
                if($campus == 'Makati'){
                    echo '7434 Yakal Street Brgy. San Antonio, Makati City';
                }else if($campus == 'Cebu'){
                    echo '5F Filinvest Cyberzone Tower 2 Salinas Drive Cor. W. Geonzon St., Cebu IT Park, Apas, Cebu City';
                }
            ?>
        </td>
    </tr><br><br>
    <tr style="font-size:12px; font-weight:bold; text-align:center;">
        <td width="100%" align="center" style="text-align:center;vertical-align: middle;">
            SUBJECTS ENLISTED
        </td>
    </tr>
    <tr style="font-size:12px; font-weight:bold; text-align:center;">
        <td width="100%" align="center" style="text-align:center;vertical-align: middle;">
            <?php echo 'A.Y. ' . $sy->strYearStart . '-' . $sy->strYearEnd . ', ' . $sy->enumSem . ' ' . $this->data["term_type"]; ?>
        </td>
    </tr>
</table>
<br><br><br>
<table>

    <tr style="font-size:12px; text-align:left;">
        <td width="30%">NAME OF STUDENT: </td>
        <td><?php echo $student['strLastName'] . ', ' . $student['strFirstName'] . ' ' . $student['strMiddleName']; ?></td>
    </tr>
    <tr style="font-size:12px; text-align:left;">
        <td width="30%">ID NO:</td>
        <td><?php echo str_replace("-", "", $student['strStudentNumber']); ?></td>
    </tr>
    <tr style="font-size:12px; text-align:left;">
        <td width="30%">COURSE:</td>
        <td><?php echo $course['strProgramCode']; ?></td>
    </tr>
</table>
<br><br>
<span style="font-size:11px">Please see attached Statement of Account for <?php echo $sy->enumSem . ' ' . $this->data["term_type"];?>.</span>
<br><br>


<table>
<?php 
    if($reg['paymentType'] == 'partial') :
        if($request['is_paid_dp'] == '0') :
?>
    <tr>
        <td style="border:1px solid #333;" width="50%"> Down Payment</td>
        <td style="border:1px solid #333;text-align:right;padding-right:5px" width="20%"><?php echo $request['down_payment'] ?> </td>
    </tr>
    <?php endif; ?>
    
    <tr>
        <td style="border:1px solid #333;" width="50%"> 1st installment balance due <?php echo date("F d, Y", strtotime($sy->installment1)) ?></td>
        <td style="border:1px solid #333;text-align:right;padding-right:5px" width="20%"><?php echo number_format($installments[0], 2, '.', ',') ?> </td>
    </tr>
    <tr>
        <td style="border:1px solid #333;" width="50%"> 2nd installment balance due <?php echo date("F d, Y", strtotime($sy->installment2)) ?></td>
        <td style="border:1px solid #333;text-align:right;padding-right:5px" width="20%"><?php echo number_format($installments[1], 2, '.', ',') ?> </td>
    </tr>
    <tr>
        <td style="border:1px solid #333;" width="50%"> 3rd installment balance due <?php echo date("F d, Y", strtotime($sy->installment3)) ?></td>
        <td style="border:1px solid #333;text-align:right;padding-right:5px" width="20%"><?php echo number_format($installments[2], 2, '.', ',') ?> </td>
    </tr>
    <tr>
        <td style="border:1px solid #333;" width="50%"> 4th installment balance due <?php echo date("F d, Y", strtotime($sy->installment4)) ?></td>
        <td style="border:1px solid #333;text-align:right;padding-right:5px" width="20%"><?php echo number_format($installments[3], 2, '.', ',') ?> </td>
    </tr>
    <tr>
        <td style="border:1px solid #333;" width="50%"> 5th installment balance due <?php echo date("F d, Y", strtotime($sy->installment5)) ?></td>
        <td style="border:1px solid #333;text-align:right;padding-right:5px" width="20%"><?php echo number_format($installments[4], 2, '.', ',') ?> </td>
    </tr>
    <tr>
        <td style="border:1px solid #333;font-weight:bold" width="50%">TOTAL BALANCE</td>
        <td style="border:1px solid #333;font-weight:bold;text-align:right;padding-right:5px" width="20%"><?php echo $request['total'] ?> </td>
    </tr>
    <tr>
        <td style="border:1px solid #333;font-size:10px" colspan="2"><i>Please note that rounding off may cause some minor disparity in the installment schedule</i></td>
    </tr>
<?php elseif($reg['paymentType'] == 'full') :?>
    <tr>
        <td style="border:1px solid #333;font-weight:bold" width="50%">TOTAL BALANCE</td>
        <td style="border:1px solid #333;font-weight:bold;text-align:right;padding-right:5px" width="20%"><?php echo $request['total'] ?> </td>
    </tr>
    <tr>
        <td style="border:1px solid #333;font-size:10px" colspan="2"><i>Please note that rounding off may cause some minor disparity in the installment schedule</i></td>
    </tr>
<?php endif; ?>
</table>

<br><br><br>
Thank you.
<br><br><br>
Prepared by:
<br><br>
<b><?php echo $user['strLastname'] . ', ' . $user['strFirstname'] . ' ' . $user['strMiddlename'];?></b>

<br><br><br>
<span style="font-size:11px"><i>Note: If you have any question about your Statement of Account, please call Finance 
<?php 
    if($campus == 'Makati'){
        echo 'or send an email at finance@iacademy.edu.ph';
    }else if($campus == 'Cebu'){
        echo '@ 520 4888 local 3114 or send an email at financecebu@iacademy.edu.ph';
    }
?>
</i></span>