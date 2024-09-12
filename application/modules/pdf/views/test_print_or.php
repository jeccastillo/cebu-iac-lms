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
            <div style="position:absolute; top: 160px; left: 100px; width: 100px; height: 20px;">
                Inv No
            </div>
            <div style="position:absolute; top: 160px; left: 200px; width: 200px; height: 20px;">
                12,976.16
            </div>
            <div style="position:absolute; top: 200px; left: 100px; width: 200px; height: 20px;">
                Description Box
            </div>
        </section>
    </div>
</body>