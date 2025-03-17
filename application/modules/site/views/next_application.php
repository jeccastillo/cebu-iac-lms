<div class="custom-container">
    <a href="https://iacademy.edu.ph/"
        class="flex mt-10 items-center gap-x-2 text-[#666666] cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg" width="8" height="15" viewBox="0 0 8 15"
            fill="none">
            <path d="M7 1L1 7.5L7 14" stroke="#666666" stroke-width="2" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg> BACK </a>
</div>
<div class=" block mx-auto mt-[60px]" data-aos="fade-up">
    <h1 class="text-4xl font-[900] text-center color-primary"> iACADEMY </h1>
</div>
<div class="custom-container max-w-[1080px]" id="adminssions-form" style="margin-top:10px;">
    <div class="color-primary text-center">
        <h4 class="font-medium text-2xl mb-5"> Application Form <br />
        </h4>
        <p>This program is for social media users looking to turn their passion into a professional
            career. It is ideal for those exploring opportunities in the field and who are open to
            studying various cases with an objective mindset to gain valuable knowledge and wisdom.
            Interested applicants may head to our website to start the application process. </p>
    </div>
    <ul>
        <h5>Minimum Requirements</h5>
        <li>&#x2010;Applicants must be at least 17 yo</li>
        <li>&#x2010;Must hold Filipino Citizenship</li>
        <li>&#x2010;Must have at least 2 active social media handles</li>
    </ul>
    <form @submit.prevent="
            customSubmit(
                'submit',
                'Submit Details',
                'form',
                request,
                'admissions/student-info'
            )
        " method="post" class="">
        <div v-if="true" class="flex flex-wrap md:space-x-5 mb-6 mt-10 justify-center ">
        </div>
        <div v-if="true" class=" mb-6 mt-10">
            <h2 class="color-primary font-bold text-2xl mb-4">Website Application Detail
                Requirements </h2>
            <h4 class="color-primary font-bold text-xl">BASIC INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100 rounded-lg mt-5 py-5 px-2.5">
                <div class="flex flex-wrap gap-x-2 gap-y-2 mb-4">
                    <div id="first-name" class="flex-grow">
                        <label class="block color-primary font-bold  mb-3  pr-4"> First Name <span
                                class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="first_name" required v-model="request.first_name">
                    </div>
                    <div id="middle-name" class="flex-grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Middle Name
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="middle_name" v-model="request.middle_name">
                    </div>
                    <div class="flex-grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Last Name <span
                                class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="last_name" required v-model="request.last_name">
                    </div>
                    <div id="suffix" class="basis-[100px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Suffix </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="request.suffix">
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-2 gap-y-2 mb-4">
                    <div id="date-birth" class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Date of Birth
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="date" v-model="request.date_of_birth" required>
                    </div>
                    <div id="place-birth" class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Place of Birth
                            <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="place_of_birth" v-model="request.place_of_birth"
                            required>
                    </div>
                    <div id="gender" class="basis-[120px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Gender <span
                                class="text-red-500">*</span>
                        </label>
                        <select v-model="request.gender" required
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option disabled value="">--options--</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-2 gap-y-2 ">
                    <div id="citizenship-base" class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Citizenship
                            <span class="text-red-500">*</span>
                        </label>
                        <select v-model="request.citizenship"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option disabled value="">--Select options--</option>
                            <option v-for="country in countries" :value="country">{{country}}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="true" class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">CONTACT INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">CONTACT DETAILS</h5>
                <div
                    class="grid gap-x-16 grid-cols-[repeat(auto-fit,_minmax(0,420px))] gap-y-2 mb-4 ">
                    <div id="email" class="">
                        <label class="block  color-primary font-bold mb-3 pr-4"> Email Address <span
                                class="text-red-500">*</span>
                        </label>
                        <input v-model="request.email"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="email" name="confirm-email" required>
                    </div>
                    <div id="email-confirm">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Confirm Email
                            Address <span class="text-red-500">*</span>
                        </label>
                        <input v-model="request.email_confirmation"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="email" name="confirm-email" required>
                    </div>
                </div>
                <div
                    class="grid grid-cols-[repeat(auto-fit,_minmax(0,420px))] gap-x-16 gap-y-2 mb-4">
                    <div class="">
                        <label class="block color-primary font-bold mb-3 pr-4"> Mobile Number <span
                                class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-x-2.5">
                            <select v-model="code1"
                                class="w-1/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option v-for="code in codes" :value="code.dialCode" required>
                                    {{ code.flag}} {{code.dialCode}}
                                </option>
                            </select>
                            <input
                                class="w-2/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="number" v-model="request.mobile_number" required>
                        </div>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Confirm Mobile
                            Number <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-x-2.5">
                            <select v-model="code2"
                                class="w-1/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option v-for="code in codes" :value="code.dialCode" required>
                                    {{ code.flag}} {{code.dialCode}}
                                </option>
                            </select>
                            <input
                                class="w-2/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="number" v-model="request.mobile_number_confirmation" required>
                        </div>
                    </div>
                </div>
                <div
                    class="grid grid-cols-[repeat(auto-fit,_minmax(0,420px))] gap-x-16 gap-y-2 mb-4">
                    <div class="">
                        <label class="block color-primary font-bold mb-3 pr-4"> Landline Number
                            <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-x-2.5">
                            <input
                                class="w-2/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="number" v-model="request.tel_number" required>
                        </div>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Confirm Landline
                            Number <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-x-2.5">
                            <input
                                class="w-2/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="number" v-model="request.tel_number_confirmation" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">HOME ADDRESS</h5>
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div id="" class="">
                        <label class="block color-primary font-bold mb-3 pr-4"> Home
                            Number/Street/Subdivision <span class="text-red-500">*</span>
                        </label>
                        <input v-model="homeAddressObj.address"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="address" type="text" required>
                    </div>
                    <div id="">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Barangay
                        </label>
                        <input v-model="homeAddressObj.barangay"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="barangay" type="text" required>
                    </div>
                    <div id="">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> City <span
                                class="text-red-500">*</span>
                        </label>
                        <input v-model="homeAddressObj.city"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="city" required>
                    </div>
                </div>
            </div>
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">CURRENT ADDRESS</h5>
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div id="" class="">
                        <label class="block color-primary font-bold mb-3 pr-4"> Home
                            Number/Street/Subdivision <span class="text-red-500">*</span>
                        </label>
                        <input v-model="currentAddressObj.address"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="address" type="text" required>
                    </div>
                    <div id="">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Barangay
                        </label>
                        <input v-model="currentAddressObj.barangay"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="barangay" type="text" required>
                    </div>
                    <div id="">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> City <span
                                class="text-red-500">*</span>
                        </label>
                        <input v-model="currentAddressObj.city"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="city" required>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="true" class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">EMERGENCY CONTACT PERSON</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100 rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div>
                        <label class="block  color-primary font-bold mb-3 pr-4"> First Name </label>
                        <input v-model="request.emergency_contact_first_name"
                            class="parent-info bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="full_name" required>
                    </div>
                    <div>
                        <label class="block color-primary font-bold mb-3 pr-4"> Middle Name </label>
                        <input v-model="request.emergency_contact_middle_name"
                            class="parent-info bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="full_name" required>
                    </div>
                    <div>
                        <label class="block  color-primary font-bold mb-3 pr-4"> Last Name </label>
                        <input v-model="request.emergency_contact_last_name"
                            class="parent-info bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="full_name" required>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Relationship
                        </label>
                        <input v-model="request.emergency_contact_relationship"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="job_title" required />
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Contact Number
                        </label>
                        <input v-model="request.emergency_contact_contact_number"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="number" name="number" required>
                    </div>
                    <div class="md:col-[2/5]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Address </label>
                        <input v-model="request.emergency_contact_address"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" required>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="true" class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">EDUCATIONAL BACKGROUND</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow">
                        <label class="block color-primary font-bold mb-3 pr-4"> Last School Attended
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="request.last_school_attended">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow lg:grow-0">
                        <label class="block color-primary font-bold mb-3 pr-4"> Grade/Year Level
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="number" v-model="request.grade_year_level">
                    </div>
                    <div class="grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Program/Strand/Degree earned </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" name="strand" v-model="request.degree">
                    </div>
                </div>
            </div>
            <div v-if="isOnList"
                class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">Register your school if not in the list </h5>
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow">
                        <label class="block color-primary font-bold mb-3 pr-4"> School Name </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_name" required>
                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> City </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_city" required>
                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> State/Province
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_province" required>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Country </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_country" required>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="true" class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">ADDITIONAL INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="grid grid-cols-[repeat(auto-fit,minmax(400px,1fr))] gap-6">
                <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                    <h5 class="color-primary mb-2.5">Do you hold good moral standing? <span
                            class="text-red-500">*</span> </h5>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio" class="mr-1" value="Yes" name="good_moral" required
                            v-model="request.good_moral"> Yes </label>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio" class="mr-1" value="No" name="good_moral" required
                            v-model="request.good_moral"> No </label>
                </div>
                <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                    <h5 class="color-primary mb-2.5">Have you been involved in any illegal
                        activities? <span class="text-red-500">*</span>
                    </h5>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio" class="mr-1" value="Yes" name="crime" required
                            v-model="request.crime"> Yes </label>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio" class="mr-1" value="No" name="crime" required
                            v-model="request.crime"> No </label>
                </div>
            </div>
            <div class="grid grid-cols-[repeat(auto-fit,minmax(400px,1fr))] gap-6">
                <div>
                    <label class="block color-primary font-bold text-base mt-2 "> Do you have any
                        health condition/s that the school should be aware of? <span
                            class="text-red-500">*</span>
                    </label>
                    <label class="block color-primary italic text-sm mb-1"> (Type "none" if you do
                        not have any) </label>
                    <input
                        class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                        type="text" v-model="request.other_health_concern" required>
                </div>
            </div>
            <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                <h5 class="color-primary mb-2.5">Do you have any of the following? (check all that
                    apply) </h5>
                <label class="custom-checkbox">
                    <input type="checkbox" v-model="request.health_concern" value="Diabetes">
                    <span class="custom-checkbox-button"></span> Diabetes </label>
                <label class="custom-checkbox">
                    <input type="checkbox" v-model="request.health_concern" value="Allergies">
                    <span class="custom-checkbox-button"></span> Allergies </label>
                <label class="custom-checkbox">
                    <input type="checkbox" v-model="request.health_concern" value="High Blood">
                    <span class="custom-checkbox-button"></span> High Blood </label>
                <label class="custom-checkbox">
                    <input type="checkbox" v-model="request.health_concern" value="Anemia">
                    <span class="custom-checkbox-button"></span> Anemia </label>
                <label class="custom-checkbox">
                    <input type="checkbox" v-model="request.health_concern" value="Others">
                    <span class="custom-checkbox-button"></span> Others (please specify) </label>
                <label v-if="request.health_concern.includes('Others')"
                    class="block color-primary mb-1 ml-1.5">
                    <input type="text"
                        class="mr-1 bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                        required v-model="request.health_concern_other">
                </label>
            </div>
        </div>
        <div v-if="true" class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">HOW DID YOU LEARN ABOUT iACADEMY AND ITS
                PROGRAMS?</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="grid grid-cols-[repeat(auto-fill,minmax(400px,1fr))] gap-6">
                <div>
                    <div class="border-[1px] border-neutral-100 p-2.5 mb-4 rounded-lg">
                        <h5 class="color-primary mb-2.5">How did you know about us?<span
                                class="text-red-500">*</span>
                        </h5>
                        <div class="flex ">
                            <div class="w-1/2">
                                <template v-for="source,index in sourceList">
                                    <label v-if="index <= 4" class="custom-checkbox mb-1">
                                        <input type="checkbox" :id="index" :name="source"
                                            :value="source" v-model="sources">
                                        <span class="custom-checkbox-button"></span>
                                        {{source}}
                                    </label>
                                </template>
                            </div>
                            <div class="w-1/2">
                                <template v-for="source,index in sourceList">
                                    <label v-if="index >= 5" class="custom-checkbox mb-1">
                                        <input type="checkbox" class="" :id="index" name="source"
                                            :value="source.toLowerCase()" v-model="sources">
                                        <span class="custom-checkbox-button"></span>
                                        {{source }} {{index >= 7? "(please specify)" : ""}}
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div>
                    <div v-if="sources.includes('event')" v-bind:key="1"
                        class="border-[1px] border-neutral-100 p-2.5 mb-4 rounded-lg ">
                        <div class="">
                            <h5 class="color-primary mb-2.5">Events (please specify)</h5>
                            <input v-model="sourcesSpecify.event"
                                class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text" required>
                        </div>
                    </div>
                    <!-- v-if="sources === 'referral'" -->
                    <div v-if="sources.includes('referral')"
                        class="border-[1px] border-neutral-100 p-2.5 mb-4 rounded-lg">
                        <h5 class="color-primary mb-2.5">Referred by<span
                                class="text-red-500">*</span></h5>
                        <div class="flex">
                            <div class="w-full">
                                <div class="grid grid-cols-[repeat(2,_1fr)]">
                                    <template v-for="refer,index in referredList">
                                        <label v-if="index < 1" class="custom-radio mb-1">
                                            <input type="radio" class="mr-1 " :id="index"
                                                name="refer" :value="refer.toLowerCase()"
                                                v-model="sourcesSpecify.referral" required>
                                            <span class="custom-radio-button"></span>
                                            {{refer}}
                                        </label>
                                    </template>
                                    <label class="custom-radio mb-1">
                                        <input type="radio" class="mr-1 " id="index" name="refer"
                                            value="teacher" v-model="sourcesSpecify.referral"
                                            required>
                                        <span class="custom-radio-button"></span> Teacher/Guardian
                                    </label>
                                </div>
                                <template v-for="refer,index in referredList">
                                    <label v-if="index > 1" class="custom-radio mb-1">
                                        <input type="radio" :id="index" name="refer"
                                            :value="refer.toLowerCase()"
                                            v-model="sourcesSpecify.referral" required>
                                        <span class="custom-radio-button"></span>
                                        {{refer}}
                                    </label>
                                </template>
                                <label class="custom-radio mb-1">
                                    <input type="radio" name="refer" value="iacademy"
                                        v-model="sourcesSpecify.referral" required>
                                    <span class="custom-radio-button"></span> iACADEMY
                                    Student/Alumni/Applicant/Employee/Partner </label>
                                <input
                                    class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="text" required v-model="refferalName"
                                    placeholder="Name of your referrer">
                            </div>
                        </div>
                    </div>
                    <div v-if="sources.includes('others')" v-bind:key="2"
                        class="border-[1px] border-neutral-100 p-2.5 mb-4 rounded-lg ">
                        <div class="">
                            <h5 class="color-primary mb-2.5">Others (please specify)</h5>
                            <input
                                class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text" v-model="sourcesSpecify.others" required>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="true" class=" mb-6 mt-10">
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow">
                        <label class="block color-primary font-bold mb-3 pr-4"> Link to social media
                            handles: </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="request.social_media_handles">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow lg:grow-0">
                        <label class="block color-primary font-bold mb-3 pr-4"> Goverment ID
                        </label>
                        <input @change="attachFile" type="file" id="government_id" required />
                    </div>
                    <div class="grow lg:grow-0">
                        <label class="block color-primary font-bold mb-3 pr-4"> Diploma </label>
                        <input @change="attachFile" type="file" id="diploma" required />
                    </div>
                    <div class="grow lg:grow-0">
                        <label class="block color-primary font-bold mb-3 pr-4"> Transcript </label>
                        <input @change="attachFile" type="file" id="transcript" required />
                    </div>
                    <div class="grow lg:grow-0">
                        <label class="block color-primary font-bold mb-3 pr-4"> Proof </label>
                        <input @change="attachFile" type="file" id="proof" required />
                    </div>
                    <div class="grow lg:grow-0">
                        <label class="block color-primary font-bold mb-3 pr-4"> Video Introduction
                        </label>
                        <input @change="attachFile" type="file" id="video_introduction" required />
                    </div>
                </div>
            </div>
            <div v-if="isOnList"
                class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">Register your school if not in the list </h5>
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow">
                        <label class="block color-primary font-bold mb-3 pr-4"> School Name </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_name" required>
                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> City </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_city" required>
                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4"> State/Province
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_province" required>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4"> Country </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text" v-model="register.school_country" required>
                    </div>
                </div>
            </div>
        </div>
        <hr class="my-5 bg-gray-400 h-[3px]" />
        <div class=" text-right" sv-if="true">
            <div v-if="loading_spinner" class="lds-ring">
                <div></div>
                <div></div>
                <div></div>
                <div></div>
            </div>
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
<!-- Start of HubSpot Embed Code -->
<script type="text/javascript" id="hs-script-loader" async defer
    src="//js.hs-scripts.com/45758391.js"></script>
