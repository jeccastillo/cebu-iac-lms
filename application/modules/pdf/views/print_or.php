<table border="0">
    <tr style="line-height:78px;">
        <td style=""></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:3px;">
        <td style=""></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:38px;">
        <td style=""></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:33px;">
        <td style="font-size:9px;text-align:left;color:#666;"><?php echo "  ".date("M j, Y",strtotime($transactions[0]['dtePaid'])); ?></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:3px;">
        <td style=""></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:55px;">
        <td style="font-size:9px;text-align:left;color:#666;"><?php echo "  ".$student['strLastname'].', '.$student['strFirstname']; ?></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:6px;">
        <td style=""></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:20px;">
        <td style=""></td>
    </tr>
</table>
<?php 
    $total = 0;
    for($i=0;$i<10;$i++): 
    if(isset($transactions[$i]['intAmountPaid']))
        $total+=$transactions[$i]['intAmountPaid'];
?>
<table border="0">
    <tr style="line-height:15px;">
        <td width="50%" style="font-size:9px;color:#666;"><?php echo isset($transactions[$i]['strTransactionType'])?"  ".$transactions[$i]['strTransactionType']:''; ?></td>
        <td width="20%" style=""></td>
        <td width="30%" style="font-size:9px;color:#666;text-align:center;"><?php echo isset($transactions[$i]['intAmountPaid'])?"  ".$transactions[$i]['intAmountPaid']:''; ?></td>
    </tr>
</table>
<?php endfor; ?>
<table border="0">
    <tr style="line-height:6px;">
        <td width="50%" style=""></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:14px;">
        <td width="50%" style=""></td>
        <td width="20%" style=""></td>
        <td width="30%" style="font-size:9px;color:#666;text-align:center;"><?php echo $total; ?></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:17px;">
        <td width="50%" style=""></td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:21px;">
       <td style="font-size:9px;color:#666;"><?php echo convert_number($total); ?> only</td>
    </tr>
</table>
<table border="0">
    <tr style="line-height:6px;">
        <td style="font-size:9px;color:#666;"></td>
    </tr>
</table>
