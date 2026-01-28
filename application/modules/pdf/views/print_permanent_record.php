<style>
@page {
    size: legal;
    margin: 30mm 10mm 80mm 10mm;

    @top-center {
        content: element(headerRunning);
    }

    @bottom-left {
        content: element(footerRunning);
    }
}

.pagedjs_page_content::after {
    content: "Page "counter(page) " of "counter(pages);
    position: absolute;
    bottom: -255px;
    left: 2px;
    font-size: 12px;
}

.info-container {
    max-width: 900px;
    background: #fff;
    /* border-bottom: 2px solid black; */
}

.info-row {
    /* margin-bottom: 1px; */
    display: flex;

    & :first-child {
        white-space: nowrap;
        font-weight: normal;
        min-width: 120px;
        display: inline-block;
    }
}

.grid-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    /* margin-bottom: 4px; */
}

.info-group {
    display: flex;
    align-items: baseline;
    position: relative;

    & :first-child {
        white-space: nowrap;
        font-weight: normal;
        min-width: 120px;
        display: inline-block;
    }
}

/* #header-template {
    position: running(headerRunning);
} */
#footer-template {
    margin-bottom: 120px;
    position: running(footerRunning);
}

.header-container {
    width: 100%;
    background-color: #3956a2;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 120px;
}

.header-container img {
    height: 80px;
    margin-right: 30px;
}

.header-text {
    text-align: center;
}

.header-text h1 {
    margin: 0;
    font-size: 24px;
    text-transform: uppercase;
}

.header-text h2 {
    margin: 0;
    font-weight: normal;
}

.footer-container {
    width: 100%;
    font-size: 10pt;
    padding-top: 10px;
    margin-bottom: 60px;
}

.text-center {
    text-align: center;
}

.signature-container {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}

.signature-line {
    border-top: 1px solid black;
    text-align: center;
    padding-top: 5px;
    width: 100%;
}

body {
    /* font-family: "Times New Roman", Times, serif; */
    /* font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; */
    /* font-family: "Century Gothic", CenturyGothic, sans-serif; */
    font-family: century-gothic, sans-serif;
    font-size: 10pt;
    line-height: normal;
    margin: 0;
    width: 816px !important;
}

table {
    width: 100%;
    border-collapse: collapse;
    border-bottom: 2px solid black;
    font-size: 10pt;
}

footer {
    /* margin-top: 10px; */
}

caption {
    text-align: initial;
    border-top: 2px solid black;
    border-bottom: 2px solid black;
}

thead {
    border-bottom: 2px solid black;
}

th {
    text-align: left;
    font-weight: normal;
    padding: 0
}

td {
    vertical-align: top;
}

tbody {
    & tr:nth-child(1)>td {
        padding-top: 8px;
    }

    & tr:last-child>td {
        padding-bottom: 8px;
    }
}

.content-area {
    position: relative;
    width: 100%;
    margin-top: 10px
}

.continued-next {
    position: absolute;
    bottom: -27px;
    left: 0;
    right: 0;
    font-size: 10pt;
    text-align: center;
    /* font-style: italic; */
    z-index: 10000;
    padding: 5px;
}

.pagedjs_sheet {
    border: 1px solid red;
    /* width: 816px !important; */
    /* height: 1344px !important; */
}

.pagedjs_area {
    /* border: 1px solid green !important; */
}

h1,
h2,
h4,
p {
    margin: 0;
}

h3 {
    border-bottom: 1px solid #000;
    padding-bottom: 5px;
}

.text-center {
    text-align: center;
}

.statement-row {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 5px;
    margin-bottom: 10px;
}

.blank-line {
    border-bottom: 1px solid #000;
    min-width: 150px;
    flex-grow: 1;
    display: inline-block;
    text-align: center;
    text-transform: uppercase;
    margin: 0 2px;
}

.signature-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    /* font-family: Arial, sans-serif; */
    width: 100%;
    max-width: 800px;
    margin-top: 20px;
}

.left-section {
    width: 45%;
}

.right-section {
    width: 35%;
    text-align: center;
}

.field-row {
    display: flex;
    align-items: end;
}

.label {
    width: 75px;
    white-space: nowrap;
}

.underline {
    flex-grow: 1;
    border-bottom: 1px solid black;
    padding-left: 5px;
    /* min-height: 18px; */
    max-width: 200px;
    text-transform: capitalize;
}

.code-col {
    width: 22%;
}

.subject-col {
    width: 25%;
}

.grade-col {
    width: 12%;
}

.remarks-col {
    width: 10%;
}

.footer-caption {
    caption-side: bottom;
}

.s-width {
    min-width: 70px !important;
}

.m-width {
    min-width: 100px !important;
}

.h-full {
    height: 100%;
}

.guardian {
    left: 120px;
    position: absolute;
    top: 15px;
}

.high-school {
    width: 260px
}

.footer-table {
    margin-bottom: 20px;
    border-bottom: 2px solid black
}

.sm-name-line {
    min-width: 100px;
}

.md-name-line {
    min-width: 180px;
}

