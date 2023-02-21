
    <section id="hero" class="section section_port relative">
       <div class="custom-container md:h-[500px] relative z-1">
           <!-- parallax object? -->
           <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png"
               class="absolute top-0 md:right-[25%] hidden md:block" alt="" data-scroll-speed="4" data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/yellow-poly.png"
               class="absolute top-[10%] md:left-[17%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />
           <img src="<?php echo $img_dir; ?>home-poly/red-poly.png"
               class="absolute top-[30%] md:left-[0%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/peach-poly.png"
               class="absolute top-[25%] md:left-[33%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/lyellow-poly.png"
               class="absolute top-[50%] md:right-[0%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <img src="<?php echo $img_dir; ?>home-poly/lblue-poly.png"
               class="absolute top-[20%] md:right-[10%] hidden md:block" alt="" data-scroll-speed="4"
               data-aos="zoom-in" />

           <!-- parallax object end -->
           <div class="custom-container relative h-full mb-[100px] md:mb-[10px]">
               <div class="md:flex mt-[100px] md:mt-0 h-full items-center justify-center">
                   <div class="md:w-12/12 py-3">

                       <div class=" block mx-auto mt-[60px]" data-aos="fade-up">
                           <h1 class="text-4xl font-[900] text-center color-primary">
                               iACADEMY
                           </h1>
                           <h1 class="text-4xl uppercase text-center color-primary">
                               School management system
                           </h1>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </section>

   <div class="custom-container" id="adminssions-form" style="margin-top:10px;">        
       <div class="color-primary">
           <h4 class="font-medium text-2xl mb-5">
               Student Information Sheet</h4>
           <p>Hello future Game Changers! Kindly fill out your information sheet. If you have any questions, feel free
               to email us at <strong><u>admissionscebu@iacademy.edu.ph</u></strong> </p>
       </div>

       <form @submit.prevent="
            customSubmit(
                'submit',
                'Submit Details',
                'form',
                request,
                'admissions/student-info'
            )
        " method="post">

           <div class="flex md:space-x-5 mb-6 mt-10">
               <div class="md:w-1/2 w-full">
                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Email <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="email" required v-model="request.email">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Re-type Email <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="email" required v-model="request.email_confirmation">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               First Name <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.first_name">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Middle Name
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" v-model="request.middle_name">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Last Name <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.last_name">
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Mobile Number <span class="text-red-500">*</span>
                           </label>
                           <the-mask
                           class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                           :mask="['(+63) ###-###-####']" type="text" v-model="request.mobile_number" required masked="true" placeholder="(+63) XXX-XXX-XXXX"></the-mask>
                           <!-- <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="number" required v-model="request.mobile_number"> -->
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Telephone Number
                           </label>
                           <the-mask
                           class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                           :mask="['(+63) ###-####']" type="text" v-model="request.tel_number" masked="true" placeholder="(+63) XXX-XXXX"></the-mask>
                           <!-- <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="number" v-model="request.tel_number"> -->
                       </div>
                   </div>

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Address <span class="text-red-500">*</span>
                           </label>
                           <textarea required
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               name="" rows="4" v-model="request.address">></textarea>

                       </div>
                   </div>


                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Birthday <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="date" required v-model="request.date_of_birth">
                       </div>
                   </div>

                    <div class="form-group mb-6">
                        <label for="">
                            Country of citizenship
                            <span class="text-danger">*</span>
                        </label>

                        <div>
                        <select required name="citizenship" v-model="request.citizenship">
                                <option>country</option>
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
                                <option value="South Georgia and the South Sandwich Islands">South Georgia & South Sandwich Islands</option>
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
                        </div>

                        <div>
                            <input
                                type="radio"
                                required
                                name="citizenship"
                                value="Foreign"
                                v-model="request.citizenship"
                            />
                            Foreign
                        </div>
                    </div>


                    <div class="form-group mb-6">
                        <label for=""
                            >Do you hold good moral standing in your
                            previous school?
                            <span class="text-danger">*</span>
                        </label>

                        <div>
                            <input
                                type="radio"
                                required
                                name="good_moral"
                                v-model="request.good_moral"
                                value="Yes"
                            />
                            Yes
                        </div>

                        <div>
                            <input
                                type="radio"
                                required
                                name="good_moral"
                                value="No"
                                v-model="request.good_moral"
                            />
                            No
                        </div>
                    </div>

                    <div class="form-group  mb-6">
                        <label for=""
                            >Have you been involved of any illegal
                            activities?
                            <span class="text-danger">*</span>
                        </label>

                        <div>
                            <input
                                type="radio"
                                required
                                name="crime"
                                v-model="request.crime"
                                value="Yes"
                            />
                            Yes
                        </div>

                        <div>
                            <input
                                type="radio"
                                required
                                name="crime"
                                value="No"
                                v-model="request.crime"
                            />
                            No
                        </div>
                    </div>      
                    <div class="mb-5">
                        <hr />
                        <div class="form-group">
                            <h4 class="mb-3">
                                Health Conditions
                                <span class="text-danger"></span>
                            </h4>

                            <label for=""
                                >Have you been hospitalized before?
                                <span class="text-danger">*</span>
                            </label>

                            <div>
                                <input
                                    type="radio"
                                    required
                                    name="hospitalized"
                                    v-model="request.hospitalized"
                                    value="Yes"
                                />
                                Yes
                            </div>

                            <div>
                                <input
                                    type="radio"
                                    required
                                    name="hospitalized"
                                    value="No"
                                    v-model="request.hospitalized"
                                />
                                No
                            </div>
                        </div>
                        <div
                            class="form-group"
                            v-if="request.hospitalized == 'Yes'"
                        >
                            <label for=""
                                >Reason <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                required
                                class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                v-model="request.hospitalized_reason"
                            />
                        </div>

                        <div class="form-group">
                            <label for=""
                                >Do you have any of the following? (check
                                all that apply)                                
                            </label>

                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    v-model="request.health_concerns"
                                    value="Diabetes"                                  
                                />
                                Diabetes
                            </div>

                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="Allergies"
                                    v-model="request.health_concerns"
                                />
                                Allergies
                            </div>

                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="High Blood"
                                    v-model="request.health_concerns"
                                />
                                High Blood
                            </div>
                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="Anemia"
                                    v-model="request.health_concerns"
                                />
                                Anemia
                            </div>
                            <div>
                                <input
                                    type="checkbox"
                                    name="health_concern"
                                    value="Others"
                                    v-model="request.health_concerns"
                                />
                                Others (please specify)
                            </div>
                            <div
                                v-if="
                                    request.health_concerns.includes(
                                        'Others'
                                    )
                                "
                            >
                                <input                                    
                                    type="text"
                                    class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    required
                                    value=""
                                    v-model="request.health_concern_other"
                                />
                            </div>
                        </div>

                        <div class="form-group">
                            <label for=""
                                >Other health concerns/conditions the school
                                should know about
                                <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                required
                                class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                v-model="request.other_health_concern"
                            />
                        </div>
                    </div>
                    <div class="mb-6">
                       <div class="md:w-5/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Applying For <span class="text-red-500">*</span>
                           </label>
                           <div class="d-flex align-items-center" v-for="t in programs" :key="t.id">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" :id="'progId-' + t.id"
                                   @click="filterProgram(t.type,t.title)" name="" :value="t.id" required />
                               <label :for="'progId-' + t.id"> {{ t.title }} {{ t.strMajor != "None" ? "with Major in " + t.strMajor: '' }}</label>
                           </div>
                       </div>
                   </div>

                   <!-- <div class="mb-6 hidden">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Applying For <span class="text-red-500">*</span>
                           </label>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Software Engineering
                           </div>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Game Development
                           </div>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Animation
                           </div>
                           <div class="d-flex align-items-center">
                               <input type="checkbox" class="mr-2 admissions_submission_cb" name="" value="1"
                                   required />
                               Multimedia Arts and Design
                           </div>
                       </div>
                   </div> -->

                   <div class="mb-6">
                       <div class="md:w-4/5">
                           <label class="block t color-primary font-bold  mb-3  pr-4" for="inline-full-name">
                               Previous School <span class="text-red-500">*</span>
                           </label>
                           <input
                               class="bg-gray-200 appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                               type="text" required v-model="request.school">
                       </div>
                   </div>
               </div>
               <div class="md:w-1/2 md:block hidden">
                   <div class="relative">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/rocket.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/blue.png" class="max-w-full h-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/red.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/tablet.png" class="max-w-full h-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/gamepad.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/yel.png"
                                   class="max-w-full h-auto mx-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/peach.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2 mt-5">
                               <img src="<?php echo $img_dir; ?>admissions/form/cam.png"
                                   class="max-w-full h-auto mx-auto">
                           </div>
                       </div>
                   </div>

                   <div class="relative mt-[70px]">
                       <div class="md:flex md:space-x-4 items-center">
                           <div class="md:w-1/2">
                               <img src="<?php echo $img_dir; ?>admissions/form/laptop.png" class="max-w-full h-auto">
                           </div>
                           <div class="md:w-1/2 mt-5">
                               <img src="<?php echo $img_dir; ?>admissions/form/dblue.png"
                                   class="max-w-full h-auto mx-auto">
                           </div>
                       </div>
                   </div>

               </div>
           </div>


           <div class="text-center color-primary mt-[50px]">
               iACADEMY shall retain in confidence all confidential information concerning and involving every
               student and the school.
               <a href=" https://iacademy.edu.ph/privacypolicy.htm" target="_blank" class="underline font-bold">
                   https://iacademy.edu.ph/privacypolicy.htm</a>

               <div class="mt-4">
                   <input type="checkbox" required id="agreement"> <label for="agreement" class="italic">I have read and
                       I
                       agree to the
                       said
                       policy.</label>
               </div>
           </div>

           <hr class="my-5 bg-gray-400 h-[3px]" />


           <div class=" text-right">
                <div v-if="loading_spinner" class="lds-ring"><div></div><div></div><div></div><div></div></div>
                <div v-else>
                    <button type="submit">                            
                        <img src="<?php echo $img_dir; ?>admissions/form/Asset 10.png">
                    </button>

                    <button type="button">
                        <img src="<?php echo $img_dir; ?>admissions/form/Asset 9.png">
                    </button>
               </div>
           </div>

       </form>
   </div>
   <style>

   </style>



   <script>
