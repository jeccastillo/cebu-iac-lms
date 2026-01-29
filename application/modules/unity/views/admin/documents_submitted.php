<div id=document class="box box-primary">
    <div class="box-body">
        <div>
            <form @submit.prevent='submitDocument'>
                <div class="row">
                    <div class="col-md-5">
                        <label>Documents</label>
                        <select v-model="documentDetails.document" required class="form-control">
                            <option value="" selected></option>
                            <option value="2x2 id pic">2pcs. of 2x2 ID pic</option>
                            <option value="affidavit of support">Affidavit of Support</option>
                            <option value="certificate of good moral">Certificate of Good Moral
                            </option>
                            <option value="form 137">FORM 137</option>
                            <option value="grade certificate">Grade Certificate / True Copy of
                                Grades</option>
                            <option value="marriage certificate">Marriage Certificate / Affidavit of
                                Marital Status</option>
                            <option value="nso birth certificate">NSO Birth Certificate</option>
                            <option value="original report card">Original Report Card</option>
                            <option value="personal history statement">Personal History Statement (5
                                copies)</option>
                            <option value="photocopy report card">Photocopy of Report Card</option>
                            <option value="recommendation letter">Recommendation Letter / Form
                            </option>
                            <option value="subject description">Subject Description</option>
                            <option value="transcript of record">Transcript of Record</option>
                            <option value="transfer credential">Transfer Credential / Honorable
                                Dismissal</option>
                            <option value="esc voucher">ESC Voucher</option>
                            <option value="qvr voucher">QVR Voucher </option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Remarks</label>
                            <input v-model="documentDetails.remarks" type="text" class=form-control>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2">
                        <label style="color:#fff;">Submit</label>
                        <button type="submit" class="btn btn-primary btn-block">Submit</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="box box-primary">
    <div class="box-body">
        <table class="table table-condensed table-bordered">
            <thead>
                <tr>
                    <th>Document</th>
                    <th>User</th>
                    <th>Remarks</th>
                    <th>Date Submmitted</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="docu in documents" :key="docu">
                    <td>{{docu.document}}</td>
                    <td>{{docu.user}}</td>
                    <td>{{docu.remarks}}</td>
                    <td>{{docu.dateSubmitted}}</td>
                    <td>
                        <button class="btn btn-danger"
                            @click="removeDocument(docu.intID)">Remove</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <hr />
    </div>
</div>