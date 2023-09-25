<table >
    <tr style="line-height:10px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:38px;">
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
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:3%"></td>
                    <td style="width:47%;height:195px;font-size:9px;"><?php echo $description; ?> <?php echo $description == "Reservation Payment" ? "<br />NON REFUNDABLE AND NON <br />TRANSFERABLE":""; ?></td>
                    <td style="width:50%"><?php echo number_format($total_amount_due,2,'.',','); ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:3%"></td>
                    <td style="width:47%"></td>
                    <td style="width:50%"><?php echo $is_cash == 1?"yes":""; ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:3%"></td>
                    <td style="width:47%"></td>
                    <td style="width:50%"><?php echo $is_cash == 0?"yes":""; ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;;">
                    <td style="width:3%"></td>
                    <td style="width:47%"></td>
                    <td style="width:50%"><?php echo ($is_cash == 2 || $is_cash == 3)?"yes":""; ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;;">
                    <td style="width:3%"></td>
                    <td style="width:47%"></td>
                    <td style="width:50%"></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:3%"></td>
                    <td style="width:47%"></td>
                    <td style="width:50%"><?php 
                        if($remarks == "Paynamics")
                            echo "Paynamics";
                        else
                            echo !$is_cash?$check_number:""; 
                        
                    
                    ?></td>
                </tr>
            </table>   
            <!-- <table>
                <tr style="line-height:3px;">
                    <td style=""></td>
                </tr>
            </table>                             -->
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:50%"></td>
                    <td style="width:50%">P<?php echo number_format($total_amount_due,2,'.',','); ?></td>
                </tr>
            </table>
        </td>
        <td style="width:70%">
            <table>
                <tr style="line-height:18px;">
                    <td style="text-align:right;"><?php echo $or_number; ?></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">
                    <td style="font-size:10px;text-align:right;padding-right:15px;"><?php echo "  ".date("m/d/y",strtotime($transaction_date)); ?></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:10px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;">
                        <span style="color:#fff;">RECEIVED fr</span>                        
                        <?php if($student_id != 'undefined' && $student_id != ""): ?>
                        <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?> <?php echo $student_name; ?>                        
                        <?php else: ?>
                        <?php echo $student_name; ?>                        
                        <?php endif; ?>
                        
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <?php
                $text = $student_address;

                $splitstring1 = substr($text, 0, floor(strlen($text) / 2));
                $splitstring2 = substr($text, floor(strlen($text) / 2));
                
                if (substr($splitstring1, 0, -1) != ' ' AND substr($splitstring2, 0, 1) != ' ')
                {
                    $middle = strlen($splitstring1) + strpos($splitstring2, ' ') + 1;
                }
                else
                {
                    $middle = strrpos(substr($text, 0, floor(strlen($text) / 2)), ' ') + 1;    
                }
                
                $string1 = substr($text, 0, $middle);  // "The Quick : Brown Fox Jumped "
                $string2 = substr($text, $middle);  // "Over The Lazy / Dog"
            ?>
            <table>
                <tr style="line-height:15px;">                    
                    <td style="font-size:9px;text-align:left;">
                    <span style="color:#fff;">Address &nbsp;</span><?php echo $string1; ?><br />
                    <span style="color:#fff;">Address &nbsp;</span><?php echo $string2; ?>
                </td>
                </tr>
            </table>
            <!-- <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table> -->
            <table >
                <tr style="line-height:5px;">                    
                    <td style="font-size:10px;text-align:left;"></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:17px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;">
                        <span style="color:#fff;">the amount of pesos &nbsp;</span>
                        <?php echo convert_number($total_amount_due); ?> <?php echo $decimal?'and '.convert_number($decimal).' cents':'only'; ?>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:8px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">
                    <td style="width:85%"></td>
                    <td style="width:15%;font-size:10px;text-align:left;">
                        <?php echo number_format($total_amount_due,2,'.',','); ?>
                    </td>
                </tr>
            </table>
            <!-- <table >
                <tr style="line-height:10px;">
                    <td style=""></td>
                </tr>
            </table> -->
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;">
                        <span style="color:#fff;">as full/partial payment of &nbsp;</span>
                        <?php echo $description; ?>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:10px;">
                    <td style="width:60%"></td>
                    <td style="width:40%;font-size:8px;text-align:center;">                        
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
                    <td style="width:70%"></td>
                    <td style="width:30%;font-size:10px;text-align:center;">
                    <?php echo number_format($total_amount_due,2,'.',','); ?>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:10px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style="width:60%"></td>
                    <td style="width:40%;font-size:8px;text-align:center;">
                        
                    </td>
                </tr>
            </table>
            <!-- <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table> -->
            <table >
                <tr style="line-height:15px;">
                    <td style="width:57%"></td>
                    <td style="width:43%;font-size:10px;text-align:center;">
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
                <tr style="line-height:10px;">
                    <td style="width:60%"></td>
                    <td style="width:40%;font-size:8px;text-align:center;">
                        
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <!-- <table >
                <tr style="line-height:10px;">
                    <td style="width:60%"></td>
                    <td style="width:40%;font-size:8px;text-align:center;">
                        
                    </td>
                </tr>
            </table> -->
            <table >
                <tr style="line-height:10px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">
                    <td style="width:60%"></td>
                    <td style="width:40%;font-size:10px;text-align:center;">
                    <?php echo number_format($total_amount_due,2,'.',','); ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr style="line-height:40px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">
                    <td style="width:63%"></td>
                    <td style="width:37%;font-size:8px;text-align:center;">
                        <?php echo $cashier_name; ?>
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
        <td width="50%" style="font-size:10px;"></td>
        <td width="20%" style=""></td>
        <td width="30%" style="font-size:10px;text-align:center;"></td>
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
       <td style="font-size:10px;"></td>
    </tr>
</table>
<table >
    <tr style="line-height:6px;">
        <td style="font-size:10px;"></td>
    </tr>
</table>
