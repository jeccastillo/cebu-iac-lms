<table >
    <tr style="line-height:10px;">
        <td style=""></td>
    </tr>
</table>
<table >
    <tr style="line-height:35px;">
        <td width="75%" style="text-align:right;font-weight:bold;font-size:9px;"></td>
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
                    <td style="width:50%;font-size:8px;height:12px;"><?php echo $description; ?> 
                    <?php echo $description == "Reservation Payment" ? "<br />NON REFUNDABLE AND NON <br />TRANSFERABLE":""; ?></td>
                    <td style="width:50%"><?php echo number_format($total_amount_due,2,'.',','); ?></td>
                </tr>
            </table>            
            <table style="height:98px;">
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td><?php echo "SY ".$term['strYearStart']."-".$term['strYearEnd']." ".$term['enumSem']." ".$term['term_label']; ?></td>                    
                </tr>
            </table>
            <!-- <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td><?php echo $type; ?></td>                    
                </tr>
            </table>      -->
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td><?php                     
                        echo $remarks;                     
                    ?>
                    </td>                    
                </tr>
            </table>
            <table>
                <tr style="line-height:98px;font-size:10px;text-align:left;">
                    <td>
                    &nbsp;
                    </td>                    
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:30%;font-size:9px;"><?php echo $description == "Reservation Payment" ? 'Name:' : ''; ?></td>
                    <td style="width:70%;<?php echo $description == "Reservation Payment" ? 'border-bottom:1px solid #000;' : ''; ?>"></td>                  
                </tr>
            </table>
            <table>
                <tr style="font-size:10px;line-height:2px;text-align:left;">
                    <td style="width:50%;color:#fff;">
                        &nbsp;
                    </td>                    
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:30%;font-size:9px;"><?php echo $description == "Reservation Payment" ? 'Signature:' : ''; ?></td>
                    <td style="width:70%;<?php echo $description == "Reservation Payment" ? 'border-bottom:1px solid #000;' : ''; ?>"></td>                  
                </tr>
            </table>                  
            <table>
                <tr style="font-size:10px;text-align:left;">
                    <td style="width:50%;line-height:35px;color:#fff;">SPACE</td>                    
                </tr>
            </table>
            <br />            
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:50%"></td>
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
                    <td style="width:50%"></td>
                    <td style="width:50%"><?php echo $is_cash == 0?"yes":""; ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:50%"></td>
                    <td style="width:50%"><?php echo ($is_cash == 2 || $is_cash == 3)?"yes":""; ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">
                    <td style="width:50%"></td>
                    <td style="width:50%"></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:15px;">
                    <td style=""></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:12px;font-size:10px;text-align:left;">                    
                    <td colspan="2"><?php 
                        if($remarks == "Paynamics" || $remarks == "BDO Pay" || $remarks == "Maya Pay")
                            echo $remarks;                                                
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
            <br />
            <br />
            <table>
                <tr style="line-height:17px;">
                    <td width="75%" style="text-align:right;font-weight:bold;font-size:9px;">OR No:<?php echo $or_number; ?></td>
                </tr>
            </table>           
            <table >
                <tr style="line-height:12px;">
                    <td style="font-size:10px;text-align:right;padding-right:15px;"><?php echo "  ".date("M j, Y",strtotime($transaction_date)); ?></td>
                </tr>
            </table>
            <table>
                <tr style="line-height:15px;">
                    <td style="font-size:10px;text-align:left;">
                    <span style="color:#fff;">RECEIVED fr</span>
                    <span style="font-size:9px">
                    <?php if($student_id != 'undefined' && $student_id != ''): ?>
                        <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?>
                    <?php endif; ?>
                    </span>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;">
                        <span style="color:#fff;">RECEIVED fr</span>
                        <span style="font-size:9px">
                            <?php echo $student_name; ?>
                        </span>                                                                        
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
                if(strlen($text) > 60){
                    $textSize = "6px";
                }
                elseif(strlen($text) > 40){
                    $textSize = "7.5px";
                }
                else
                    $textSize = "9px";

                $splitstring1 = substr($text, 0, 40);
                $splitstring2 = substr($text, 40);
                
                if (substr($splitstring1, 0, -1) != ' ' AND substr($splitstring2, 0, 1) != ' ')
                {
                    $middle = strlen($splitstring1) + strpos($splitstring2, ' ') + 1;
                }
                else
                {
                    $middle = strrpos(substr($text, 0, 40), ' ') + 1;    
                }
                
                $string1 = substr($text, 0, $middle);  // "The Quick : Brown Fox Jumped "
                $string2 = substr($text, $middle);  // "Over The Lazy / Dog"
            ?>
            <table>
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;">
                        <span style="color:#fff;">Address &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span style="font-size:<?php echo $textSize; ?>;"><?php echo $student_address; ?></span>                        
                    </td>
                </tr>
            </table>
            <!-- <table >
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table> -->
            <table >
                <tr style="line-height:3px;">                    
                    <td style="font-size:10px;text-align:left;"></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:30px;">
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
                <tr style="line-height:5px;">
                    <td style=""></td>
                </tr>
            </table>
            <table >
                <tr style="line-height:15px;">
                    <td style="width:85%"></td>
                    <td style="width:15%;font-size:10px;text-align:right;">
                        P<?php echo number_format($total_amount_due,2,'.',','); ?><br />
                    </td>
                </tr>
            </table>            
            <table >
                <tr style="line-height:15px;">                    
                    <td style="font-size:10px;text-align:left;">
                        <span style="color:#fff;">as full/partial payment of &nbsp;</span>
                        <?php echo $description; ?>
                    </td>
                </tr>
            </table>
            <table >
                <tr style="line-height:12px;">
                    <td style="width:60%"></td>
                    <td style="width:40%;font-size:8px;text-align:right;">                        
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
                    <td style="width:30%;font-size:10px;text-align:right;">
                    P<?php echo number_format($total_amount_due,2,'.',','); ?>
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
                    <td style="width:43%;font-size:10px;text-align:right;">
                    P<?php echo number_format($total_amount_due,2,'.',','); ?>
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
                    <td style="width:40%;font-size:10px;text-align:right;">
                    P<?php echo number_format($total_amount_due,2,'.',','); ?>
                    </td>
                </tr>
            </table>
            <table>
                <tr style="line-height:47px;">
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
