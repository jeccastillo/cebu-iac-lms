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
            <table style="border:none;width:100%;margin-top:20mm;">
                <tr>
                    <td style="width:30%">
                        <table>
                            <tr style="text-align:left;font-size:12px;">
                                <td style="width:50%;"><?php echo $description; ?></td>
                                <td style="width:50%;vertical-align:top;"><?php echo number_format($total_amount_due,2,'.',','); ?></td>
                            </tr>                            
                        </table>
                        <table style="min-height:98px;">
                            <tr style="text-align:left;font-size:10px;">
                                <td style="vertical-align:top;"><?php echo $description == "Reservation Payment" ? "NON REFUNDABLE AND NON TRANSFERABLE":""; ?></td>                                
                            </tr>
                            <tr style="font-size:12px;text-align:left;vertical-align:top;">
                                <td style="vertical-align:top;"><?php echo "SY ".$term['strYearStart']."-".$term['strYearEnd']." ".$term['enumSem']." ".$term['term_label']." ".$type; ?></td>                    
                            </tr>
                        </table>
                    </td>
                    <td style="width:70%;vertical-align:top;">
                        Right
                    </td>
                </tr>                
            </table>     
        </section>        
    </div>
</body>