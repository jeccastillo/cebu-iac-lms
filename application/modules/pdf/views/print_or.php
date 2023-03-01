<table >
    <tr style="line-height:10px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:45px;">
        <td style=""><?php //echo $or_number; ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:20px;">
        <td style="font-size:9px;text-align:right;color:#666;"><?php echo "  ".date("M j, Y",strtotime($transaction_date)); ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:12px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:15px;">
        <td style="width:43%"></td>
        <td style="width:57%;font-size:9px;text-align:left;color:#666;"><?php echo $student_id." ".$student_name; ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:5px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:15px;">
        <td style="width:40%"></td>
        <td style="width:60%;font-size:9px;text-align:left;color:#666;"><?php echo $student_address; ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:5px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:15px;">
        <td style="width:40%"></td>
        <td style="width:60%;font-size:9px;text-align:left;color:#666;"></td>
    </tr>
</table>
<table >
    <tr style="line-height:5px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:15px;">
        <td style="width:30%"></td>
        <td style="width:70%;font-size:9px;text-align:left;color:#666;">
            <span style="color:#fff;">the amount of pesos</span>
            <?php echo convert_number($total_amount_due); ?> <?php echo $decimal?'and '.convert_number($decimal).' cents':'only'; ?>
        </td>
    </tr>
</table>
<table >
    <tr style="line-height:6px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:55px;">
        <td style="font-size:9px;text-align:left;color:#666;"><?php echo $student_address; ?></td>
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
        <td width="30%" style="font-size:9px;color:#666;text-align:center;">Php<?php echo number_format($total_amount_due,2,'.',','); ?></td>
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
        <td width="30%" style="font-size:9px;color:#666;text-align:center;">Php<?php echo number_format($total_amount_due,2,'.',','); ?></td>
    </tr>
</table>
<table >
    <tr style="line-height:17px;">
        <td width="50%" style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:21px;">
       <td style="font-size:9px;color:#666;"></td>
    </tr>
</table>
<table >
    <tr style="line-height:6px;">
        <td style="font-size:9px;color:#666;"></td>
    </tr>
</table>
