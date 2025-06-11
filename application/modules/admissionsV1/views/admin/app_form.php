<body>
    <div class="sheet-outer A4">
        <div id="application-form" class="sheet padding-5mm" style="margin-top:10px;">
            <!-- 1st row -->
            <div class="grid grid-cols-[1fr_192px] gap-x-2">
                <div class="self-end">
                    <div class="flex justify-between relative">
                        <div class="absolute w-full h-[30px] bottom-0 border border-[#E5E7EB]">
                        </div>
                        <div class="flex flex-col">
                            <label for="">Last Name</label>
                            <input data-text class="!pl-[15px]" type="text"
                                v-model="responseData.last_name" disabled>
                        </div>
                        <div class="flex flex-col">
                            <label for="">First Name</label>
                            <input data-text type="text" v-model="responseData.first_name" disabled>
                        </div>
                        <div class="flex flex-col">
                            <label for="">Middle Name</label>
                            <input data-text type="text" v-model="responseData.middle_name"
                                disabled>
                        </div>
                    </div>
                    <div class="flex gap-x-2">
                        <div class="flex flex-col">
                            <label for="">DATE OF BIRTH</label>
                            <input type="text" class="w-[140px]"
                                v-model="responseData.date_of_birth_formatted">
                        </div>
                        <div class="flex flex-col">
                            <label for="">AGE</label>
                            <input type="text" class="w-[50px]" value="">
                        </div>
                        <div class="flex items-center mt-2">
                            <legend class="mr-2 text-xs">sex</legend>
                            <div class="flex flex-col">
                                <div>
                                    <label>
                                        <input type="radio" value="Male"
                                            v-model="responseData.gender" class="square-radio"
                                            disabled /> male</label>
                                </div>
                                <div>
                                    <label>
                                        <input type="radio" value="Female"
                                            v-model="responseData.gender" disabled
                                            class="square-radio" /> female</label>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center mt-2">
                            <legend class="w-[8ch] text-xs">civil status</legend>
                            <div class="flex flex-col">
                                <div>
                                    <input type="checkbox" id="" name="" value="" disabled />
                                    <label for="">single</label>
                                </div>
                                <div>
                                    <input type="checkbox" id="" name="" value="" disabled />
                                    <label for="">married</label>
                                </div>
                            </div>
                            <div class="flex flex-col">
                                <div>
                                    <input type="checkbox" id="" name="" value="" disabled />
                                    <label for="">seperated</label>
                                </div>
                                <div>
                                    <input type="checkbox" id="" name="" value="" disabled />
                                    <label for="">annulled</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col ">
                        <label for="">home address</label>
                        <input type="text" v-model="responseData.address_complete">
                    </div>
                </div>
                <div class="relative">
                    <img class="relative bottom-[10]" width="190" height="192">
                    <span
                        class="text-sm absolute top-2/4 left-2/4 -translate-x-1/2 -translate-y-1/2">2x2
                        Photo</span>
                </div>
            </div>
            <!-- landline number -->
            <div class="container mt-2">
                <div class="flex gap-x-[10px]">
                    <div class="flex flex-col ">
                        <label for="">landline number</label>
                        <input type="text" v-model="responseData.tel_number" disabled>
                    </div>
                    <div class="flex flex-col ">
                        <label for="">mobile number</label>
                        <input type="text" v-model="responseData.mobile_number" disabled>
                    </div>
                    <div class="flex flex-col flex-grow-[2] ">
                        <label for="">email address</label>
                        <input type="text" v-model="responseData.email" disabled>
                    </div>
                </div>
                <div class="flex gap-x-[10px] mt-2">
                    <legend>educational attainment</legend>
                    <div>
                        <input type="checkbox" id="" name="" value="" disabled />
                        <label for="">undergraduate studies</label>
                    </div>
                    <div>
                        <input type="checkbox" id="" name="" value="" disabled />
                        <label for="">post-graduate studies</label>
                    </div>
                </div>
            </div>
            <!-- undergraduate studies -->
            <div class="container ">
                <legend>undergradute studies</legend>
                <div class="flex gap-x-[10px]">
                    <div class="flex flex-col flex-grow-[2]">
                        <label for="">school</label>
                        <input type="text" value="" disabled>
                    </div>
                    <div class="flex flex-col ">
                        <label for="">inclusive years</label>
                        <input type="text" value="" disabled>
                    </div>
                </div>
                <div class="flex flex-col mt-1">
                    <label for="">program</label>
                    <input type="text" value="" disabled>
                </div>
            </div>
            <!-- post-graduate studies -->
            <div class="container ">
                <legend>post-graduate studies</legend>
                <div class="flex gap-x-[10px]">
                    <div class="flex flex-col flex-grow-[2]">
                        <label for="">school</label>
                        <input type="text" value="" disabled>
                    </div>
                    <div class="flex flex-col ">
                        <label for="">inclusive years</label>
                        <input type="text" value="" disabled>
                    </div>
                </div>
                <div class="flex flex-col mt-1">
                    <label for="">program</label>
                    <input type="text" value="" disabled>
                </div>
                <div class="flex mt-1 gap-x-[10px]">
                    <div class="flex flex-col grow">
                        <label for="">occupation</label>
                        <input type="text" value="" disabled>
                    </div>
                    <div class="flex flex-col grow">
                        <label for="">employer</label>
                        <input type="text" value="" disabled>
                    </div>
                    <div class="flex flex-col grow">
                        <label for="">industry</label>
                        <input type="text" value="" disabled>
                    </div>
                </div>
                <div class="flex mt-1 gap-x-[10px]">
                    <div class="flex flex-col flex-grow-[2.5]">
                        <label for="">preferred course</label>
                        <input type="text" value="" disabled>
                    </div>
                    <div class="flex flex-col grow">
                        <label for="">emergency contact:name</label>
                        <input type="text" value="" disabled>
                    </div>
                    <div class="flex flex-col grow">
                        <label for="">contact number</label>
                        <input type="text" value="" disabled>
                    </div>
                </div>
                <div class="flex gap-x-[10px] mt-2 items-center">
                    <legend class="w-[22ch]">source of information about the school</legend>
                    <div>
                        <input type="checkbox" id="" name="" value="" disabled />
                        <label for="">facebook</label>
                    </div>
                    <div>
                        <input type="checkbox" id="" name="" value="" disabled />
                        <label for=""><small>i</small>academy website </label>
                    </div>
                    <div>
                        <input type="checkbox" id="" name="" value="" disabled />
                        <label for="">referral</label>
                    </div>
                    <div>
                        <input type="checkbox" id="" name="" value="" disabled />
                        <label for="">others</label>
                        <hr class="w-[120px] relative left-[75] -top-[5px]">
                    </div>
                </div>
            </div>
            <!-- related trainings-->
            <div class="container">
                <legend>other related trainings attended</legend>
                <div class="grid grid-cols-[1fr_1fr] items-end">
                    <div class="flex mt-1 gap-x-[5px]">
                        <div class="flex flex-col grow ">
                            <label for="" class="text-center text-[10px]">traning provider</label>
                            <input class="pb-0 pl-[10px]" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                        </div>
                        <div class="flex flex-col grow">
                            <label for="" class="text-center text-[10px]">took</label>
                            <input class="pb-0 pl-[10px]" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                        </div>
                        <div class="flex flex-col grow">
                            <label for="" class="text-center text-[10px]">date</label>
                            <input class="pb-0 pl-[10px]" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                            <input class="pb-0 pl-[10px] mt-1" type="text" value="" disabled>
                        </div>
                    </div>
                    <div>
                        <div class="pl-[40px]">
                            <legend>skill level in the preferred course</legend>
                            <div class="ml-2">
                                <input type="checkbox" id="" name="" value="" disabled />
                                <label for="">beginner</label>
                            </div>
                            <div class="ml-2">
                                <input type="checkbox" id="" name="" value="" disabled />
                                <label for="">intermediate</label>
                            </div>
                            <div class="ml-2">
                                <input type="checkbox" id="" name="" value="" disabled />
                                <label for="">advance</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- reason for -->
            <div class="container">
                <!-- grid -->
                <div class="grid grid-cols-[40%_60%] gap-x-2">
                    <div class="pt-2">
                        <div class="flex items-start gap-x-1">
                            <input type="checkbox" id="" name="" value="" disabled />
                            <p class="text-[9px] italic"> I hereby certify that all information I
                                have indicated herein is true and correct. Any information, if
                                found, may serve as grounds for the nullification of my entire
                                enrollment at iACADEMY. I also affirm that I have read and
                                understood all conditions stated above.</p>
                        </div>
                        <div class="flex items-center gap-x-1 mt-2">
                            <input type="checkbox" id="" name="" value="" disabled />
                            <p class="text-[9px] italic">I hereby authorize iACADEMY to use, collect
                                and process the information for legitimate purposes and allow the
                                authorized personnel to process the same in accordance with the Data
                                Privacy Act of 2012, its IRR, and the Data Privacy Manual of the
                                School. I hold the School, its officers and representatives, free
                                and harmless from any violation of my right to privacy arising from
                                or as a result of the violation by any other party.</p>
                        </div>
                        <div class="mt-5">
                            <hr>
                            <h6 class="text-center">signature over printed name</h6>
                        </div>
                    </div>
                    <div>
                        <div class="flex flex-col ">
                            <label for="">reason for applying for this course</label>
                            <input type="text" value="" disabled>
                        </div>
                        <div>
                            <h6 class="text-center"> for administration use only </h6>
                            <div class="grid grid-cols-[repeat(3,1fr)]">
                                <div
                                    class="leading-none pl-[10px] pt-[10px] pb-[40px] border border-gray-200">
                                    <legend class="text-[10px]">mode of payment:</legend>
                                    <div class="ml-2">
                                        <input type="checkbox" id="" name="" value="" disabled />
                                        <label for="" class="text-[10px]">Cash</label>
                                    </div>
                                    <div class="ml-2">
                                        <input type="checkbox" id="" name="" value="" disabled />
                                        <label for="" class="text-[10px]">Check</label>
                                    </div>
                                    <div class="ml-2">
                                        <input type="checkbox" id="" name="" value="" disabled />
                                        <label for="" class="text-[10px]">Bank</label>
                                    </div>
                                    <div class="ml-2">
                                        <input type="checkbox" id="" name="" value="" disabled />
                                        <label for="" class="text-[10px]">Credit/Debit Card</label>
                                    </div>
                                </div>
                                <div
                                    class="leading-loose pl-[10px] pt-[10px] pb-[40px] border border-gray-200">
                                    <span>approved by:</span>
                                    <br>
                                    <br>
                                    <span>signiture:</span>
                                </div>
                                <div
                                    class="leading-[2.5] pl-[10px] pt-[10px] pb-[40px] border border-gray-200">
                                    <span>or number:</span>
                                    <br>
                                    <span>or date:</span>
                                    <br>
                                    <span>amount paid:</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