<!-- End of HubSpot Embed Code -->
<style>
input::placeholder {
    text-align: center;
}

.parent-info::placeholder {
    text-align: center;
    font-size: 12px;
    font-style: italic;
}

select {
    text-align: center;
    text-align-last: center;
    color: #f5f5f5;
    background-color: #f5f5f5;
}

input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

input[type="number"] {
    -moz-appearance: textfield;
}

.custom-checkbox input[type="checkbox"],
.custom-radio input[type="radio"] {
    opacity: 0;
    position: absolute;
    z-index: -1;
}

.custom-checkbox,
.custom-radio {
    display: flex;
    align-items: center;
    cursor: pointer;
    position: relative;
}

.custom-checkbox-button,
.custom-radio-button {
    width: 13px;
    height: 13px;
    border: 2px solid #000;
    border-radius: 35%;
    margin-right: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.3s;
}

.custom-checkbox-button::after,
.custom-radio-button::after {
    content: '';
    width: 13px;
    height: 13px;
    background-color: #3B82F680;
    border-radius: 35%;
    opacity: 0;
    transition: opacity 0.3s;
}

.custom-checkbox input[type="checkbox"]:checked+.custom-checkbox-button,
.custom-radio input[type="radio"]:checked+.custom-radio-button {
    background-color: #fff;
}

