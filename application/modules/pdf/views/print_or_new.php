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
            <table style="border:none;width:100%">
                <tr>
                    <td style="width:30%">
                        <table>
                            <tr style="line-height:12px;font-size:10px;text-align:left;">
                                <td style="width:50%;font-size:8px;height:12px;"><?php echo $description; ?> 
                                <?php echo $description == "Reservation Payment" ? "<br />NON REFUNDABLE AND NON <br />TRANSFERABLE":""; ?></td>
                                <td style="width:50%"><?php echo number_format($total_amount_due,2,'.',','); ?></td>
                            </tr>
                        </table>
                    </td>
                    <td style="width:70%">
                        Right
                    </td>
                </tr>                
            </table>     
        </section>        
    </div>
</body>