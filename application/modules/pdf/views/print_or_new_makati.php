<style>
   body { margin: 0; font-family: Arial, Sans-Serif; }
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
    table{
        width:100%;
    }
    table tr td{
        vertical-align:top;
    }
    @media screen {
        body { 
            background: #e0e0e0 
        }
    
        .sheet {
            background: white;
            box-shadow: 0 .5mm 2mm rgba(0,0,0,.3); 
            margin: 5mm auto;
        }
    }

    .sheet-outer.A4 .sheet { 
        width: 210mm; 
        height: 296mm 
    }
    .sheet.padding-5mm { padding-top: 10mm; padding-left: 8mm; padding-right: 10mm; }

    @page {
        size: A4;
        margin: 0
    }
    @media print {
        .sheet-outer.A4, .sheet-outer.A5.landscape { 
            width: 210mm 
        }
    }

</style>
<body>
    <div class="sheet-outer A4">
        <section class="sheet padding-5mm">            
            <table style="border:none;width:100%;margin-top:18mm;">
                <tr>
                    <td style="width:30%">
                        <table>
                            <tr style="text-align:left;font-size:12px;">
                                <td style="width:50%;font-size:12px;"><div style="height:20px;"><?php echo $description; ?></div></td>
                                <td style="width:50%;vertical-align:top;"><?php echo number_format($total_amount_due,2,'.',','); ?></td>
                            </tr>
                        </table>
                        <table style="height:70mm;overflow:hidden;">
                            <tr style="font-size:12px;text-align:left;vertical-align:top;">
                                <td style="vertical-align:top;">
                                    <?php echo $description == "Reservation Payment" ? "NON REFUNDABLE AND NON TRANSFERABLE":""; ?><br />
                                    <?php echo "SY ".$term['strYearStart']."-".$term['strYearEnd']." ".$term['enumSem']." ".$term['term_label']."<br />".$type; ?><br />
                                    <?php echo $remarks; ?>
                                    <table>
                                        <tr style="line-height:12px;font-size:10px;text-align:left;">
                                            <td style="width:30%;font-size:11px;padding-top:15px;"><?php echo $description == "Reservation Payment" ? 'Name:' : ''; ?></td>
                                            <td style="width:70%;<?php echo $description == "Reservation Payment" ? 'border-bottom:1px solid #000;' : ''; ?>"></td>                  
                                        </tr>
                                    </table>                                    
                                    <table>
                                        <tr style="line-height:12px;font-size:10px;text-align:left;">
                                            <td style="width:30%;font-size:11px;padding-top:5px;"><?php echo $description == "Reservation Payment" ? 'Signature:' : ''; ?></td>
                                            <td style="width:70%;<?php echo $description == "Reservation Payment" ? 'border-bottom:1px solid #000;' : ''; ?>"></td>                  
                                        </tr>
                                    </table>
                                </td>                    
                            </tr>
                        </table>
                        <table>
                            <tr style="font-size:12px;text-align:left;">
                                <td style="width:50%">&nbsp;</td>
                                <td style="width:50%"><?php echo $is_cash == 1?"yes":""; ?></td>
                            </tr>
                        </table>             
                        <table>
                            <tr style="font-size:12px;text-align:left;">
                                <td style="width:50%">&nbsp;</td>
                                <td style="width:50%"><?php echo $is_cash == 0?"yes":""; ?></td>
                            </tr>
                        </table>                        
                        <table>
                            <tr style="font-size:12px;text-align:left;">
                                <td style="width:50%">&nbsp;</td>
                                <td style="width:50%"><?php echo ($is_cash == 2 || $is_cash == 3)?"yes":""; ?></td>
                            </tr>
                        </table>                                               
                        <table>
                            <tr style="font-size:12px;text-align:left;">                    
                                <td colspan="2" style="padding-top:13mm;"><?php 
                                    if($remarks == "Paynamics" || $remarks == "BDO Pay" || $remarks == "Maya Pay")
                                        echo $remarks;                                                
                                    else
                                        echo !$is_cash?$check_number:""; 
                                    
                                
                                ?></td>
                            </tr>
                        </table>   
                        <table style="">
                            <tr style="font-size:12px;text-align:left;">
                                <td style="width:50%">&nbsp;</td>
                                <td style="width:50%;">P<?php echo number_format($total_amount_due,2,'.',','); ?></td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:70%;vertical-align:top;">
                        <table style="padding-top:3mm;">
                            <tr>
                                <td style="text-align:right;font-weight:bold;font-size:9px;padding-right:20mm;">OR No:<?php echo $or_number; ?></td>
                            </tr>
                        </table>           
                        <table>
                            <tr style="padding-top:6mm;">
                                <td style="width:50%;padding-left:26mm;font-size:12px;text-align:left;">
                                    <div style="padding-top:15px;">
                                        <?php if($student_id != 'undefined' && $student_id != ''): ?>
                                            <?php echo preg_replace("/[^a-zA-Z0-9]+/", "", $student_id); ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td style="width:50%;font-size:12px;text-align:right;padding-right:10mm;"><?php echo "  ".date("M j, Y",strtotime($transaction_date)); ?></td>
                            </tr>
                        </table>                       
                        <table >
                            <tr>                    
                                <td style="font-size:12px;;text-align:left;padding-left:26mm;">                                    
                                    <div style="font-size:11px;position: relative;margin-top: -7px;">
                                        <?php echo $student_name; ?>
                                    </div>                                                                        
                                </td>
                            </tr>
                        </table>                       
                        <?php
                            $text = $student_address;
                            if(strlen($text) > 60){
                                $textSize = "8px";
                            }
                            elseif(strlen($text) > 40){
                                $textSize = "9.5px";
                            }
                            else
                                $textSize = "11px";

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
                            <tr>                    
                                <td style="font-size:12px;;text-align:left;padding-left:15mm;padding-top:5mm;">                                    
                                    <span style="font-size:<?php echo $textSize; ?>;"><?php echo $student_address; ?></span>                        
                                </td>
                            </tr>
                        </table>                      
                        <table>
                            <tr>                    
                                <td style="font-size:11px;;text-align:left;padding-left:34mm;padding-top:10mm;">
                                    <?php echo convert_number($total_amount_due); ?> <?php echo $decimal?'and '.convert_number($decimal).' cents':'only'; ?>
                                </td>
                            </tr>
                        </table>
                        <table >
                            <tr>
                                <td style="width:85%"></td>
                                <td style="width:15%;font-size:12px;;text-align:right;padding-top:10px;">
                                    P<?php echo number_format($total_amount_due,2,'.',','); ?><br />
                                </td>
                            </tr>
                        </table>            
                        <table >
                            <tr style="line-height:15px;">                    
                                <td style="font-size:12px;;text-align:left;padding-left:42mm;padding-top:5px;">                                    
                                    <?php echo $description; ?>
                                </td>
                            </tr>
                        </table>                       
                        <table >
                            <tr>
                                <td style="width:70%"></td>
                                <td style="width:30%;font-size:12px;;text-align:right;padding-top:10mm;">
                                P<?php echo number_format($total_amount_due,2,'.',','); ?>
                                </td>
                            </tr>
                        </table>                        
                        <table>
                            <tr style="line-height:15px;">
                                <td style="width:57%"></td>
                                <td style="width:43%;font-size:12px;;text-align:right;padding-top:5mm;">
                                P<?php echo number_format($total_amount_due,2,'.',','); ?>
                                </td>
                            </tr>
                        </table>                        
                        <table >
                            <tr>
                                <td style="width:60%"></td>
                                <td style="width:40%;font-size:12px;;text-align:right;padding-top:13mm;">
                                P<?php echo number_format($total_amount_due,2,'.',','); ?>
                                </td>
                            </tr>
                        </table>                        
                        <table style="margin-top:15mm;">
                            <tr>
                                <td style="width:63%"></td>
                                <td style="width:37%;font-size:12px;text-align:center;">
                                    <?php echo $cashier_name; ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>                
            </table>     
        </section>        
    </div>
</body>