.custom-checkbox input[type="checkbox"]:checked+.custom-checkbox-button::after,
.custom-radio input[type="radio"]:checked+.custom-radio-button::after {
    opacity: 1;
}

.style-chooser .vs__search::placeholder,
.style-chooser .vs__dropdown-toggle,
.style-chooser .vs__dropdown-menu {
    background-color: rgb(245 245 245)
}
</style>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="https://unpkg.com/vue-select@3.0.0"></script>
<link rel="stylesheet" href="https://unpkg.com/vue-select@3.0.0/dist/vue-select.css">
<script src="<?php echo $js_dir ?>dataExport.js"></script>
<script>
const sourcesLeft = ['Google', 'Facebook', 'Instagram', 'Tiktok', 'News']
const sourcesRight = ['School Fair/Orientation', 'Billboard', 'Event', 'Referral', 'Others']
const timeLeft = ['8:00am-10:00am', '10:00am-12:00pm', '12:00pm-2:00pm', '2:00pm-4:00pm']
const timeRight = ['4:00pm-6:00pm', '6:00pm-8:00pm', '8:00pm-10:00pm', '10:00pm-12:00am']
const referred = ['Family', 'Teacher/Guidance', 'Relatives', 'Friend']
const applicantTypeCollege = ['iACADEMY SHS Graduate', 'Graduate from other SHS',
    'iACADEMY College Graduate', 'Graduate from other College'
]
const collegeTypeValue = ['College - Freshman', 'College - Transferee', 'College - Second Degree',
    'College - iACADEMY SHS Graduate'
]
const freshmenValue = ['College - Freshmen iACADEMY', 'College - Freshmen Other']
const secondDegreeValue = ['2nd - Degree iACADEMY', '2nd - Degree Other']
const applicantTypeShs = ['SHS - New', 'SHS - Transferee']
const healthConditions = ['Diabetes', 'Allergies', 'High Blood', 'Anemia']
Vue.component('v-select', VueSelect.VueSelect)
new Vue({
    el: "#adminssions-form",
    data: {
        selected: '',
        countryList: [],
        barangay: [],
        cities: [],
        hide_school_address: false,
        states: [],
        apiUrl: "http://cebuapi.iacademy.edu.ph/api/v1/",
        optionValue: '',
        sources: [],
        times: [],
        sourceList: [...sourcesLeft, ...sourcesRight],
        timeList: [...timeLeft, ...timeRight],
        referredList: [...referred],
        collegeList: [...applicantTypeCollege],
        shsList: [...applicantTypeShs],
        healthConcern: [...healthConditions],
        freshmenValue: [...freshmenValue],
        secondDegreeValue: [...secondDegreeValue],
        filterCollege: ['college', 'college', 'college', 'college'],
        filterShs: ['shs', 'shs', 'shs'],
        countries: [...countries],
        codes: phoneCode,
        code1: '+63',
        code2: '+63',
        register: {
            school_name: '',
            school_city: '',
            school_province: '',
            school_country: ''
        },
        sourcesSpecify: {
            event: '',
            referral: '',
            others: ''
        },
        isOnList: false,
        bestTime: [],
        refferalName: '',
        prevSchoolList: [],
        syid: <?php echo $current_term; ?>,
        isDual: false,
        request: {
            date_of_birth: "",
            program: "",
            health_concern: [],
            campus: "Makati",
            citizenship: 'Philippines',
            student_type: '',
            source: '',
            last_school_attended: '',
            grade_year_level: '',
            degree: '',
            home_address: '',
            current_address: '',
            government_id: "",
            diploma: '',
            transcript: '',
            proof: '',
            video_introduction: '',
            emergency_contact_first_name: '',
            emergency_contact_middle_name: '',
            emergency_contact_last_name: '',
            emergency_contact_relationship: '',
            emergency_contact_contact_number: '',
            emergency_contact_address: ''
        },
        homeAddressObj: {
            address: '',
            city: '',
            barangay: '',
        },
        currentAddressObj: {
            address: '',
            city: '',
            barangay: '',
        },
        notOnTheList: '',
        selectedSchool: '',
        setSelectedSchool: {
            name: "Not on the list"
        },
        term: undefined,
        loading_spinner: false,
        programs: [],
        sy: [],
        filtered_programs: [],
        programs_group: [],
        types: [],
        base_url: "<?php echo base_url(); ?>",
    },
    mounted() {
        axios.get(this.base_url + 'site/view_active_programs_makati/' + this.syid, {
            headers: {
                Authorization: `Bearer ${window.token}`
            },
        }).then((data) => {
            this.programs = data.data.data;
            this.sy = data.data.sy;
            this.term = data.data.term;
        }).catch((e) => {
            console.log("error");
        });
        axios.get(api_url + 'admissions/student-info/types', {
            headers: {
                Authorization: `Bearer ${window.token}`
            },
        }).then((data) => {
            this.types = data.data.data;
            setTimeout(() => {
                $(".admissions_submission_cb").on("click", e => {
                    $(".admissions_submission_cb").not(e
                        .currentTarget).prop("checked", false);
                    if ($(e.currentTarget).is(":checked")) {
                        this.request.type_id = e.currentTarget
                        .value;
                        $(".admissions_submission_cb").removeAttr(
                            "required");
                    } else {
                        $(".admissions_submission_cb").attr(
                            "required", true);
                    }
                });
            }, 500);
            document.querySelector('#course_first_choice').onchange = (e) => {
                this.request.program = e.target.selectedOptions[0].getAttribute(
                    'data-title');
            };
            document.querySelector('#course_second_choice').onchange = (e) => {
                this.request.program2 = e.target.selectedOptions[0]
                    .getAttribute('data-title');
            };
            document.querySelector('#course_third_choice').onchange = (e) => {
                this.request.program3 = e.target.selectedOptions[0]
                    .getAttribute('data-title');
            };
        }).catch((e) => {
            console.log("error");
        });
        this.getAllPrevSchool()
        this.getAllCountry()
    },
    computed: {
        isSecondaDegree() {
            return this.request.student_type == '2nd - Degree iACADEMY' || this.request
                .student_type == '2nd - Degree Other'
        }
    },
    methods: {
        async getBarangay(e) {
            const {
                data
            } = await axios.get(
                `https://psgc.cloud/api/cities/${e.target.selectedOptions[0].id}/barangays`
                )
            this.barangay = data
        },
        async getCities(e) {
            if (this.addressObj.country == 'Philippines') {
                const {
                    data
                } = await axios.get(`https://psgc.cloud/api/cities`)
                this.cities = data
            } else {
                const {
                    data
                } = await axios.post(
                    'https://countriesnow.space/api/v0.1/countries/state/cities', {
                        "country": this.addressObj.country,
                        "state": e.target.value
                    })
                this.cities = data.data
            }
        },
        async getState(e) {
            this.states = []
            this.cities = []
            this.barangay = []
            Swal.fire({
                showCancelButton: false,
                showCloseButton: false,
                allowEscapeKey: false,
                title: 'Please wait',
                text: 'Loading state/province',
                icon: 'info',
            })
            Swal.showLoading();
            if (e.target.value == 'Philippines') {
                const {
                    data
                } = await axios.get('https://psgc.cloud/api/provinces')
                this.states = data
            } else {
                const {
                    data
                } = await axios.post(
                    'https://countriesnow.space/api/v0.1/countries/states', {
                        "country": e.target.value
                    })
                for (const state of data.data.states) {
                    this.states.push(state.name)
                }
            }
            Swal.close();
        },
        async getAllCountry() {
            const {
                data
            } = await axios.get('https://countriesnow.space/api/v0.1/countries')
            for (const countryObj of data.data) {
                this.countryList.push(countryObj.country)
            }
        },
        async getAllPrevSchool() {
            try {
                const {
                    data
                } = await axios.get(`${api_url}admissions/previous-schools`, {
                    headers: {
                        Authorization: `Bearer ${window.token}`
                    }
                })
                this.prevSchoolList = data
                const obj = {
                    name: 'Not on the list'
                }
                this.prevSchoolList.push(obj)
            } catch (error) {
                const obj = {
                    name: 'Not on the list'
                }
                this.prevSchoolList.push(obj)
            }
        },
        async onInputChange(value) {
            this.hide_school_address = false
            if (value == null) {
                this.isOnList = false
                this.request.school_id = ''
                this.request.school_name = ""
                this.request.school_city = ""
                this.request.school_province = ""
                this.request.school_country = ""
                this.notOnTheList = null
                for (const key in this.register) {
                    this.register[key] = ''
                }
                return
            }
            if (value.name == 'Not on the list') {
                this.isOnList = true
                this.request.school_id = ''
                this.request.school_name = ""
                this.request.school_city = ""
                this.request.school_province = ""
                this.request.school_country = ""
                this.hide_school_address = true
                this.notOnTheList = 'yes'
                return
            }
            if (value.name != '') {
                this.isOnList = false
                this.notOnTheList = null
            }
            this.request.school_id = value.id
            this.request.school_name = value.name
            this.request.school_city = value.city
            this.request.school_province = value.province
            this.request.school_country = value.country
        },
        submitForm: function() {
            //console.log(this.request);
        },
        unmaskedValue: function() {
            var val = this.$refs.input.clean
            console.log(val);
        },
        filterProgram: function(type, title) {
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
                    $(".admissions_submission_pg").not(e.currentTarget)
                        .prop("checked", false);
                    if ($(e.currentTarget).is(":checked")) {
                        this.request.program_id = e.currentTarget.value;
                        $(".admissions_submission_pg").removeAttr(
                            "required");
                    } else {
                        $(".admissions_submission_pg").attr("required",
                            true);
                    }
                });
            }, 500);
        },
        filterCourses: function(type) {
            if (type === 'shs') this.filtered_programs = this.programs.shs;
            else if (type === 'college') this.filtered_programs = this.programs.college;
            else if (type === 'drive') this.filtered_programs = this.programs.drive;
            else {
                this.filtered_programs = this.programs.sd;
            }
            this.request.type = type;
        },
        confirmEmail: function() {
            if (this.request.email != this.request.email_confirmation) {
                Swal.fire({
                    title: 'iACADEMY MAKATI CAMPUS',
                    html: 'The email address you provided does not match',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: true
                })
                return true
            }
            return false
        },
        confirmMobileNumber: function() {
            this.request.mobile_number = `${this.code1}${this.request.mobile_number}`
            this.request.mobile_number_confirmation =
                `${this.code2}${this.request.mobile_number_confirmation}`
            if (this.request.mobile_number != this.request.mobile_number_confirmation) {
                Swal.fire({
                    title: 'iACADEMY MAKATI CAMPUS',
                    html: 'The mobile number you provided does not match',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: true
                })
                return true
            }
            return false
        },
        confirmTelNumber: function() {
            this.request.tel_number = `${this.request.tel_number}`
            this.request.tel_number_confirmation =
                `${this.code2}${this.request.tel_number_confirmation}`
            if (this.request.tel_number != this.request.tel_number_confirmation) {
                Swal.fire({
                    title: 'iACADEMY MAKATI CAMPUS',
                    html: 'The Landline number you provided does not match',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: true
                })
                return true
            }
            return false
        },
        setSource: function() {
            if (this.sources.includes('event')) {
                const index = this.sources.indexOf("event");
                this.sources[index] = `Event(${this.sourcesSpecify.event})`
            }
            if (this.sources.includes('others')) {
                const index = this.sources.indexOf("others");
                this.sources[index] = `Others(${this.sourcesSpecify.others})`
            }
            if (this.sources.includes('referral')) {
                const index = this.sources.indexOf("referral");
                this.sources[index] =
                    `referral-${this.sourcesSpecify.referral}(${this.refferalName})`
            }
            this.request.source = this.sources.join();
            // this.request.source = this.sources
            // if (this.sources == 'event' || this.sources == 'others') {
            //     this.request.source =
            //         `${this.sources}-${this.sourcesSpecify[this.sources]}`
            // }
            // if (this.sources == 'referral') {
            //     this.request.source =
            //         `${this.sources}-${this.sourcesSpecify[this.sources]}-${this.refferalName}`
            // }
        },
        setTime: function() {
            this.request.best_time = this.bestTime.join();
        },
        setFirstChoice: function(e) {
            this.request.program = e.target.selectedOptions[0].getAttribute(
                'data-title')
        },
        setSecondChoice: function(e) {
            this.request.program2 = e.target.selectedOptions[0].getAttribute(
                'data-title')
        },
        setThirdChoice: function(e) {
            this.request.program3 = e.target.selectedOptions[0].getAttribute(
                'data-title')
        },
        setYearStart: function() {
            const result = this.sy.find(data => data.intID === this.request.syid);
            this.request.year_start = result.strYearStart
        },
        setSchoolProgram: function() {
            const result = this.filtered_programs.find(programs => programs.id === this
                .request.type_id);
            this.request.program_school = result.school
        },
        onSelectChange() {
            this.isOnList = true
            this.hide_school_address = true
            this.selectedSchool = this.setSelectedSchool
        },
        setAddress() {
            const home = this.homeAddressObj
            const current = this.currentAddressObj
            this.request.home_address = `${home.address}, ${home.barangay}, ${home.city}`
            this.request.current_address =
                `${current.address}, ${current.barangay}, ${current.city}`
        },
        attachFile($event) {
            this.request[$event.target.id] = $event.target.files[0]
        },
        customSubmit: function(type, title, text, data, url, redirect) {
            if (this.confirmEmail()) {
                return
            }
            if (this.confirmMobileNumber()) {
                return
            }
            if (this.sources.length == 0) {
                Swal.fire({
                    title: 'iACADEMY MAKATI CAMPUS',
                    html: 'Missing value on How did you know about us?',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: true
                })
                return
            }
            // For school registration
            if (this.request.school_id == "") {
                Object.assign(this.request, this.register);
            }
            if (this.request.school_name == '') {
                Swal.fire({
                    title: 'iACADEMY MAKATI CAMPUS',
                    html: 'Missing value on last school attended ',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: true
                })
                return
            }
            this.setSource()
            this.setAddress()
            Swal.fire({
                title: 'iACADEMY APPLICATION FORM ',
                html: `
                You're about to submit this form. Double-check your details before confirming! 
            `,
                showCancelButton: true,
                confirmButtonText: "Submit Application",
                imageWidth: 100,
                icon: "question",
                cancelButtonText: "No, cancel!",
                showCloseButton: true,
                showLoaderOnConfirm: true,
                preConfirm: (login) => {
                    this.loading_spinner = true;
                    if (this.request.health_concern.includes("Others")) {
                        const hasOther = this.request.health_concern
                            .indexOf("Others");
                        this.request.health_concern.splice(hasOther, 1, this
                            .request.health_concern_other);
                    }
                    this.request.health_concern = this.request
                        .health_concern.join(", ");
                    const formData = new FormData()
                    Object.keys(this.request).forEach(key => {
                        formData.append(key, this.request[key])
                    });
                    axios.post(`${api_url}next_school/register`, formData, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    }).then(data => {
                        this.is_done = true;
                        if (data.data.success) {
                            this.loading_spinner = false;
                            var ret = data.data.data;
                            Swal.fire({
                                title: "SUCCESS",
                                text: data.data.message,
                                icon: "success"
                            }).then(function() {
                                location.href =
                                    'http://nextschool.ph/'
                            });
                        } else {
                            this.loading_spinner = false;
                            Swal.fire('Failed!', data.data.message,
                                'error')
                        }
                    });
                    // }
                }
            })
        },
    },
});
</script>
<style>
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap");

* {
    font-family: "Roboto", sans-serif;
}
</style>
<!-- Start of HubSpot Embed Code -->
<script type="text/javascript" id="hs-script-loader" async defer
    src="//js.hs-scripts.com/45758391.js"></script>
<!-- End of HubSpot Embed Code -->