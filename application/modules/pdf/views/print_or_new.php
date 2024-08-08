<style>
   body { margin: 0 }
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
    .sheet.padding-5mm { padding: 5mm }

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
                        Left
                    </td>
                    <td style="width:70%">
                        Right
                    </td>
                </tr>                
            </table>     
        </section>        
    </div>
</body>