.name {
    text-transform: capitalize;
}

.uppercase {
    text-transform: uppercase;
}
</style>
<script src="https://unpkg.com/pagedjs/dist/paged.polyfill.js"></script>

<body>
    <!-- Header Template for All Pages -->
    <!-- <div id="header-template">
      <div class="header-container">
        <img src="https://i.ibb.co/HT6nr7pz/seal-makati.png" alt="iACADEMY Seal" />
        <div class="header-text">
          <h1>iACADEMY</h1>
          <h2 style="font-size: 14px">Makati City</h2>
          <h2 style="font-size: 16px">STUDENT'S PERMANENT RECORD</h2>
        </div>
      </div>
    </div> -->
    <footer id="footer-template">
        <div class="footer-container">
            <p class="text-center">TRANSFER ELIGIBILITY</p>
            <div>
                <p> I certify that this is the true record of <span
                        class="blank-line md-name-line"><?php echo $student['strFirstname'] .' '. $student['strMiddlename'] .' '. $student['strLastname']; ?></span>who
                    is eligible for admission to <span
                        class="blank-line sm-name-line"><?php echo $other_details['admission_to']?></span>and
                    has no outstanding obligation to the school </p>
            </div>
            <br />
            <p>Remarks: <?php echo $other_details['remarks']?></p>
            <br />
            <p>Grading System:</p>
            <p> 90-100 (Outstanding); 85-89 (Very Satisfactory); 80-84 (Satisfactory); 75-79 (Fairly
                Satisfactory); Below 75 (Did Not Meet Expectation); IP (In Progress); OW (Officially
                Withdrawn); OD (Officially Dropped) </p>
            <br />
            <p>Note: This document is valid only when it bears the seal of the School and affixed
                with the original signature in ink. Any erasure or alteration made on this copy
                renders the whole document invalid.</p>
            <div class="signature-container">
                <div class="left-section">
                    <div class="field-row">
                        <span class="label">Prepared by:</span>
                        <span class="underline"><?php echo $other_details['prepared_by']?></span>
                    </div>
                    <div class="field-row">
                        <span class="label">Verified by:</span>
                        <span class="underline"><?php echo $other_details['verified_by']?></span>
                    </div>
                    <div class="field-row">
                        <span class="label">Date Issued:</span>
                        <span
                            class="underline"><?php echo date("F d, Y", strtotime($other_details['date_issued']))?></span>
                    </div>
                </div>
                <div class="right-section">
                    <div class="name"><?php echo $other_details['registrar']?></div>
                    <div class="signature-line">
                        <div class="role">AVP for Administration</div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <main class="content-area">
        <div id="main-content">
            <div class="info-container">
                <div class="info-row full-width">
                    <span>Name :</span>
                    <span class="value uppercase">
                        <?php echo $student['strLastname'] .', '. $student['strFirstname'] .' '. $student['strMiddlename']; ?>
                    </span>
                </div>
                <div class="grid-row">
                    <div class="info-group">
                        <span>ID No. :</span>
                        <span
                            class="value"><?php echo str_replace('-', '', $student['strStudentNumber']);?></span>
                    </div>
                    <div class="info-group">
                        <span>LRN No. :</span>
                        <span class="value"><?php echo $student['strLRN']?></span>
                    </div>
                </div>
                <div class="grid-row">
                    <div class="info-group">
                        <span>Track/Strand :</span>
                        <span class="value"><?php echo $student['strand']?></span>
                    </div>
                    <div class="info-group">
                        <span>Gender :</span>
                        <span class="value">Female</span>
                    </div>
                </div>
                <div class="info-row full-width">
                    <span>Address :</span>
                    <span class="value uppercase"><?php echo $student['strAddress']?></span>
                </div>
                <div class="grid-row">
                    <div class="info-group">
                        <span>Date of Birth :</span>
                        <span class="value"><?php echo $student['dteBirthDate']?></span>
                    </div>
                    <div class="info-group">
                        <span>Place of Birth :</span>
                        <span class="value"><?php echo $student['place_of_birth']?></span>
                    </div>
                </div>
                <div class="grid-row">
                    <div class="info-group">
                        <span>Citizenship :</span>
                        <span class="value"><?php echo $student['strCitizenship']?></span>
                    </div>
                    <div class="info-group">
                        <span>Parent/Guardian :</span>
                        <span class="value"><?php echo $student['mother']?></span>
                        <span class="value guardian"><?php echo $student['father']?></span>
                    </div>
                </div>
                <br />
                <div class="grid-row" style="gap:0">
                    <div class="info-group">
                        <span>Junior High School :</span>
                        <span class="high-school"><?php echo $student['high_school']?></span>
                    </div>
                    <div class="info-group">
                        <span>Date of Admission :</span>
                        <span
                            class="value"><?php echo date("F d, Y", strtotime($other_details['admission_date']))?></span>
                    </div>
                </div>
                <div class="grid-row">
                    <div class="info-group">
                        <span>Senior High School :</span>
                        <span class="value"><?php echo $other_details['senior_high_school']?></span>
                    </div>
                    <div class="info-group">
                        <span>Date of Graduation :</span>
                        <span
                            class="value"><?php echo date("F d, Y", strtotime($other_details['graduation_date']))?></span>
                    </div>
                </div>
            </div>
            <div id="dynamic-rows"></div>
            <div> <?php


