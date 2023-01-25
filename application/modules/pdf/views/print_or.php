<table >
    <tr style="line-height:78px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:3px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:38px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:33px;">
        <td style="font-size:9px;text-align:left;color:#666;"><?php echo "  ".date("M j, Y",strtotime($transaction_date)); ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:3px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:55px;">
        <td style="font-size:9px;text-align:left;color:#666;"><?php echo "  ".$student['strLastname'].', '.$student['strFirstname']; ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:6px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:20px;">
        <td style=""></td>
    </tr>
</table>
<?php 
   
?>
<table >
    <tr style="line-height:15px;">
        <td width="50%" style="font-size:9px;color:#666;"><?php echo $description; ?></td>
        <td width="20%" style=""></td>
        <td width="30%" style="font-size:9px;color:#666;text-align:center;"><?php echo $total_amount_due; ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:6px;">
        <td width="50%" style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:14px;">
        <td width="50%" style=""></td>
        <td width="20%" style=""></td>
        <td width="30%" style="font-size:9px;color:#666;text-align:center;"><?php echo $total_amount_due; ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:17px;">
        <td width="50%" style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:21px;">
       <td style="font-size:9px;color:#666;"><?php echo convert_number($total_amount_due); ?> only</td>
    </tr>
</table>
<table >
    <tr style="line-height:6px;">
        <td style="font-size:9px;color:#666;"></td>
    </tr>
</table>