new Vue({
    el: "#adminssions-form",
    data: {
        request: {
            type_id: "",
            date_of_birth: "",
            program: "",
            health_concerns: [],
            syid: "<?php echo $current_term; ?>",
        },
        loading_spinner: false,
        programs: [],
        programs_group: [],
        types: [],
        base_url: "<?php echo base_url(); ?>",
    },
    mounted() {

        axios
            .get(this.base_url + 'site/view_active_programs', {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {
                this.programs = data.data.data;                
            })
            .catch((e) => {
                console.log("error");
            });

        axios
            .get(api_url + 'admissions/student-info/types', {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })
            .then((data) => {
                this.types = data.data.data;
                setTimeout(() => {
                    $(".admissions_submission_cb").on("click", e => {
                        $(".admissions_submission_cb")
                            .not(e.currentTarget)
                            .prop("checked", false);
                        if ($(e.currentTarget).is(":checked")) {
                            this.request.type_id = e.currentTarget.value;
                            $(".admissions_submission_cb").removeAttr(
                                "required"
                            );
                        } else {
                            $(".admissions_submission_cb").attr(
                                "required",
                                true
                            );
                        }
                    });
                }, 500);
            })
            .catch((e) => {
                console.log("error");
            });

    },

    methods: {
        submitForm: function() {
            //console.log(this.request);
        },
        unmaskedValue: function(){
            var val = this.$refs.input.clean
            console.log(val);
        },

        filterProgram: function(type,title) {
            var group = _.filter(this.programs, function(o) {
                return o.type == type;
            });
            var others = _.filter(this.programs, function(o) {
                return o.type == "others";
            });
            this.programs_group = _.concat(group, others);
            this.request.program = title;            

            setTimeout(() => {
                $(".admissions_submission_pg").on("click", e => {
                    $(".admissions_submission_pg")
                        .not(e.currentTarget)
                        .prop("checked", false);
                    if ($(e.currentTarget).is(":checked")) {
                        this.request.program_id = e.currentTarget.value;                        
                        $(".admissions_submission_pg").removeAttr("required");

                    } else {
                        $(".admissions_submission_pg").attr("required", true);
                    }
                });
            }, 500);
        },

        customSubmit: function(type, title, text, data, url, redirect) {

            this.loading_spinner = true;
            if(this.request.mobile_number.length < 18){
                this.loading_spinner = false;
                Swal.fire(
                    'Failed!',
                    "Please fill in mobile number",
                    'warning'
                )
            }
            else{
                if (this.request.health_concerns.includes("Others")) {
                    const hasOther = this.request.health_concerns.indexOf("Others");
                    this.request.health_concerns.splice(
                        hasOther,
                        1,
                        this.request.health_concern_other
                    );
                }


                this.request.health_concern = this.request.health_concerns.join(
                    ", "
                );

                axios
                    .post(api_url + url, data, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    })
                    .then(data => {
                        this.is_done = true;

                        if (data.data.success) {

                            this.loading_spinner = false;
                            var ret = data.data.data;

                            Swal.fire({
                                title: "SUCCESS",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.href = "<?php echo base_url(); ?>site/initial_requirements/" + ret.slug;
                            });

                        } else {
                            this.loading_spinner = false;
                            Swal.fire(
                                'Failed!',
                                data.data.message,
                                'error'
                            )
                        }
                    });
            }
        },
    },
});
   </script>