<div class="custom-container">
    <a href="https://iacademy.edu.ph/"
        class="flex mt-10 items-center gap-x-2 text-[#666666] cursor-pointer">
        <svg xmlns="http://www.w3.org/2000/svg"
            width="8"
            height="15"
            viewBox="0 0 8 15"
            fill="none">
            <path d="M7 1L1 7.5L7 14"
                stroke="#666666"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        BACK
    </a>
</div>

<div class=" block mx-auto mt-[60px]"
    data-aos="fade-up">
    <h1 class="text-4xl font-[900] text-center color-primary">
        iACADEMY
    </h1>
</div>

<div class="custom-container max-w-[1080px]"
    id="adminssions-form"
    style="margin-top:10px;">
    <div class="color-primary text-center">
        <h4 class="font-medium text-2xl mb-5">
            Application Form for {{ term.term_student_type.toUpperCase() }}
            <strong>(Cebu Campus)</strong><br />
        </h4>
        <p>Hello future Game Changers! Kindly fill out your information sheet. If you have any
            questions, feel free
            to email us at <strong><u>admissions@iacademy.edu.ph</u></strong> </p>

        <p style="margin-top:15px;">
            Note: You are applying for iACADEMY Cebu Campus, if you want to apply to iACADEMY Makati
            click
            <a style="text-decoration: underline;"
                href="http://cebu.iacademy.edu.ph/site/student_application_makati_view">here</a>.
        </p>
    </div>

    <form @submit.prevent="
            customSubmit(
                'submit',
                'Submit Details',
                'form',
                request,
                'admissions/student-info'
            )
        "
        method="post"
        class="">

        <div v-if="true"
            class="flex flex-wrap md:space-x-5 mb-6 mt-10 justify-center ">
            <div id="select-term"
                class=" pr-4 flex-[1_0_188px]">
                <div class="mb-5">
                    <label class="block t color-primary font-bold mb-3 pr-4"
                        for="inline-full-name">
                        Select Term <span class="text-red-500">*</span>
                    </label>
                    <select
                        class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                        type="text"
                        required
                        v-model="request.syid">
                        <option disabled
                            value="">--Select options--</option>
                        <option v-for="s in sy"
                            :value="s.intID">
                            {{ `${s.enumSem} ${s.term_label} SY ${s.strYearStart}-${s.strYearEnd}`}}
                        </option>

                    </select>
                </div>
                <div id="applicant-type"
                    class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                    <label class="block t color-primary font-bold  mb-2  pr-4"
                        for="inline-full-name">
                        Applicant Type <i class="font-normal">(Select one)</i>
                    </label>
                    <template v-if="term.term_student_type == 'college'">
                        <h6 class="color-primary font-bold">Freshman</h6>
                        <label v-if="term.term_student_type == 'college'"
                            v-for="college,index of collegeList.slice(0,2)"
                            class="block indent-5 color-primary mb-1 ml-1.5">
                            <input type="radio"
                                :id="index"
                                :value="freshmenValue[index]"
                                name="college"
                                v-model="request.student_type"
                                @click="filterCourses('college')"
                                required
                                class="mr-1">
                            {{college}}
                        </label>
                        <h6 class="color-primary font-bold">2nd Degree</h6>
                        <label v-if="term.term_student_type == 'college'"
                            v-for="college,index of collegeList.slice(2,4)"
                            class="block indent-5 color-primary mb-1 ml-1.5">
                            <input type="radio"
                                :id="index"
                                :value="secondDegreeValue[index]"
                                name="college"
                                v-model="request.student_type"
                                @click="filterCourses('college')"
                                required
                                class="mr-1">
                            {{college}}
                        </label>
                        <h6 class="color-primary font-bold">Other</h6>
                        <label v-if="term.term_student_type == 'college'"
                            class="block indent-5 color-primary mb-1 ml-1.5">
                            <input type="radio"
                                value="`College - Transferee`"
                                name="college"
                                v-model="request.student_type"
                                @click="filterCourses('college')"
                                required
                                class="mr-1">
                            Transferee
                        </label>
                    </template>
                    <label v-if="term.term_student_type == 'shs'"
                        v-for="shs,index of shsList"
                        class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            id="one"
                            :value="shs"
                            v-model="request.student_type"
                            @click="filterCourses(filterShs[index])"
                            class="mr-1"
                            required>
                        {{shs}}
                    </label>

                </div>
            </div>
            <div id=applying-for
                class=" flex-[4_1_auto] max-w-[710px]">
                <div class="md:w-5/5">
                    <label class="block t color-primary font-bold  mb-3  pr-4"
                        for="inline-full-name">
                        Applying for
                    </label>
                    <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                        <label v-for="t in programs"
                            class="block indent-5 color-primary mb-1 ml-1.5">
                            <input type="radio"
                                :id="'progId-' + t.id"
                                :value="t.id"
                                @click="filterProgram(t.type,t.title)"
                                v-model="cebuFilter"
                                required
                                class="mr-1">
                            {{t.title}}
                        </label>

                    </div>


                </div>
            </div>

        </div>
        <div v-if="true"
            class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">BASIC INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100 rounded-lg mt-5 py-5 px-2.5">
                <div class="flex flex-wrap gap-x-2 gap-y-2 mb-4">
                    <div id="first-name"
                        class="flex-grow">
                        <label class="block color-primary font-bold  mb-3  pr-4">
                            First Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="first_name"
                            required
                            v-model="request.first_name">


                    </div>
                    <div id="middle-name"
                        class="flex-grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Middle Name
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="middle_name"
                            v-model="request.middle_name">


                    </div>
                    <div class="flex-grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Last Name <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="last_name"
                            required
                            v-model="request.last_name">


                    </div>
                    <div id="suffix"
                        class="basis-[100px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Suffix
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="request.suffix">


                    </div>
                </div>
                <div class="flex flex-wrap gap-x-2 gap-y-2 mb-4">
                    <div id="date-birth"
                        class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Date of Birth <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="date"
                            v-model="request.date_of_birth"
                            required>

                    </div>
                    <div id="place-birth"
                        class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Place of Birth <span class="text-red-500">*</span>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="place_of_birth"
                            v-model="request.place_of_birth"
                            required>

                    </div>

                    <div id="gender"
                        class="basis-[120px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Gender <span class="text-red-500">*</span>
                        </label>
                        <select v-model="request.gender"
                            required
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option disabled
                                value="">--options--</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-wrap gap-x-2 gap-y-2 ">
                    <div id="citizenship-base"
                        class="basis-[300px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Citizenship <span class="text-red-500">*</span>
                        </label>
                        <select v-model="request.citizenship"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option disabled
                                value="">--Select options--</option>
                            <option v-for="country in countries"
                                :value="country">{{country}}</option>
                        </select>
                    </div>
                    <div id="citizenship-dual"
                        v-if="isDual"
                        class="basis-[300px] self-end">
                        <select v-model="request.country_of_citizenship2"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            required>
                            <option disabled
                                value="">--Select options--</option>
                            <option v-for="country in countries"
                                :value="country">{{country}}</option>
                        </select>
                    </div>
                    <div id="citizenship-radio"
                        class="self-end">
                        <label class="block color-primary mb-1 ml-1.5">
                            <input type="radio"
                                id="one"
                                class="mr-1"
                                :checked="isDual"
                                @click="isDual = !isDual">
                            I'm a dual citizen
                        </label>
                    </div>
                </div>
            </div>

        </div>
        <div v-if="true"
            class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">CONTACT INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">CONTACT DETAILS</h5>
                <div
                    class="grid gap-x-16 grid-cols-[repeat(auto-fit,_minmax(0,420px))] gap-y-2 mb-4 ">
                    <div id="email"
                        class="">
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Email Address <span class="text-red-500">*</span>
                        </label>
                        <input v-model="request.email"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="email"
                            name="confirm-email"
                            required>


                    </div>
                    <div id="email-confirm">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Confirm Email Address <span class="text-red-500">*</span>
                        </label>
                        <input v-model="request.email_confirmation"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="email"
                            name="confirm-email"
                            required>

                    </div>

                </div>
                <div
                    class="grid grid-cols-[repeat(auto-fit,_minmax(0,420px))] gap-x-16 gap-y-2 mb-4">
                    <div class="">
                        <label class="block color-primary font-bold mb-3 pr-4">
                            Mobile Number <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-x-2.5">
                            <select v-model="code1"
                                class="w-1/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option v-for="code in codes"
                                    :value="code.dialCode"
                                    required>
                                    {{ code.flag}} {{code.dialCode}}
                                </option>
                            </select>
                            <input
                                class="w-2/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="number"
                                name="mobile-number"
                                v-model="request.mobile_number"
                                required>

                        </div>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Confirm Mobile Number <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-x-2.5">
                            <select v-model="code2"
                                class="w-1/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option v-for="code in codes"
                                    :value="code.dialCode"
                                    required>
                                    {{ code.flag}} {{code.dialCode}}
                                </option>
                            </select>
                            <input
                                class="w-2/3 bg-neutral-100 border border-neutral-100 rounded-lg  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="number"
                                name="mobile-number"
                                v-model="request.mobile_number_confirmation"
                                required>


                        </div>
                    </div>

                </div>
            </div>
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">ADDRESS</h5>
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div id=""
                        class="">
                        <label class="block color-primary font-bold mb-3 pr-4">
                            Home Number/Street/Subdivision <span class="text-red-500">*</span>
                        </label>
                        <input v-model="request.address"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="address"
                            type="text"
                            required>

                    </div>
                    <div id="">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Barangay
                        </label>
                        <input v-model="addressObj.barangay"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="barangay"
                            type="text"
                            required>

                    </div>
                    <div id="">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            City <span class="text-red-500">*</span>
                        </label>
                        <input v-model="addressObj.city"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="city"
                            required>

                        <!-- <select v-if="addressObj.country == 'Philippines'"
                            @change="getBarangay"
                            v-model="addressObj.city"
                            class="w-full bg-neutral-100 border border-neutral-100 rounded-lg py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option v-for="city in cities"
                                :value="city.name"
                                :id="city.code"
                                required>
                                {{ city.name}}
                            </option>
                        </select> -->
                        <!-- <select v-else
                            v-model="addressObj.city"
                            class="w-full bg-neutral-100 border border-neutral-100 rounded-lg py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option v-for="city in cities"
                                :value="city"
                                required>
                                {{ city}}
                            </option>
                        </select> -->
                    </div>
                </div>
                <div
                    class="grid grid-cols-[repeat(auto-fill,minmax(250px,1fr))] items-end gap-2.5 mb-4 ">
                    <div id="">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Country<span class="text-red-500">*</span>
                        </label>
                        <select @change="getState"
                            name="country"
                            v-model="addressObj.country"
                            class="w-full bg-neutral-100 border border-neutral-100 rounded-lg py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                            <option v-for="country in countryList"
                                :value="country"
                                required>
                                {{ country}}
                            </option>
                        </select>
                        <!-- <input v-model="request.country"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>
                         -->
                    </div>
                    <div id=""
                        class="">
                        <label class="block color-primary font-bold mb-3 pr-4">
                            State/Province <span class="text-red-500">*</span>
                        </label>
                        <input v-model="addressObj.province"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="state"
                            required>
                        <!-- <select v-if="addressObj.country == 'Philippines'"
                                @change="getCities"
                                v-model="addressObj.province"
                                required
                                class="w-full bg-neutral-100 border border-neutral-100 rounded-lg py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option v-for="state in states"
                                    :id="state.code"
                                    :value="state.name">
                                    {{ state.name}}
                                </option>
                            </select>
                            <select v-else
                                @change="getCities"
                                v-model="addressObj.province"
                                required
                                class="w-full bg-neutral-100 border border-neutral-100 rounded-lg py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500">
                                <option v-for="state in states"
                                    :value="state">
                                    {{ state}}
                                </option>
                            </select> -->
                    </div>
                </div>
            </div>

        </div>
        <div v-if="true"
            class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">PARENT'S INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />

            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">MOTHER <span class="text-red-500">*</span> </h5>
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div>
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Name
                        </label>
                        <input v-model="request.mother_name"
                            class="parent-info bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="full_name"
                            placeholder='(write "n/a" only if not applicable)'
                            required>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Occupation
                        </label>
                        <input v-model="request.mother_occupation"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="job_title"
                            required />

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Email Address
                        </label>
                        <input v-model="request.mother_email"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="email"
                            name="email"
                            required>

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Mobile Number
                        </label>
                        <input v-model="request.mother_contact"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="number"
                            name="number"
                            required>

                    </div>


                </div>
                <div>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            class="mr-1"
                            id="mother"
                            name="primary_contact"
                            v-model="request.primary_contact"
                            value="mother"
                            required>
                        SET AS PRIMARY CONTACT
                    </label>
                </div>

            </div>
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">FATHER <span class="text-red-500">*</span> </h5>
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div>
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Name
                        </label>
                        <input v-model="request.father_name"
                            class="parent-info bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="full_name"
                            placeholder='(write "n/a" only if not applicable)'
                            required>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Occupation
                        </label>
                        <input v-model="request.father_occupation"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="job_title"
                            type="text"
                            required>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Email Address
                        </label>
                        <input v-model="request.father_email"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            name="email"
                            type="email"
                            required>

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Mobile Number
                        </label>
                        <input v-model="request.father_contact"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="number"
                            name="number"
                            required>

                    </div>


                </div>
                <div>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            class="mr-1"
                            id="father"
                            name="primary_contact"
                            v-model="request.primary_contact"
                            value="father"
                            required>
                        SET AS PRIMARY CONTACT
                    </label>
                </div>

            </div>
            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">GUARDIAN <span class="text-red-500">*</span> </h5>
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div>
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Name
                        </label>
                        <input v-model="request.guardian_name"
                            class="parent-info bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="full_name"
                            placeholder='(write "n/a" only if not applicable)'
                            required>
                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Relationship
                        </label>
                        <input v-model="request.guardian_occupation"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            required>

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Email Address
                        </label>
                        <input v-model="request.guardian_email"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="email"
                            name="email"
                            required>

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Mobile Number
                        </label>
                        <input v-model="request.guardian_contact"
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="number"
                            name="number"
                            required>

                    </div>


                </div>
                <div>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            class="mr-1"
                            id="guardian"
                            name="primary_contact"
                            v-model="request.primary_contact"
                            value="guardian"
                            required>
                        SET AS PRIMARY CONTACT
                    </label>
                </div>

            </div>
        </div>
        <div v-if="true"
            class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">EDUCATIONAL BACKGROUND</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />

            <div class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">

                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow">
                        <label class="block color-primary font-bold mb-3 pr-4">
                            Last School Attended
                        </label>
                        <v-select :options="prevSchoolList"
                            label="name"
                            class="style-chooser"
                            @input="onInputChange"></v-select>

                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            City
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="request.school_city">

                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            State/Province
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="request.school_province">

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Country
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="request.school_country">

                    </div>


                </div>
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow lg:grow-0">
                        <label class="block color-primary font-bold mb-3 pr-4">
                            Grade/Year Level
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="number"
                            v-model="request.grade_year_level">


                    </div>
                    <div class="grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Program/Strand/Degree earned
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            name="strand"
                            v-model="request.program_strand_degree">

                    </div>
                    <div class="grow">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            LRN <i class="font-normal">(For Junior High School Applicants)</i>
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            v-model="request.lrn"
                            type="text">

                    </div>
                </div>

            </div>
            <div v-if="isOnList"
                class="border-[1px] border-neutral-100  rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary mb-2.5">Register your school if not in the list </h5>
                <div class="flex flex-wrap gap-2.5 mb-4 ">
                    <div class="grow">
                        <label class="block color-primary font-bold mb-3 pr-4">
                            School Name
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="register.school_name"
                            required>


                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            City
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="register.school_city"
                            required>

                    </div>
                    <div class="basis-[154px]">
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            State/Province
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="register.school_province"
                            required>

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Country
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full  py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="register.school_country"
                            required>

                    </div>


                </div>
            </div>

        </div>
        <div v-if="true"
            class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">ADDITIONAL INFORMATION</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="grid grid-cols-[repeat(auto-fit,minmax(400px,1fr))] gap-6">
                <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                    <h5 class="color-primary mb-2.5">Do you hold good moral standing in your
                        previous
                        school? <span class="text-red-500">*</span> </h5>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            class="mr-1"
                            value="Yes"
                            name="good_moral"
                            required
                            v-model="request.good_moral">
                        Yes
                    </label>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            class="mr-1"
                            value="No"
                            name="good_moral"
                            required
                            v-model="request.good_moral">
                        No
                    </label>
                </div>
                <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                    <h5 class="color-primary mb-2.5">Have you involved of any illegal activities?
                        <span class="text-red-500">*</span>
                    </h5>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            class="mr-1"
                            value="Yes"
                            name="crime"
                            required
                            v-model="request.crime">
                        Yes
                    </label>
                    <label class="block color-primary mb-1 ml-1.5">
                        <input type="radio"
                            class="mr-1"
                            value="No"
                            name="crime"
                            required
                            v-model="request.crime">
                        No
                    </label>
                </div>
            </div>
            <h5 class="color-primary font-bold text-base mt-4 mb-2">Health Conditions</h5>
            <div class="grid grid-cols-[repeat(auto-fit,minmax(400px,1fr))] gap-6">
                <div>
                    <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                        <h5 class="color-primary mb-2.5">Have you been hospitalized before?* <span
                                class="text-red-500">*</span> </h5>
                        <label class="block color-primary mb-1 ml-1.5">
                            <input type="radio"
                                class="mr-1"
                                value="Yes"
                                required
                                name="hospitalized"
                                v-model="request.hospitalized">
                            Yes
                        </label>
                        <label class="block color-primary mb-1 ml-1.5">
                            <input type="radio"
                                class="mr-1"
                                value="No"
                                name="hospitalized"
                                required
                                v-model="request.hospitalized">
                            No
                        </label>
                    </div>
                    <label class="block color-primary font-bold text-base mt-2 ">
                        Other health concerns/conditions <span class="text-red-500">*</span>
                    </label>
                    <label class="block color-primary italic text-sm mb-1">
                        (Type "none" if you do not have any)
                    </label>
                    <input
                        class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                        type="text"
                        v-model="request.other_health_concern"
                        required>

                </div>
                <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                    <h5 class="color-primary mb-2.5">Do you have any of the following? (check all
                        that apply) </h5>
                    <label class="custom-checkbox">
                        <input type="checkbox"
                            v-model="request.health_concerns"
                            value="Diabetes">
                        <span class="custom-checkbox-button"></span>
                        Diabetes
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox"
                            v-model="request.health_concerns"
                            value="Allergies">
                        <span class="custom-checkbox-button"></span>
                        Allergies
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox"
                            v-model="request.health_concerns"
                            value="High Blood">
                        <span class="custom-checkbox-button"></span>
                        High Blood
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox"
                            v-model="request.health_concerns"
                            value="Anemia">
                        <span class="custom-checkbox-button"></span>
                        Anemia
                    </label>
                    <label class="custom-checkbox">
                        <input type="checkbox"
                            v-model="request.health_concerns"
                            value="Others">
                        <span class="custom-checkbox-button"></span>
                        Others (please specify)
                    </label>
                    <label v-if="request.health_concerns.includes('Others')"
                        class="block color-primary mb-1 ml-1.5">
                        <input type="text"
                            class="mr-1 bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            required
                            v-model="request.health_concern_other">
                    </label>
                </div>
            </div>

            <div v-if="isSecondaDegree"
                class="border-[1px] border-neutral-100 rounded-lg mt-5 py-2.5 pl-2.5 pr-2.5">
                <h5 class="color-primary text-base mb-2.5">Professional Background </h5>
                <div
                    class="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] items-end gap-2.5 mb-4 ">
                    <div>
                        <label class="block  color-primary font-bold mb-3 pr-4">
                            Company
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="request.sd_company">


                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Industry
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="request.sd_position">

                    </div>
                    <div>
                        <label class="block t color-primary font-bold  mb-3  pr-4">
                            Designation
                        </label>
                        <input
                            class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                            type="text"
                            v-model="request.sd_degree">

                    </div>
                </div>
            </div>
        </div>
        <div v-if="true"
            class=" mb-6 mt-10">
            <h4 class="color-primary font-bold text-xl">HOW DID YOU FIND OUT ABOUT iACADEMY?</h4>
            <hr class="mb-5 bg-[#10326f] h-1 w-3/5" />
            <div class="grid grid-cols-[repeat(auto-fill,minmax(400px,1fr))] gap-6">

                <div>
                    <div class="border-[1px] border-neutral-100 p-2.5 mb-4 rounded-lg">
                        <h5 class="color-primary mb-2.5">How did you know about us?<span
                                class="text-red-500">*</span></h5>
                        <div class="flex ">
                            <div class="w-1/2">

                                <template v-for="source,index in sourceList">
                                    <label v-if="index <= 4"
                                        class="custom-checkbox mb-1">
                                        <input type="checkbox"
                                            :id="index"
                                            :name="source"
                                            :value="source"
                                            v-model="sources">
                                        <span class="custom-checkbox-button"></span>
                                        {{source}}
                                    </label>
                                </template>
                            </div>
                            <div class="w-1/2">
                                <template v-for="source,index in sourceList">
                                    <label v-if="index >= 5"
                                        class="custom-checkbox mb-1">
                                        <input type="checkbox"
                                            class=""
                                            :id="index"
                                            name="source"
                                            :value="source.toLowerCase()"
                                            v-model="sources">
                                        <span class="custom-checkbox-button"></span>
                                        {{source }} {{index >= 7? "(please specify)" : ""}}
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="border-[1px] border-neutral-100 p-2.5 rounded-lg">
                        <h5 class="color-primary text-sm mb-2.5">Best time to contact you? <span
                                class="text-red-500">*</span>
                            <em>(to
                                receive
                                application updates/announcement/etc)</em>

                        </h5>
                        <div class="flex ">
                            <div class="w-1/2">
                                <template v-for="time,index in timeList">
                                    <label v-if="index <= 3"
                                        class="custom-checkbox mb-1">
                                        <input type="checkbox"
                                            name="time"
                                            :id="index"
                                            :value="time"
                                            v-model="bestTime">
                                        <span class="custom-checkbox-button"></span>
                                        {{time}}
                                    </label>

                                </template>
                            </div>
                            <div class="w-1/2">
                                <template v-for="time,index in timeList">
                                    <label v-if="index >= 4"
                                        class="custom-checkbox mb-1">
                                        <input type="checkbox"
                                            name="time"
                                            :id="index"
                                            :value="time"
                                            v-model="bestTime">
                                        <span class="custom-checkbox-button"></span>
                                        {{time}}
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div v-if="sources.includes('event')"
                        v-bind:key="1"
                        class="border-[1px] border-neutral-100 p-2.5 mb-4 rounded-lg ">
                        <div class="">
                            <h5 class="color-primary mb-2.5">Events (please specify)</h5>
                            <input v-model="sourcesSpecify.event"
                                class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text"
                                required>


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
                                        <label v-if="index < 1"
                                            class="custom-radio mb-1">
                                            <input type="radio"
                                                class="mr-1 "
                                                :id="index"
                                                name="refer"
                                                :value="refer.toLowerCase()"
                                                v-model="sourcesSpecify.referral"
                                                required>
                                            <span class="custom-radio-button"></span>
                                            {{refer}}
                                        </label>
                                    </template>
                                    <label class="custom-radio mb-1">
                                        <input type="radio"
                                            class="mr-1 "
                                            id="index"
                                            name="refer"
                                            value="teacher"
                                            v-model="sourcesSpecify.referral"
                                            required>
                                        <span class="custom-radio-button"></span>
                                        Teacher/Guardian
                                    </label>
                                </div>
                                <template v-for="refer,index in referredList">
                                    <label v-if="index > 1"
                                        class="custom-radio mb-1">
                                        <input type="radio"
                                            :id="index"
                                            name="refer"
                                            :value="refer.toLowerCase()"
                                            v-model="sourcesSpecify.referral"
                                            required>
                                        <span class="custom-radio-button"></span>
                                        {{refer}}
                                    </label>
                                </template>
                                <label class="custom-radio mb-1">
                                    <input type="radio"
                                        name="refer"
                                        value="iacademy"
                                        v-model="sourcesSpecify.referral"
                                        required>
                                    <span class="custom-radio-button"></span>
                                    iACADEMY Student/Alumni/Applicant/Employee/Partner

                                </label>
                                <input
                                    class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="text"
                                    required
                                    v-model="refferalName"
                                    placeholder="Name of your referrer">

                            </div>

                        </div>
                    </div>
                    <div v-if="sources.includes('others')"
                        v-bind:key="2"
                        class="border-[1px] border-neutral-100 p-2.5 mb-4 rounded-lg ">
                        <div class="">
                            <h5 class="color-primary mb-2.5">Others (please specify)</h5>
                            <input
                                class="bg-neutral-100 border border-neutral-100 rounded-lg w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                type="text"
                                v-model="sourcesSpecify.others"
                                required>


                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div class="text-center color-primary mt-[50px]"
            v-if="true">
            iACADEMY shall retain in confidence all confidential information concerning and
            involving every
            student and the school.
            <a href=" https://iacademy.edu.ph/privacypolicy.htm"
                target="_blank"
                class="underline font-bold">
                https://iacademy.edu.ph/privacypolicy.htm</a>

            <div class="mt-4">
                <input type="checkbox"
                    required
                    id="agreement"> <label for="agreement"
                    class="italic">I have read and
                    I
                    agree to the
                    said
                    policy.</label>
            </div>
        </div>

        <hr class="my-5 bg-gray-400 h-[3px]" />


        <div class=" text-right"
            sv-if="true">
            <div v-if="loading_spinner"
                class="lds-ring">
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
<!-- <script type="text/javascript"
    id="hs-script-loader"
    async
    defer
    src="//js.hs-scripts.com/45758391.js"></script> -->
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
<link rel="stylesheet"
    href="https://unpkg.com/vue-select@3.0.0/dist/vue-select.css">



