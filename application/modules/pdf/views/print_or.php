<table >
    <tr style="line-height:10px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:27px;">
        <td style=""><?php //echo $or_number; ?></td>
    </tr>
</table>
<table>
    <tr>        
        <td style="width:30%">
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:9px;text-align:left;color:#666;">
                    <td style="width:50%;height:200px;"><?php echo $description; ?> <?php echo $description == "Application Payment" ? "<br />Non-Refundable":""; ?></td>
                    <td style="width:50%">P<?php echo number_format($total_amount_due,2,'.',','); ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:9px;text-align:left;color:#666;">
                    <td style="width:50%"></td>
                    <td style="width:50%"><?php echo $is_cash?"cash":""; ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:9px;text-align:left;color:#666;">
                    <td style="width:50%"></td>
                    <td style="width:50%"><?php echo !$is_cash?"check":""; ?></td>
                </tr>
            </table>
        </td>
        <td style="width:70%">
            <table>
                <tr style="line-height:25px;">
                    <td style=""><?php //echo $or_number; ?></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:20px;">
                    <td style="font-size:9px;text-align:right;color:#666;"><?php echo "  ".date("M j, Y",strtotime($transaction_date)); ?></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:9px;text-align:left;color:#666;">
                        <span style="color:#fff;">RECEIVED from &nbsp;</span>
                        <?php echo $student_id." ".$student_name; ?>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:9px;text-align:left;color:#666;">
                    <span style="color:#fff;">Address &nbsp;</span>
                    <?php echo $student_address; ?></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:9px;text-align:left;color:#666;"></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;color:#666;">
                        <span style="color:#fff;">the amount of pesos &nbsp;</span>
                        <?php echo convert_number($total_amount_due); ?> <?php echo $decimal?'and '.convert_number($decimal).' cents':'only'; ?>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">
                    <td style="width:80%"></td>
                    <td style="width:20%;font-size:10px;text-align:left;color:#666;">
                        <?php echo number_format($total_amount_due,2,'.',','); ?>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;color:#666;">
                        <span style="color:#fff;">as full/partial payment of &nbsp;</span>
                        <?php echo $description; ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table >
    <tr style="line-height:12px;">
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
        <td width="50%" style="font-size:9px;color:#666;"></td>
        <td width="20%" style=""></td>
        <td width="30%" style="font-size:9px;color:#666;text-align:center;"></td>
    </tr>
</table>
<table >
    <tr style="line-height:6px;">
        <td width="50%" style=""></td>
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