$mainRecord = $records[0];
$mainRecord = $records[0];
$subjects = $mainRecord['records'];
$details = $mainRecord['other_data'];

foreach ($records  as $block): 
    $subjects = $block['records'];
    $details  = $block['other_data'];
        
?> <table>
                    <caption>
                        <div class="">
                            <div class="grid-row">
                                <div class="info-group">
                                    <span class="s-width">Grade :</span>
                                    <span class="value">Grade
                                        <?php echo isset($subjects[0]['year']) ? $subjects[0]['year'] : ''; ?></span>
                                </div>
                                <div class="info-group">
                                    <span class="m-width">Sem :</span>
                                    <span class="value"><?php echo $details['term']['enumSem']; ?>
                                        Semester</span>
                                </div>
                            </div>
                            <div class="grid-row">
                                <div class="info-group">
                                    <span class="s-width"> School:</span>
                                    <span class="value"></span>
                                </div>
                                <div class="info-group">
                                    <span class="m-width">School Year :</span>
                                    <span
                                        class="value"><?php echo $details['term']['strYearStart'] . "-" . $details['term']['strYearEnd']; ?></span>
                                </div>
                            </div>
                        </div>
                    </caption>
                    <thead>
                        <tr>
                            <th class="code-col"></th>
                            <th class="subject-col">Subjects</th>
                            <th class="grade-col">Semester<br />Final Grade</th>
                            <th class="remarks-col">Remarks</th>
                        </tr>
                    </thead>
                    <tbody> <?php foreach ($subjects as $subject): ?> <tr>
                            <td><?php echo $subject['strCode']; ?></td>
                            <td><?php echo $subject['strDescription']; ?></td>
                            <td><?php echo $subject['semFinalGrade']; ?></td>
                            <td><?php echo strtoupper($subject['strRemarks']); ?></td>
                        </tr> <?php endforeach; ?> </tbody>
                </table>
                <div class="footer-table">
                    <div class="grid-row">
                        <div class="info-group">
                            <span>Days of School:</span>
                            <span class="value">90</span>
                        </div>
                        <div class="info-group">
                            <span class="">General Average :</span>
                            <span class="value"><?php echo (int)$details['gwa']; ?></span>
                        </div>
                    </div>
                    <div class="grid-row">
                        <div class="info-group">
                            <span>Days of Present:</span>
                            <span class="value"></span>
                        </div>
                        <div class="info-group">
                            <span>Remarks :</span>
                            <span
                                class="value"><?php echo (int)$details['gwa'] < 75 ? 'FAILED' : 'PASSED'; ?></span>
                        </div>
                    </div>
                </div> <?php endforeach; ?>
            </div>
        </div>
    </main>
    <!-- Footer Template for All Pages -->
    <script>
    // const dynamicRows = document.getElementById("dynamic-rows")
    // for (let i = 1; i <= 100; i++) {
    //     const div = document.createElement("div")
    //     div.style.padding = "5px 0"
    //     div.style.borderBottom = "1px solid #eee"
    //     div.innerHTML = `<b>Course Unit ${i}:</b> Advanced Graphic Design - Grade: 95 (Passed)`
    //     dynamicRows.appendChild(div)
    // }
    const container = document.createElement('div');
    container.className = 'grid-row';
    container.innerHTML = `
         <div class="info-group" style="margin-bottom:10px">
             <span style="min-width: 50px;">Name:</span>
             <span class="value uppercase"><?php echo $student['strLastname'] .', '. $student['strFirstname'] .' '. $student['strMiddlename']; ?></span>
        </div>
        <div class="info-group" style="justify-content:center">
            <span style="min-width: 50px;">ID No.:</span>
            <span class="value"><?php echo str_replace('-', '', $student['strStudentNumber']);?></span>
        </div>
    
        `;
    class PageContinuationHandler extends Paged.Handler {
        afterPageLayout(pageElement, page, breakToken) {
            console.log(page.position);
            const parent = pageElement.querySelector(".content-area")
            const child = document.createElement("p")
            if (page.position > 0) {
                console.log(child);
                if (parent) {
                    parent.prepend(container)
                }
            }
            if (pageElement.querySelector(".continued-next")) return
            const continued = document.createElement("p")
            continued.className = "continued-next"
            if (breakToken) {
                continued.textContent = `Continue on page ${page.position + 1}`
            } else {
                continued.textContent = `****Nothing Follows****`
            }
            const contentArea = pageElement.querySelector(
                ".pagedjs_page_content .content-area")
            if (contentArea) {
                contentArea.append(continued)
            }
        }
    }
    Paged.registerHandlers(PageContinuationHandler)
    // window.PagedPolyfill.preview()
    </script>
</body>