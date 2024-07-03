<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/vuejs-datepicker/1.6.2/vuejs-datepicker.min.js">
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.7.0/moment.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue2-datepicker@3.11.1/index.min.js"></script>

<div class="content-wrapper " id="applicant-container">
    <section class="content-header container ">
        <h1>
            Student Applicants
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i>Student Applicant Details </a></li>
            <li class="active">Details</li>
        </ol>
    </section>
    <div class="content  container">
        <div action="">
            <div class="box ">
                <div class="box-header with-border font-weight-bold py-5" style="text-align:left; font-weight:bold">
                    <h3 class="box-title text-left text-primary " style="font-size:2rem">
                        Applicant Details
                    </h3>
                </div>

                <div class="box-body" style="padding:2rem">
                    <div>
                        <strong><i class="fa fa-sitemap margin-r-5"></i>Status</strong>
                        <p>
                            <span class="label label-danger" v-if="request.status ==  'New'">New</span>
                            <span class="label label-primary" v-if="request.status ==  'For Interview'">For
                                Interview</span>
                            <span class="label label-warning" v-if="request.status ==  'Waiting For Interview'">Waiting
                                For
                                Interview</span>
                            <!-- <span class="label label-warning">Scheduled</span> -->
                            <span class="label label-info" v-if="request.status ==  'For Reservation'">For
                                Reservation</span>
                            <span class="label label-success" v-if="request.status ==  'Reserved'">Reserved</span>
                            <span class="label label-success" v-if="request.status ==  'Confirmed'">Confirmed</span>
                            <span class="label label-success" v-if="request.status ==  'For Enrollment'">For
                                Enrollment</span>
                            <span class="label label-success" v-if="request.status ==  'Enrolled'">Enrolled</span>
                            <span class="label label-danger" v-if="request.status ==  'Cancelled'">Cancelled
                                Application</span>
                            <span class="label label-danger" v-if="request.status ==  'Did Not Reserve'">Did Not
                                Reserve</span>
                            <span class="label label-danger" v-if="request.status ==  'Floating'">Floating
                                Application</span>
                            <span class="label label-danger" v-if="request.status ==  'Will Not Proceed'">Will Not Proceed</span>
                            <span class="label label-danger" v-if="request.status ==  'Rejected'">Rejected</span>
                            <span class="label label-danger" v-if="request.status ==  'Withdrawn Before'">Withdrawn
                                Enrollment Before Opening of SY</span>
                            <span class="label label-danger" v-if="request.status ==  'Withdrawn After'">Withdrawn
                                Enrollment After Opening of SY</span>
                            <span class="label label-danger" v-if="request.status ==  'Withdrawn End'">Withdrawn
                                Enrollment at the End of the Term</span>

                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-calendar margin-r-5"></i> Date Applied</strong>
                        <p class="text-muted">
                            {{request.date}}
                        </p>
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-book margin-r-5"></i> Name</strong>
                        <p class="text-muted">
                            {{request.first_name + ' ' + request.last_name}} <button
                                v-if="request.status !=  'Game Changer' && request.status !=  'For Enrollment'"
                                class="btn btn-primary" @click="showEdit">{{show_edit_title}}</button>
                        </p>
                        <hr>
                    </div>
                    <div v-if="show_edit_name">
                        <div>
                            <strong><i class="fa fa-book margin-r-5"></i>Edit First Name</strong>
                            <input type="text" class="form-control" v-model="request.first_name"
                                @blur="updateField('first_name',$event)" />
                            <hr>
                        </div>
                        <div>
                            <strong><i class="fa fa-book margin-r-5"></i>Edit Last Name</strong>
                            <input type="text" class="form-control" v-model="request.last_name"
                                @blur="updateField('last_name',$event)" />
                            <hr>
                        </div>
                        <div>
                            <strong><i class="fa fa-book margin-r-5"></i>Edit Middle Name</strong>
                            <input type="text" class="form-control" v-model="request.middle_name"
                                @blur="updateField('middle_name',$event)" />
                            <hr>
                        </div>
                    </div>
                    <div>
                        <strong><i class="fa fa-book margin-r-5"></i> Application Payment Link</strong>
                        <p class="text-muted">
                            {{base_url+'site/admissions_student_payment/'+slug+'/'+sy_reference}}
                        </p>
                        <hr>

                    </div>
                    <div
                        v-if="request.status !=  'Enrolled' && request.status !=  'Enlisted' && request.status !=  'Confirmed' && request.status !=  'For Enrollment'">
                        <label>Select Term</label>
                        <select required @change="updateField('syid',$event)" v-model="sy_reference"
                            class="form-control">
                            <option v-for="sem in sy" :value="sem.intID">
                                {{ sem.term_student_type + " " + sem.enumSem + " SY " + sem.strYearStart + " - " + sem.strYearEnd  }}</option>

                        </select>
                        <hr />
                    </div>
                    <div>
                        <strong><i class="fa fa-envelope margin-r-5"></i> Email</strong>
                        <input type="text" class="form-control" v-model="request.email" @blur="updateField('email',$event)" />                        
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-phone margin-r-5"></i> Mobile Number</strong>
                        <input v-if="request.status !=  'Game Changer' && request.status !=  'For Enrollment'"
                            type="text" class="form-control" v-model="request.mobile_number"
                            @blur="updateField('mobile_number',$event)" />
                        <p v-else class="text-muted">
                            {{request.mobile_number}}
                        </p>
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-phone-square margin-r-5"></i> Telephone Number</strong>
                        <input type="text" class="form-control" v-model="request.tel_number"
                            @blur="updateField('tel_number',$event)" />
                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-location-arrow margin-r-5"></i> Address</strong>
                        <textarea class="form-control" v-model="request.address"
                            @blur="updateField('address',$event)"></textarea>

                        <hr>
                    </div>

                    <div>
                        <strong><i class="fa fa-calendar margin-r-5"></i> Birthday</strong>
                        <input type="text" class="form-control" v-model="request.date_of_birth"
                            @blur="updateField('date_of_birth',$event)" />

                        <hr>
                    </div>
                    <?php if($userlevel == "2"):  ?>
                    <div>
                        <strong><i class="fa fa-calendar margin-r-5"></i> Date Enrolled</strong>
                        <input type="date" class="form-control" v-model="request.date_enrolled_u"
                            @blur="updateField('date_enrolled',$event)" />

                        <hr>
                    </div>
                    <?php endif; ?>
                    <div>
                        <strong><i class="fa fa-home margin-r-5"></i> Previous School</strong>
                        <input type="text" class="form-control" v-model="request.school"
                            @blur="updateField('school',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Waive Application Fee?</strong>
                        <select class="form-control" @change="updateField('waive_app_fee',$event)"
                            v-model="request.waive_app_fee">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Reserve Enroll Promo</strong>
                        <select class="form-control" @change="updateField('reserve_enroll',$event)"
                            v-model="request.reserve_enroll">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                        <hr>
                    </div>
                    <div v-if="request.waive_app_fee">
                        <strong><i class="fa fa-user margin-r-5"></i>Reason</strong>
                        <select class="form-control" @change="updateField('waive_reason',$event)"
                            v-model="request.waive_reason">
                            <option value="organic">Organic</option>
                            <option value="scholarship">Scholarship</option>
                            <option value="special application">Special Application</option>
                        </select>
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-user margin-r-5"></i>Citizenship</strong>
                        <select class="form-control" @change="updateField('citizenship',$event)"
                            v-model="request.citizenship">
                            <option value="Afghanistan">Afghanistan</option>
                            <option value="Aland Islands">Åland Islands</option>
                            <option value="Albania">Albania</option>
                            <option value="Algeria">Algeria</option>
                            <option value="American Samoa">American Samoa</option>
                            <option value="Andorra">Andorra</option>
                            <option value="Angola">Angola</option>
                            <option value="Anguilla">Anguilla</option>
                            <option value="Antarctica">Antarctica</option>
                            <option value="Antigua and Barbuda">Antigua & Barbuda</option>
                            <option value="Argentina">Argentina</option>
                            <option value="Armenia">Armenia</option>
                            <option value="Aruba">Aruba</option>
                            <option value="Australia">Australia</option>
                            <option value="Austria">Austria</option>
                            <option value="Azerbaijan">Azerbaijan</option>
                            <option value="Bahamas">Bahamas</option>
                            <option value="Bahrain">Bahrain</option>
                            <option value="Bangladesh">Bangladesh</option>
                            <option value="Barbados">Barbados</option>
                            <option value="Belarus">Belarus</option>
                            <option value="Belgium">Belgium</option>
                            <option value="Belize">Belize</option>
                            <option value="Benin">Benin</option>
                            <option value="Bermuda">Bermuda</option>
                            <option value="Bhutan">Bhutan</option>
                            <option value="Bolivia">Bolivia</option>
                            <option value="Bonaire, Sint Eustatius and Saba">Caribbean Netherlands</option>
                            <option value="Bosnia and Herzegovina">Bosnia & Herzegovina</option>
                            <option value="Botswana">Botswana</option>
                            <option value="Bouvet Island">Bouvet Island</option>
                            <option value="Brazil">Brazil</option>
                            <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                            <option value="Brunei Darussalam">Brunei</option>
                            <option value="Bulgaria">Bulgaria</option>
                            <option value="Burkina Faso">Burkina Faso</option>
                            <option value="Burundi">Burundi</option>
                            <option value="Cambodia">Cambodia</option>
                            <option value="Cameroon">Cameroon</option>
                            <option value="Canada">Canada</option>
                            <option value="Cape Verde">Cape Verde</option>
                            <option value="Cayman Islands">Cayman Islands</option>
                            <option value="Central African Republic">Central African Republic</option>
                            <option value="Chad">Chad</option>
                            <option value="Chile">Chile</option>
                            <option value="China">China</option>
                            <option value="Christmas Island">Christmas Island</option>
                            <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                            <option value="Colombia">Colombia</option>
                            <option value="Comoros">Comoros</option>
                            <option value="Congo">Congo - Brazzaville</option>
                            <option value="Congo, Democratic Republic of the Congo">Congo - Kinshasa</option>
                            <option value="Cook Islands">Cook Islands</option>
                            <option value="Costa Rica">Costa Rica</option>
                            <option value="Cote D'Ivoire">Côte d’Ivoire</option>
                            <option value="Croatia">Croatia</option>
                            <option value="Cuba">Cuba</option>
                            <option value="Curacao">Curaçao</option>
                            <option value="Cyprus">Cyprus</option>
                            <option value="Czech Republic">Czechia</option>
                            <option value="Denmark">Denmark</option>
                            <option value="Djibouti">Djibouti</option>
                            <option value="Dominica">Dominica</option>
                            <option value="Dominican Republic">Dominican Republic</option>
                            <option value="Ecuador">Ecuador</option>
                            <option value="Egypt">Egypt</option>
                            <option value="El Salvador">El Salvador</option>
                            <option value="Equatorial Guinea">Equatorial Guinea</option>
                            <option value="Eritrea">Eritrea</option>
                            <option value="Estonia">Estonia</option>
                            <option value="Ethiopia">Ethiopia</option>
                            <option value="Falkland Islands (Malvinas)">Falkland Islands (Islas Malvinas)</option>
                            <option value="Faroe Islands">Faroe Islands</option>
                            <option value="Fiji">Fiji</option>
                            <option value="Finland">Finland</option>
                            <option value="France">France</option>
                            <option value="French Guiana">French Guiana</option>
                            <option value="French Polynesia">French Polynesia</option>
                            <option value="French Southern Territories">French Southern Territories</option>
                            <option value="Gabon">Gabon</option>
                            <option value="Gambia">Gambia</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Germany">Germany</option>
                            <option value="Ghana">Ghana</option>
                            <option value="Gibraltar">Gibraltar</option>
                            <option value="Greece">Greece</option>
                            <option value="Greenland">Greenland</option>
                            <option value="Grenada">Grenada</option>
                            <option value="Guadeloupe">Guadeloupe</option>
                            <option value="Guam">Guam</option>
                            <option value="Guatemala">Guatemala</option>
                            <option value="Guernsey">Guernsey</option>
                            <option value="Guinea">Guinea</option>
                            <option value="Guinea-Bissau">Guinea-Bissau</option>
                            <option value="Guyana">Guyana</option>
                            <option value="Haiti">Haiti</option>
                            <option value="Heard Island and Mcdonald Islands">Heard & McDonald Islands</option>
                            <option value="Holy See (Vatican City State)">Vatican City</option>
                            <option value="Honduras">Honduras</option>
                            <option value="Hong Kong">Hong Kong</option>
                            <option value="Hungary">Hungary</option>
                            <option value="Iceland">Iceland</option>
                            <option value="India">India</option>
                            <option value="Indonesia">Indonesia</option>
                            <option value="Iran, Islamic Republic of">Iran</option>
                            <option value="Iraq">Iraq</option>
                            <option value="Ireland">Ireland</option>
                            <option value="Isle of Man">Isle of Man</option>
                            <option value="Israel">Israel</option>
                            <option value="Italy">Italy</option>
                            <option value="Jamaica">Jamaica</option>
                            <option value="Japan">Japan</option>
                            <option value="Jersey">Jersey</option>
                            <option value="Jordan">Jordan</option>
                            <option value="Kazakhstan">Kazakhstan</option>
                            <option value="Kenya">Kenya</option>
                            <option value="Kiribati">Kiribati</option>
                            <option value="Korea, Democratic People's Republic of">North Korea</option>
                            <option value="Korea, Republic of">South Korea</option>
                            <option value="Kosovo">Kosovo</option>
                            <option value="Kuwait">Kuwait</option>
                            <option value="Kyrgyzstan">Kyrgyzstan</option>
                            <option value="Lao People's Democratic Republic">Laos</option>
                            <option value="Latvia">Latvia</option>
                            <option value="Lebanon">Lebanon</option>
                            <option value="Lesotho">Lesotho</option>
                            <option value="Liberia">Liberia</option>
                            <option value="Libyan Arab Jamahiriya">Libya</option>
                            <option value="Liechtenstein">Liechtenstein</option>
                            <option value="Lithuania">Lithuania</option>
                            <option value="Luxembourg">Luxembourg</option>
                            <option value="Macao">Macao</option>
                            <option value="Macedonia, the Former Yugoslav Republic of">North Macedonia</option>
                            <option value="Madagascar">Madagascar</option>
                            <option value="Malawi">Malawi</option>
                            <option value="Malaysia">Malaysia</option>
                            <option value="Maldives">Maldives</option>
                            <option value="Mali">Mali</option>
                            <option value="Malta">Malta</option>
                            <option value="Marshall Islands">Marshall Islands</option>
                            <option value="Martinique">Martinique</option>
                            <option value="Mauritania">Mauritania</option>
                            <option value="Mauritius">Mauritius</option>
                            <option value="Mayotte">Mayotte</option>
                            <option value="Mexico">Mexico</option>
                            <option value="Micronesia, Federated States of">Micronesia</option>
                            <option value="Moldova, Republic of">Moldova</option>
                            <option value="Monaco">Monaco</option>
                            <option value="Mongolia">Mongolia</option>
                            <option value="Montenegro">Montenegro</option>
                            <option value="Montserrat">Montserrat</option>
                            <option value="Morocco">Morocco</option>
                            <option value="Mozambique">Mozambique</option>
                            <option value="Myanmar">Myanmar (Burma)</option>
                            <option value="Namibia">Namibia</option>
                            <option value="Nauru">Nauru</option>
                            <option value="Nepal">Nepal</option>
                            <option value="Netherlands">Netherlands</option>
                            <option value="Netherlands Antilles">Curaçao</option>
                            <option value="New Caledonia">New Caledonia</option>
                            <option value="New Zealand">New Zealand</option>
                            <option value="Nicaragua">Nicaragua</option>
                            <option value="Niger">Niger</option>
                            <option value="Nigeria">Nigeria</option>
                            <option value="Niue">Niue</option>
                            <option value="Norfolk Island">Norfolk Island</option>
                            <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                            <option value="Norway">Norway</option>
                            <option value="Oman">Oman</option>
                            <option value="Pakistan">Pakistan</option>
                            <option value="Palau">Palau</option>
                            <option value="Palestinian Territory, Occupied">Palestine</option>
                            <option value="Panama">Panama</option>
                            <option value="Papua New Guinea">Papua New Guinea</option>
                            <option value="Paraguay">Paraguay</option>
                            <option value="Peru">Peru</option>
                            <option selected value="Philippines">Philippines</option>
                            <option value="Pitcairn">Pitcairn Islands</option>
                            <option value="Poland">Poland</option>
                            <option value="Portugal">Portugal</option>
                            <option value="Puerto Rico">Puerto Rico</option>
                            <option value="Qatar">Qatar</option>
                            <option value="Reunion">Réunion</option>
                            <option value="Romania">Romania</option>
                            <option value="Russian Federation">Russia</option>
                            <option value="Rwanda">Rwanda</option>
                            <option value="Saint Barthelemy">St. Barthélemy</option>
                            <option value="Saint Helena">St. Helena</option>
                            <option value="Saint Kitts and Nevis">St. Kitts & Nevis</option>
                            <option value="Saint Lucia">St. Lucia</option>
                            <option value="Saint Martin">St. Martin</option>
                            <option value="Saint Pierre and Miquelon">St. Pierre & Miquelon</option>
                            <option value="Saint Vincent and the Grenadines">St. Vincent & Grenadines</option>
                            <option value="Samoa">Samoa</option>
                            <option value="San Marino">San Marino</option>
                            <option value="Sao Tome and Principe">São Tomé & Príncipe</option>
                            <option value="Saudi Arabia">Saudi Arabia</option>
                            <option value="Senegal">Senegal</option>
                            <option value="Serbia">Serbia</option>
                            <option value="Serbia and Montenegro">Serbia</option>
                            <option value="Seychelles">Seychelles</option>
                            <option value="Sierra Leone">Sierra Leone</option>
                            <option value="Singapore">Singapore</option>
                            <option value="Sint Maarten">Sint Maarten</option>
                            <option value="Slovakia">Slovakia</option>
                            <option value="Slovenia">Slovenia</option>
                            <option value="Solomon Islands">Solomon Islands</option>
                            <option value="Somalia">Somalia</option>
                            <option value="South Africa">South Africa</option>
                            <option value="South Georgia and the South Sandwich Islands">South Georgia & South Sandwich
                                Islands</option>
                            <option value="South Sudan">South Sudan</option>
                            <option value="Spain">Spain</option>
                            <option value="Sri Lanka">Sri Lanka</option>
                            <option value="Sudan">Sudan</option>
                            <option value="Suriname">Suriname</option>
                            <option value="Svalbard and Jan Mayen">Svalbard & Jan Mayen</option>
                            <option value="Swaziland">Eswatini</option>
                            <option value="Sweden">Sweden</option>
                            <option value="Switzerland">Switzerland</option>
                            <option value="Syrian Arab Republic">Syria</option>
                            <option value="Taiwan, Province of China">Taiwan</option>
                            <option value="Tajikistan">Tajikistan</option>
                            <option value="Tanzania, United Republic of">Tanzania</option>
                            <option value="Thailand">Thailand</option>
                            <option value="Timor-Leste">Timor-Leste</option>
                            <option value="Togo">Togo</option>
                            <option value="Tokelau">Tokelau</option>
                            <option value="Tonga">Tonga</option>
                            <option value="Trinidad and Tobago">Trinidad & Tobago</option>
                            <option value="Tunisia">Tunisia</option>
                            <option value="Turkey">Turkey</option>
                            <option value="Turkmenistan">Turkmenistan</option>
                            <option value="Turks and Caicos Islands">Turks & Caicos Islands</option>
                            <option value="Tuvalu">Tuvalu</option>
                            <option value="Uganda">Uganda</option>
                            <option value="Ukraine">Ukraine</option>
                            <option value="United Arab Emirates">United Arab Emirates</option>
                            <option value="United Kingdom">United Kingdom</option>
                            <option value="United States">United States</option>
                            <option value="United States Minor Outlying Islands">U.S. Outlying Islands</option>
                            <option value="Uruguay">Uruguay</option>
                            <option value="Uzbekistan">Uzbekistan</option>
                            <option value="Vanuatu">Vanuatu</option>
                            <option value="Venezuela">Venezuela</option>
                            <option value="Viet Nam">Vietnam</option>
                            <option value="Virgin Islands, British">British Virgin Islands</option>
                            <option value="Virgin Islands, U.s.">U.S. Virgin Islands</option>
                            <option value="Wallis and Futuna">Wallis & Futuna</option>
                            <option value="Western Sahara">Western Sahara</option>
                            <option value="Yemen">Yemen</option>
                            <option value="Zambia">Zambia</option>
                            <option value="Zimbabwe">Zimbabwe</option>
                        </select>
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-book margin-r-5"></i> Father Name</strong>
                        <input type="text" class="form-control" v-model="request.father_name"
                            @blur="updateField('father_name',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-phone-square margin-r-5"></i>Father Contact No.</strong>
                        <input type="text" class="form-control" v-model="request.father_contact"
                            @blur="updateField('father_contact',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-envelope margin-r-5"></i>Father Email</strong>
                        <input type="email" class="form-control" v-model="request.father_email"
                            @blur="updateField('father_email',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-book margin-r-5"></i> Mother Name</strong>
                        <input type="text" class="form-control" v-model="request.mother_name"
                            @blur="updateField('mother_name',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-phone-square margin-r-5"></i>Mother Contact No.</strong>
                        <input type="text" class="form-control" v-model="request.mother_contact"
                            @blur="updateField('mother_contact',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-envelope margin-r-5"></i>Mother Email</strong>
                        <input type="email" class="form-control" v-model="request.mother_email"
                            @blur="updateField('mother_email',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-book margin-r-5"></i> Guardian Name</strong>
                        <input type="text" class="form-control" v-model="request.guardian_name"
                            @blur="updateField('guardian_name',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-phone-square margin-r-5"></i>Guardian Contact No.</strong>
                        <input type="text" class="form-control" v-model="request.guardian_contact"
                            @blur="updateField('guardian_contact',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-envelope margin-r-5"></i>Guardian Email</strong>
                        <input type="email" class="form-control" v-model="request.guardian_email"
                            @blur="updateField('guardian_email',$event)" />
                        <hr>
                    </div>
                    <div>
                        <strong><i class="fa fa-home margin-r-5"></i> How did you find out about
                            iACADEMY?</strong>
                        <input type="text" class="form-control" v-model="request.source"
                            @blur="updateField('source',$event)" />
                        <hr>
                    </div>

                    <div>
                        <strong :class="request.good_moral=='No'?'text-red':''"><i
                                class="fa fa-user margin-r-5"></i>Holds a good moral standing in previous
                            school</strong>
                        <select class="form-control" @change="updateField('good_moral',$event)"
                            v-model="request.good_moral">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                        <hr>
                    </div>

                    <div>
                        <strong :class="request.crime=='Yes'?'text-red':''"><i class="fa fa-user margin-r-5"></i>Has
                            been involved of any illegal activities</strong>
                        <select class="form-control" @change="updateField('crime',$event)" v-model="request.crime">
                            <option value="Yes"><span class="text-red">Yes</span></option>
                            <option value="No">No</option>
                        </select>
                        </p>
                        <hr>
                    </div>

                    <div class="" v-if="request.uploaded_requirements.length > 0">
                        <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                class=" text-primary">Initial
                                Requirements</span>
                        </strong>

                        <hr>
                    </div>

                    <div v-for="requirement in request.uploaded_requirements">
                        <strong><i
                                class="fa fa-user margin-r-5"></i>{{ (requirement.type=="2x2_foreign"?"2x2":requirement.type ) }}</strong>
                        <p class="text-muted">
                            <a :href="requirement.path" target="_blank">
                                {{requirement.filename}}</a>
                        </p>
                        <hr>
                    </div>


                    <div v-if="request.schedule_date">
                        <div class="">
                            <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                    class=" text-primary">Interview Schedule
                                </span>
                            </strong>

                            <hr>
                        </div>

                        <div>
                            <strong><i class="fa fa-calendar margin-r-5"></i> Date</strong>
                            <p class="text-muted">
                                {{request.schedule_date}}
                            </p>
                            <hr>
                        </div>

                        <div>
                            <strong><i class="fa fa-clock-o margin-r-5"></i> Time</strong>
                            <p class="text-muted">
                                {{request.schedule_time_from}} - {{request.schedule_time_to}}
                            </p>
                            <hr>
                        </div>
                    </div>

                    <div>
                        <div class="">
                            <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                    class=" text-primary">Health Declaration
                                </span>
                            </strong>

                            <hr>
                        </div>

                        <div>
                            <strong>Hospitalized?</strong>
                            <p class="text-muted">
                                {{request.hospitalized}}
                            </p>
                            <hr>
                        </div>

                        <div>
                            <strong>Hospitalized Reason</strong>
                            <p class="text-muted">
                                {{request.hospitalized_reason}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong>Health Concerns</strong>
                            <p class="text-red">
                                {{request.health_concern}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong>Other Health Concerns</strong>
                            <p class="text-muted">
                                {{request.other_health_concern}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong><i class="fa fa-user margin-r-5"></i> Student Type</strong>
                            <p class="text-muted">
                                {{request.tos?request.tos:'freshman'}}
                            </p>
                            <hr />
                            <select v-if="request.campus == 'Cebu'" class="form-control"
                                @change="updateField('student_type',$event)" v-model="request.tos">
                                <option value="freshman">freshman</option>
                                <option value="foreign">foreign</option>
                                <option value="transferee">transferee</option>
                                <option value="second degree">second degree</option>
                            </select>
                            <select v-if="request.campus == 'Makati'" required class="form-control"
                                @change="updateField('student_type',$event)" v-model="request.tos">
                                <option value="COLLEGE - Freshman">COLLEGE - Freshman</option>
                                <option value="COLLEGE - Transferee">COLLEGE - Transferee</option>
                                <option value="SHS - Freshman">SHS - Freshman</option>
                                <option value="SHS -  Transferee">SHS - Transferee</option>
                                <option value="SHS - DRIVE HomeSchool Program">SHS - DRIVE HomeSchool Program</option>
                                <option value="2ND - DEGREE">2ND - DEGREE</option>
                            </select>
                            </p>

                            <hr>
                        </div>
                        <!-- if second degree  -->

                        <div v-if="request.type == 'other'">
                            <div>
                                <strong><i class="fa fa-home margin-r-5"></i> Company </strong>
                                <input type="text" class="form-control" v-model="request.sd_company"
                                    @blur="updateField('school',$event)" />
                                <hr>
                            </div>
                            <div>
                                <strong><i class="fa fa-user margin-r-5"></i> Position </strong>
                                <input type="text" class="form-control" v-model="request.sd_position"
                                    @blur="updateField('school',$event)" />
                                <hr>
                            </div>
                            <div>
                                <strong><i class="fa fa-book margin-r-5"></i> Previous Degree </strong>
                                <input type="text" class="form-control" v-model="request.sd_degree"
                                    @blur="updateField('school',$event)" />
                                <hr>
                            </div>
                        </div>

                        <!-- end -->
                    </div>
                    <!-- <div>
                        <strong><i class="fa fa-sitemap margin-r-5"></i>Update Status</strong>
                        <div class="row">
                            <div class="text-muted mt-1 col-sm-5">
                                <select name="" class="form-control" required id="select-update-status">
                                    <option value="" disabled selected>--select--</option>
                                    <option value="new">New</option>
                                    <option value="for_interview">For Interview</option>
                                    <option value="scheduled">Scheduled</option>
                                    <option value="For Reservation">For Reservation</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <hr>
                    </div> -->

                    <div>
                        <div class="">
                            <strong><i class="fa  margin-r-5"></i> <span style="font-size:2rem"
                                    class=" text-primary">LRN and Voucher
                                </span>
                            </strong>
                            <hr>
                        </div>

                        <div>
                            <strong>LRN</strong>
                            <p class="text-muted">
                                {{request.lrn}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong>Voucher</strong>
                            <p class="text-muted">
                                <a :href="request.voucher_path" target="_blank">{{request.voucher}}</a>
                            </p>
                            <hr>
                        </div>


                    </div>

                    <div class="text-right">
                        <?php if($userlevel == "2" || $userlevel == "5"): ?>
                        <button v-if="request.status == 'Waiting For Interview' || request.status == 'For Interview'"
                            type="button" data-toggle="modal" data-target="#setFISchedule"
                            class=" btn btn-info">Update/Set FI</button>
                        <button type="button" v-if="request.status == 'New'" @click="deleteApplicant"
                            class=" btn btn-danger">Delete applicant</button>
                        <button type="button"
                            v-if="request.status == 'Waiting For Interview' && request.campus == 'Cebu'"
                            data-toggle="modal" @click="update_status = 'For Interview';" data-target="#myModal" class=" btn
                            btn-primary">For
                            Interview</button>
                        <button type="button" v-if="request.status == 'For Interview'"
                            @click="update_status = 'For Reservation'" data-toggle="modal" data-target="#myModal"
                            class=" btn btn-info">For
                            Reservation</button>
                        <button type="button" v-if="request.status == 'Reserved'"
                            @click="update_status = 'For Enrollment'" data-toggle="modal" data-target="#myModal"
                            class=" btn btn-info">For
                            Enrollment</button>
                        <?php endif; ?>
                        <?php if($userlevel == "2" || $userlevel == "5" || $userlevel == "3"): ?>
                        <a :href="base_url+'admissionsV1/update_requirements/'+slug" class="btn btn-info">Update
                            Requirements</a>
                        <?php endif; ?>
                    </div>

                </div>

            </div>

        </div>
    </div>

    <?php if($userlevel == "2" || $userlevel == "5" || $userlevel == "3"): ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title text-left text-primary">Manual Status Update</h3>
                        <p>Use this tool to manually update statuses for reverting be careful of changing the status of
                            the student if he/she is already enlisted or enrolled</p>
                    </div>
                    <div class="box-body">
                        <form method="post" @submit.prevent="updateStatusManual">
                            <label>Select Status</label>
                            <select required v-model="status_update_manual" class="form-control">
                                <option value="New">New</option>
                                <option value="Waiting For Interview">Waiting For Interview</option>
                                <option value="For Interview">For Interview</option>
                                <option value="For Reservation">For Reservation</option>
                                <option value="Reserved">Reserved</option>
                                <option value="For Enrollment">For Enrollment</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Enlisted">Enlisted</option>
                                <option value="Enrolled">Enrolled</option>
                                <option value="Cancelled">Cancelled Application</option>
                                <option value="Floating">Floating Application</option>
                                <option value="Will Not Proceed">Will Not Proceed</option>
                                <option value="Did Not Reserve">Did Not Reserve</option>
                                <option value="Rejected">Rejected</option>
                                <?php if($userlevel == "2" || $userlevel == "3"): ?>
                                <option value="Withdrawn Before">Withdrawn Enrollment Before Opening of SY</option>
                                <option value="Withdrawn After">Withdrawn Enrollment After Opening of SY</option>
                                <option value="Withdrawn End">Withdrawn Enrollment at the End of the Term</option>
                                <?php endif; ?>
                            </select>
                            <hr />
                            <textarea required class="form-control" v-model="remarks_manual"></textarea>
                            <hr />
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>

                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title text-left text-primary">Entrance Exam</h3>
                    </div>



                    <div class="box-body">

                        <!-- if no existing exam link: To Generate-->

                        <form v-if="!entrance_exam" @submit.prevent="generateExam"
                            style="text-align:center; display:flex; justify-content:center; margin-bottom:2rem;">
                            <div class="col-xs-5">
                                <select name="examID" v-model="exam_type_id" id="selectExamID" class="form-control"
                                    required id="">
                                    <option value="" disabled selected>--select exam type--</option>
                                    <option v-for="ex in exam_types" :value="ex.intID">{{ex.strName}}</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success">
                                Generate Exam Link
                            </button>
                        </form>
                        <!-- end  -->

                        Exam Link: <br />
                        <div class="copy-text" v-if="entrance_exam">
                            {{student_exam_link}}
                            <a href="#" class="btn btn-primary btn-sm"
                                @click.prevent="copyClipBoard(student_exam_link)">Copy</a>
                        </div>
                        <hr />


                        <table class="table table-sm" v-if="entrance_exam">
                            <thead>
                                <th>Section</th>
                                <th>Scores</th>
                                <th>Percentage</th>
                            </thead>
                            <tbody v-if="sections_scores">
                                <tr v-for="score in sections_scores">
                                    <td>
                                        {{score.section}}
                                    </td>
                                    <td>
                                        {{score.score + ' / ' + score.items}}
                                    </td>
                                    <td>
                                        {{score.percentage}}
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="3">&nbsp</td>
                                </tr>

                                <tr>
                                    <td> <strong>Date Submitted:</strong> {{ entrance_exam.date_taken }}</td>
                                    <td></td>
                                    <td> <strong>Total Score:</strong>
                                        {{ entrance_exam.score + '/' + entrance_exam.exam_overall }}</td>
                                </tr>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header">
                        <h3 class="box-title text-left text-primary">Program</h3>
                    </div>

                    <!-- for cebu applicant -->
                    <div v-if="request.campus == 'Cebu'" class="box-body" style="padding:2rem">
                        <div>
                            <strong><i class="fa fa-user margin-r-5"></i>Selected Program</strong>
                            <p class="text-muted">
                                {{request.program}}
                            </p>
                            <hr>
                        </div>
                        <form @submit.prevent="confirmProgram(1)" method="post">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>Select Program to Change</th>
                                        <td>
                                            <select v-model="program_update" @change="changeProgram($event, 1)" required
                                                class="form-control">
                                                <option v-for="program in programs" :value="program.intProgramID">
                                                    {{ program.strProgramDescription }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr />
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Update Program</button>
                            </div>
                        </form>
                    </div>
                    <!-- end -->

                    <!-- for Makati applicant -->
                    <div v-if="request.campus == 'Makati'" class="box-body" style="padding:2rem">
                        <div>
                            <strong><i class="fa fa-user margin-r-5"></i>Selected Program: 1st Choice</strong>
                            <p class="text-muted">
                                {{request.program}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong><i class="fa fa-user margin-r-5"></i>Selected Program: 2nd Choice</strong>
                            <p class="text-muted">
                                {{request.program2}}
                            </p>
                            <hr>
                        </div>
                        <div>
                            <strong><i class="fa fa-user margin-r-5"></i>Selected Program: 3rd Choice</strong>
                            <p class="text-muted">
                                {{request.program3}}
                            </p>
                            <hr>
                        </div>
                        <form @submit.prevent="confirmProgram(1)" class="" method="post">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>Select Program to Change (1st Choice)</th>
                                        <td>
                                            <select v-model="program_update" @change="changeProgram($event,1)" required
                                                class="form-control">
                                                <option v-for="program in filtered_programs"
                                                    :value="program.intProgramID">
                                                    {{ program.strProgramDescription }} </option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr />
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Update Program</button>
                            </div>
                        </form>

                        <form @submit.prevent="confirmProgram(2)" method="post" class="mt-5">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>Select Program to Change (2nd Choice)</th>
                                        <td>
                                            <select v-model="program_update2" @change="changeProgram($event,2)" required
                                                class="form-control">
                                                <option v-for="program in filtered_programs"
                                                    :value="program.intProgramID">
                                                    {{ program.strProgramDescription }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr />
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Update Program</button>
                            </div>
                        </form>

                        <form @submit.prevent="confirmProgram(3)" method="post" class="mt-5">
                            <table class="table table-bordered table-striped">
                                <tbody>
                                    <tr>
                                        <th>Select Program to Change (3rd Choice)</th>
                                        <td>
                                            <select v-model="program_update3" @change="changeProgram($event,3)" required
                                                class="form-control">
                                                <option v-for="program in filtered_programs"
                                                    :value="program.intProgramID">
                                                    {{ program.strProgramDescription }}</option>
                                            </select>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr />
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary">Update Program</button>
                            </div>
                        </form>
                    </div>
                    <!-- end -->




                </div>
            </div>
        </div>
    </div>

    <div class="container">

        <div class="row">

            <!-- for interview -->
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header with-border  font-weight-bold" style="text-align:left; font-weight:bold">
                        <h3 class="box-title text-left text-primary">Payments Made</h3>
                    </div>

                    <div class="box-body" style="padding:2rem">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Request ID</th>
                                    <th>Total Payment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payment in request.payments">
                                    <td>{{payment.description}}</td>
                                    <td>{{payment.request_id}}</td>
                                    <td>₱ {{payment.total_amount_due}}</td>
                                    <td>{{payment.status}}</td>
                                    <td>{{payment.status == 'Paid' ? (payment.date_paid ? payment.date_paid : payment.updated_at) : payment.date_expired  }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- for interview -->
            <div class="col-lg-12">
                <div class="box box-primary">
                    <div class="box-header with-border  font-weight-bold" style="text-align:left; font-weight:bold">
                        <h3 class="box-title text-left text-primary">Status Logs</h3>
                    </div>

                    <div class="box-body" style="padding:2rem">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Admissions Officer</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="log in request.logs">
                                    <td>{{log.date_change}}</td>
                                    <td>{{log.status}}</td>
                                    <td>{{log.admissions_officer}}</td>
                                    <td>
                                        {{log.remarks}}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setFISchedule" role="dialog">
        <form @submit.prevent="submitSchedule" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Update FI Schedule</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="inline-full-name">
                            Select Date
                        </label>
                        <date-picker v-model="request_sched.date" :input-attr="{
                                        required: true,
                                        id: 'date'
                                    }" format="YYYY-MM-DD" lang="en" type="date" placeholder="Select date">
                        </date-picker>
                    </div>
                    <div class="form-group">
                        <label for="inline-full-name">
                            Select Time
                        </label>
                        <date-picker :time-picker-options="
                                            reserve_time_picker_options
                                        " v-model="request_sched.from" type="time" lang="en" format="hh:mm A"
                            @change="checkTime" placeholder="HH:MM AM" :input-attr="{
                                        required: true,
                                        id: 'time_from'
                                    }" input-class="form-control">
                        </date-picker>
                    </div>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
    </div>

    <div class="modal fade" id="myModal" role="dialog">
        <form @submit.prevent="updateStatus" class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <!-- modal header  -->
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">{{update_status}}</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Remarks <span class="text-danger">*</span> </label>
                        <textarea class="form-control" v-model="status_remarks" rows="5" required></textarea>
                    </div>
                </div>
                <div class=" modal-footer">
                    <!-- modal footer  -->
                    <button type="submit" class="btn btn-primary">Submit</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>

        </form>
    </div>
</div>




<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.12/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
new Vue({
    el: '#applicant-container',
    components: {
        'date-picker': DatePicker
    },
    data: {
        request: {
            uploaded_requirements: [],
            waive_app_fee: 0,
            reserve_enroll: 0,
            waive_reason: undefined,
        },
        request_sched: {
            from: "",
            to: "",
        },

        sections_scores: null,
        sy_reference: undefined,
        exam_types: [],
        student_exam_link: "",
        sy: [],
        loader_spinner: true,
        base_url: "<?php echo base_url(); ?>",
        type: "",
        slug: "<?php echo $this->uri->segment('3'); ?>",
        update_status: "",
        status_remarks: "",
        status_update_manual: "",
        remarks_manual: "",
        status_update: "",
        sched: "",
        exam_type_id: "",
        show_edit_name: false,
        show_edit_title: "Edit",
        date_selected: "",
        date_selected_formatted: "",
        filtered_programs: [],
        entrance_exam: undefined,
        programs: [],
        program_update: undefined,
        program_update2: undefined,
        program_update3: undefined,
        program_text: undefined,
        program_text2: undefined,
        program_text3: undefined,
        reserve_time_picker_options: {
            start: "08:00",
            step: "00:30",
            end: "16:00"
        },
        payload: {
            field: undefined,
        },
        delete_applicant: {

        }
    },

    mounted() {

        let url_string = window.location.href;
        let url = new URL(url_string);



        this.loader_spinner = true;
        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.request = data.data.data;
                this.sy_reference = this.request.sy_reference;
                this.loader_spinner = false;
                //this.program_update = this.request.type_id;
                axios.get(base_url + 'admissionsV1/programs/' + this.slug)
                    .then((data) => {
                        this.programs = data.data.programs;
                        this.entrance_exam = data.data.entrance_exam;
                        this.sections_scores = data.data.section_scores;

                        this.status_update_manual = this.request.status;
                        this.sy = data.data.sy;
                        if(this.programs.length > 0)
                            this.filtered_programs = this.programs.filter((prog) => {
                                return prog.type == this.request.type
                            })



                        if (this.entrance_exam && this.entrance_exam.token) {
                            this.student_exam_link = this.base_url + 'unity/student_exam/' + this.slug +
                                '/' + this.entrance_exam.exam_id + '/' + this.entrance_exam.token
                        } else {
                            this.student_exam_link = this.base_url + 'unity/student_exam/' + this.slug +
                                '/' + this.entrance_exam.exam_id + '/submitted'
                        }



                    })
                    .catch((error) => {
                        console.log(error);
                    })

            })
            .catch((error) => {
                console.log(error);
            })

        axios.get(this.base_url + 'admissionsV1/get_exam_types')
            .then((data) => {
                this.exam_types = data.data.exam_types

            })
            .catch((error) => {
                console.log(error);
            })



    },

    methods: {

        copyClipBoard: function(str) {
            var el = document.createElement('textarea');
            el.value = str;
            el.setAttribute('readonly', '');
            el.style = {
                position: 'absolute',
                left: '-9999px'
            };
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);

            Swal.fire({
                showCancelButton: false,
                showCloseButton: true,
                allowEscapeKey: true,
                title: 'Copied',
                text: 'You have copied the exam link to your clipboard',
                icon: 'success',
            });
        },

        generateExam: function() {
            Swal.fire({
                title: 'Generate Exam Link',
                text: "Are you sure you want to generate?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes'
            }).then((result) => {
                if (result.isConfirmed) {


                    let formData = new FormData();
                    formData.append("exam_id", this.exam_type_id)
                    formData.append("student_id", this.slug)
                    formData.append("student_name", this.request.first_name + ' ' + this.request
                        .last_name)

                    axios.post("<?php echo base_url();?>" + "examination/generate_exam", formData)
                        .then(function(data) {
                            if (data.data.success) {
                                Swal.fire(
                                    'SUCCESS!',
                                    'Exam link has been generated.',
                                    'success'
                                )
                                setTimeout(() => {
                                    location.reload();
                                }, 1500);
                            }
                        })
                        .catch(function(error) {
                            console.log(error);
                        });


                }
            })
        },

        showEdit: function() {
            if (this.show_edit_name) {
                this.show_edit_name = false;
                this.show_edit_title = "Edit";
            } else {
                this.show_edit_name = true;
                this.show_edit_title = "Hide";
            }
        },
        checkTime: function() {

            if (this.request.from && this.request.to) {
                if (this.request.from >= this.request.to) {
                    Swal.fire(
                        'Failed!',
                        "Invalid time, please select valid time.",
                        'error'
                    )

                    this.request.to = "";

                }
            }

        },
        changeProgram: function(event, type) {
            //console.log(event.target[event.target.selectedIndex].text);
            if (type == '1') {
                this.program_text = event.target[event.target.selectedIndex].text;
            }

            if (type == '2') {
                this.program_text2 = event.target[event.target.selectedIndex].text;
            }
            if (type == '3') {
                this.program_text3 = event.target[event.target.selectedIndex].text;
            }
        },
        updateField: function(type, event) {
            //this.loading_spinner = true;
            <?php if($userlevel == "2" || $userlevel == "5" || $userlevel == "3"):  ?>
            Swal.fire({
                showCancelButton: false,
                showCloseButton: false,
                allowEscapeKey: false,
                title: 'Please wait',
                text: 'Processing update',
                icon: 'info',
            })

            Swal.showLoading();
            this.payload = {
                field: type,
                value: event.target.value,
                admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
            };

            axios
                .post(api_url + 'admissions/student-info/update-field/custom/' + this.slug, this.payload, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {

                    Swal.hideLoading();
                    Swal.fire({
                        showCancelButton: false,
                        showCloseButton: true,
                        allowEscapeKey: false,
                        title: 'Successfully Updated',
                        text: 'Field Updated',
                        icon: 'success',
                    });
                    //document.location = base_url + 'admissionsV1/view_lead/' + this.slug;


                });
            <?php else: ?>
            Swal.fire({
                showCancelButton: false,
                showCloseButton: true,
                allowEscapeKey: false,
                title: 'Unauthorized Access',
                text: 'You do not have access to this function',
                icon: 'warning',
            }).then(data => {
                document.location = base_url + 'admissionsV1/view_lead/' + this.slug;

            });
            <?php endif; ?>


        },
        confirmProgram: function(type) {

            this.loading_spinner = true;
            Swal.fire({
                showCancelButton: false,
                showCloseButton: false,
                allowEscapeKey: false,
                title: 'Please wait',
                text: 'Processing update',
                icon: 'info',
            })

            Swal.showLoading();
            this.payload = {
                field: type == 1 ? 'type_id' : type == 2 ? 'type_id2' : 'type_id3',
                value: type == 1 ? this.program_update : type == 2 ? this.program_update2 : this
                    .program_update3,
                program: type == 1 ? this.program_text : type == 2 ? this.program_text2 : this
                    .program_text3,
                admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
            };

            axios
                .post(api_url + 'admissions/student-info/update-field/custom/' + this.slug, this.payload, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                .then(data => {

                    Swal.hideLoading();
                    document.location = base_url + 'admissionsV1/view_lead/' + this.slug;


                });


        },


        deleteApplicant: function() {
            this.loading_spinner = true;
            Swal.fire({
                title: "Delete Applicant",
                text: "Are you sure you want to delete?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "warning",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return axios.delete(api_url + 'admissions/student-info/' + this.slug, this
                            .delete_applicant, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            this.is_done = true;

                            if (data.data.success) {

                                Swal.fire({
                                    title: "SUCCESS",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(res => {
                                    document.location = base_url +
                                        "admissionsV1/view_all_leads";
                                });

                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )

                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {}
            })
        },
        submitSchedule: function() {

            let time_from = moment(this.request_sched.from).format('LT');
            let time_to = moment(this.request_sched.from).add(30, 'minutes').format('LT');

            this.request_sched.date = moment(this.request_sched.date).format("YYYY-MM-DD");

            this.request_sched.slug = this.slug;
            this.request_sched.time_from = moment(time_from, ["h:mm A"]).format("HH:mm")
            this.request_sched.time_to = moment(time_to, ["h:mm A"]).format("HH:mm")



            Swal.fire({
                title: "Submit Schedule",
                text: "Are you sure you want to submit?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    return axios
                        .post(api_url + 'interview-schedules/admin/set_date', this
                            .request_sched, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            this.is_done = true;

                            if (data.data.success) {

                                Swal.fire({
                                    title: "SUCCESS",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(res => {
                                    location.reload();
                                });

                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )

                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {}
            })
        },
        updateStatus: function() {


            Swal.fire({
                title: 'Update Status',
                text: "Are you sure you want to update?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios
                        .post(api_url + 'admissions/student-info/' + this.slug +
                            '/update-status', {
                                status: this.update_status,
                                remarks: this.status_remarks,
                                admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
                            }, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            if (data.data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )
                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                // if (result.isConfirmed) {
                //     Swal.fire({
                //         icon: result?.value.data.success ? "success" : "error",
                //         html: result?.value.data.message,
                //         allowOutsideClick: false,
                //     }).then(() => {
                //         if (reload && result?.value.data.success) {
                //             if (reload == "reload") {
                //                 location.reload();
                //             } else {
                //                 window.location.href = reload;
                //             }
                //         }
                //     });
                // }
            })
        },
        updateStatusManual: function() {

            Swal.fire({
                title: 'Update Status',
                text: "Are you sure you want to update?",
                showCancelButton: true,
                confirmButtonText: "Yes",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {

                    return axios
                        .post(api_url + 'admissions/student-info/' + this.slug +
                            '/update-status', {
                                status: this.status_update_manual,
                                remarks: this.remarks_manual,
                                admissions_officer: "<?php echo $user['strFirstname'] . '  ' . $user['strLastname'] ; ?>"
                            }, {
                                headers: {
                                    Authorization: `Bearer ${window.token}`
                                }
                            })
                        .then(data => {
                            if (data.data.success) {
                                Swal.fire({
                                    title: "Success",
                                    text: data.data.message,
                                    icon: "success"
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Failed!',
                                    data.data.message,
                                    'error'
                                )
                            }
                        });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                // if (result.isConfirmed) {
                //     Swal.fire({
                //         icon: result?.value.data.success ? "success" : "error",
                //         html: result?.value.data.message,
                //         allowOutsideClick: false,
                //     }).then(() => {
                //         if (reload && result?.value.data.success) {
                //             if (reload == "reload") {
                //                 location.reload();
                //             } else {
                //                 window.location.href = reload;
                //             }
                //         }
                //     });
                // }
            })
        }


    }

})
</script>