<script src="<?php echo $js_dir ?>dataExport.js"></script>

<script>
const sourcesLeft = ['Google', 'Facebook', 'Instagram', 'Tiktok', 'News']
const sourcesRight = ['School Fair/Orientation', 'Billboard', 'Event', 'Referral', 'Others']

const timeLeft = ['8:00am-10:00am', '10:00am-12:00pm', '12:00pm-2:00pm', '2:00pm-4:00pm']
const timeRight = ['4:00pm-6:00pm', '6:00pm-8:00pm', '8:00pm-10:00pm', '10:00pm-12:00am']

const referred = ['Family', 'Teacher/Guidance',
    'Relatives', 'Friend'
]

const applicantTypeCollege = ['iACADEMY SHS Graduate', 'Graduate from other SHS',
    'iACADEMY College Graduate', 'Graduate from other College'
]

const collegeTypeValue = ['College - Freshman', 'College - Transferee',
    'College - Second Degree', 'College - iACADEMY SHS Graduate'
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
            type_id: "",
            date_of_birth: "",
            program: "",
            health_concerns: [],
            campus: "Cebu",
            citizenship: 'Philippines',
            syid: '',
            student_type: '',
            source: '',
            best_time: '',
            type_id2: "",
            type_id3: "",
            school_id: '',
            school_name: '',
            school_city: '',
            school_province: '',
            school_country: '',
            grade_year_level: '',
            primary_contact: ''
        },
        addressObj: {
            country: '',
            province: '',
            city: '',
            barangay: '',
        },
        term: undefined,
        loading_spinner: false,
        programs: [],
        sy: [],
        filtered_programs: [],
        programs_group: [],
        types: [],
        cebuFilter: '',
        base_url: "<?php echo base_url(); ?>",
    },

    mounted() {

        axios
            .get(this.base_url + 'site/view_active_programs/' + this.syid, {
                headers: {
                    Authorization: `Bearer ${window.token}`
                },
            })

            .then((data) => {

                this.programs = data.data.data;
                this.sy = data.data.sy;
                this.term = data.data.term;

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
                            this.request.type_id = e.currentTarget
                                .value;
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
            })
            .catch((e) => {
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
                } = await axios.get(
                    `https://psgc.cloud/api/cities`
                )
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
                } = await axios.get(
                    `${api_url}admissions/previous-schools`, {
                        headers: {
                            Authorization: `Bearer ${window.token}`
                        }
                    })
           
                 this.prevSchoolList = data
                   
                }

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

            if (value == null) {
                this.isOnList = false
                this.request.school_id = ''
                this.request.school_name = ""
                this.request.school_city = ""
                this.request.school_province = ""
                this.request.school_country = ""
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
                return
            }

            if (value.name != '') {
                this.isOnList = false
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
                    $(".admissions_submission_pg")
                        .not(e.currentTarget)
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
            if (type === 'shs')
                this.filtered_programs = this.programs.shs;
            else if (type === 'college')
                this.filtered_programs = this.programs.college;
            else if (type === 'drive')
                this.filtered_programs = this.programs.drive;
            else {
                this.filtered_programs = this.programs.sd;
            }

            this.request.type = type;
        },
        confirmEmail: function() {
            if (this.request.email != this.request.email_confirmation) {
                Swal.fire({
                    title: 'iACADEMY Cebu CAMPUS',
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
                    title: 'iACADEMY Cebu CAMPUS',
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


        customSubmit: function(type, title, text, data, url, redirect) {

            if (this.confirmEmail()) {
                return
            }
            if (this.confirmMobileNumber()) {
                return
            }

            if (this.sources.length == 0) {
                Swal.fire({
                    title: 'iACADEMY Cebu CAMPUS',
                    html: 'Missing value on How did you know about us?',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: true
                })
                return
            }

            if (this.bestTime.length == 0) {
                Swal.fire({
                    title: 'iACADEMY Cebu CAMPUS',
                    html: 'Missing value on Best time to contact you',
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
                    title: 'iACADEMY Cebu CAMPUS',
                    html: 'Missing value on last school attended ',
                    confirmButtonText: "Ok",
                    imageWidth: 100,
                    icon: "error",
                    showCloseButton: true
                })
                return
            }


            this.setSource()
            this.setTime()


            Swal.fire({
                title: 'iACADEMY Cebu CAMPUS',
                html: `
                You are applying for iACADEMY Cebu Campus. Click <a style='color:#000099' href='https://cebu.iacademy.edu.ph'>here</a> if you are applying for iACADEMY Cebu Campus
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

                    // if (this.request.mobile_number.length < 9) {
                    //     this.loading_spinner = false;
                    //     Swal.fire(
                    //         'Failed!',
                    //         "Please fill in mobile number",
                    //         'warning'
                    //     )
                    // } else {

                    if (this.request.health_concerns.includes(
                            "Others")) {
                        const hasOther = this.request.health_concerns
                            .indexOf("Others");
                        this.request.health_concerns.splice(
                            hasOther,
                            1,
                            this.request.health_concern_other
                        );
                    }


                    this.request.health_concern = this.request
                        .health_concerns.join(
                            ", "
                        );

                    Object.assign(this.request, this.addressObj)

                    console.log(this.request);
                    console.log(data);

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
                                    location.href =
                                        "<?php echo base_url(); ?>site/initial_requirements/" +
                                        ret
                                        .slug;
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