<style>
input {
    border: 2px solid #E5E7EB;
    border-radius: 0.20rem;
    padding: 5px 15px;
    font-size: 12px
}

input[data-text] {
    padding-left: 0;
    border: 0;
    border-radius: 0;
}

label,
legend,
h6 {
    text-transform: uppercase;
    font-size: 14px;
    font-weight: bold;
    color: #0c326e;
}

span {
    font-size: 10px;
    text-transform: uppercase;
    color: #0c326e;
}

img {
    border: 2px solid #0c326e
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
    padding-top: 6mm;
    padding-left: 4mm;
    padding-right: 4mm;
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

input[type="radio"].square-radio {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 14px;
    height: 14px;
    border: 1px solid #555;
    border-radius: 4px;
    background-color: #fff;
    cursor: pointer;
    display: inline-block;
    vertical-align: middle;
    position: relative;
    padding: 0;
}

input[type="radio"].square-radio:checked::before {
    content: "";
    position: absolute;
    top: 1px;
    left: 1px;
    width: 10px;
    height: 10px;
    background-color: #555;
    border-radius: 2px;
}

/* .square-radio {
    appearance: none;
    
    -webkit-appearance: none;
    -moz-appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #333;
    
    cursor: pointer;
    position: relative;
}

.square-radio:checked::before {
    content: "";
    position: absolute;
    top: 4px;
    left: 4px;
    width: 10px;
    height: 10px;
    background-color: #333;
} */
</style>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js">
</script>
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
<script>
new Vue({
    el: "#application-form",
    data() {
        return {
            responseData: '',
            slug: "<?php echo $this->uri->segment('3'); ?>",
        }
    },
    mounted() {
        this.getStudentInfo();
    },
    methods: {
        async getStudentInfo() {
            const {
                data: response
            } = await axios.get(api_url + 'admissions/student-info/' + this.slug)
            this.responseData = response.data
            console.log(response.data);
        }
    },
});
</script>
<style>
@import url("https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap");

* {
    font-family: "Roboto", sans-serif;
}
</style>