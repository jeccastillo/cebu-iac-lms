<style>
body {
    margin: 0;
    font-family: Arial, Sans-Serif;
}

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

section {
    font-size: 12px
}

table {
    width: 100%;
}

table tr td {
    vertical-align: top;
}

@media screen {
    body {
        background: #e0e0e0
    }

    .sheet {
        background: white;
        box-shadow: 0 .5mm 2mm rgba(0, 0, 0, .3);
        margin: 5mm auto;
    }
}

.sheet-outer.A4 .sheet {
    width: 210mm;
    height: 296mm
}

.sheet.padding-5mm {
    padding-top: 10mm;
    padding-left: 8mm;
    padding-right: 10mm;
}

@page {
    size: A4;
    margin: 0
}

@media print {

    .sheet-outer.A4,
    .sheet-outer.A5.landscape {
        width: 210mm
    }
}
</style>

<body>
    <div class="sheet-outer A4">
        <section class="sheet padding-5mm">
            <div style="position:absolute; top: 95px; left: 20px; width: 100px; height: 20px;">
                1234A
            </div>
            <div style="position:absolute; top: 95px; left: 95px; width: 200px; height: 20px;">
                12,976.16
            </div>
            <div style="position:absolute; top: 140px; left: 0px; width: 200px; height: 20px;">
                Description Box
            </div>
            <div style="position:absolute; top: 190px; left: 95px; width: 200px; height: 20px;">
                12,976.16
            </div>
            <div style="position:absolute; top: 340px; left: 0px; width: 200px; height: 20px;">
                Remarks
            </div>
            <div style="position:absolute; top: 340px; left: 95px; width: 200px; height: 20px;">
                12,976.16
            </div>
            <div style="position:absolute; top: 90; right: 60px; width: 200px; height: 20px;">
                OR No: 03041
            </div>
            <div style="position:absolute; top: 115px; right: -60px; width: 200px; height: 20px;">
                09/12/24
            </div>
            <div style="position:absolute; top: 140px; left: 336px; width: 500px; height: 20px;">
                C202301050 HASSAN, KALILAH MYNA, VILLAMOR 
            </div>
            <div style="position:absolute; top: 170px; left: 310px; width: 500px; height: 20px;">
                1773 Dian St. Palanan Makati City
            </div>
            <div style="position:absolute; top: 195px; left: 295px; width: 500px; height: 20px;">
                As Applicable
            </div>
            <div style="position:absolute; top: 215px; left: 350px; width: 500px; height: 20px;">
                Twelve thousand, nine hundred seventy-six and sixteen cents
            </div>
            <div style="position:absolute; top: 240px; right: -50; width: 200px; height: 20px;">
                12,976.16
            </div>
            <div style="position:absolute; top: 265px; left: 380px; width: 200px; height: 20px;">
                Tuition Fee / 3rd Term SY 2023-2024
            </div>
            
        </section>
    </div>
</body>