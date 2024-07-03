<div class="content-wrapper pt-5 px-5">
    <section id="adminssions-form" class="section section_port relative container">
        <div v-if="loading_spinner" wire:loading
            class="fixed top-0 left-0 right-0 bottom-0 w-full h-screen z-50 overflow-hidden bg-gray-700 opacity-75 flex flex-col items-center justify-center">
            <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
            <h2 class="text-center text-white text-xl font-semibold">Loading...</h2>
            <p class="w-1/3 text-center text-white">This may take a few seconds, please don't close this page.</p>
        </div>
        <div class="custom-container  relative z-1">
            <!-- <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png"
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
               data-aos="zoom-in" /> -->

            <form @submit.prevent="submitPost"
                class="custom-container relative h-full pt-[200px] mb-[100px] md:mb-[10px]">

                <div class="md:flex  md:mt-0 h-full items-center justify-center" style="margin-top:3rem;">
                    <div class="md:w-12/12 py-3">
                        <h3 class="text-center">
                            <span class="font-bold"> Update Initial Requirements!
                                <br />
                        </h3>

                        <!-- Not PH  -->
                        <div class="row" style="margin-top:5rem;"
                            v-if="request.citizenship != 'Philippines'">
                            <div class="col-md-3">

                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Photocopy of the valid passport pages bearing the bio-page,
                                            the latest
                                            admissions or
                                            arrival in PH with "valid authorized stay" date and the Bureau of Quarantine
                                            stamp.</small></p>
                                    <p v-if="uploaded_paths.passport_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.passport_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_passport" @change="uploadReq('passport',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>

                            <!-- <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Copy of Alien Certificate of Registration (i-CARD) if any</small></p>
                                    <p v-if="uploaded_paths.acr_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.acr_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>

                                </div>

                                <input ref="file_acr" @change="uploadReq('acr',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>
                            <div class="col-md-3">

                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Quarantine Medical Examination by the Bureau of Quarantine</small></p>
                                    <p v-if="uploaded_paths.acr_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.acr_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>

                                </div>

                                <input ref="file_quarantine" @change="uploadReq('quarantine_med_exam',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div> -->
                            <!-- birthcert -->
                            <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Copy of Birth Certificate.</small></p>
                                    <p v-if="uploaded_paths.birth_certificate_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.birth_certificate_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_birthcert" @change="uploadReq('birthcert',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>
                            <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Digital Copy of Current School ID</small></p>
                                    <p v-if="uploaded_paths.digital_school_id_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.digital_school_id_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_id" @change="uploadReq('school_id',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">

                            </div>
                            <div class="col-md-3">

                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>2x2 ID picture (white background with name tag below)</small></p>
                                    <p v-if="uploaded_paths.foreign_2x2_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.foreign_2x2_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_2x2_foreign" @change="uploadReq('2x2_foreign',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">


                            </div>
                            <!-- end -->
                        </div>
                        <!-- Not PH  -->
                        <div class="row" style="margin-top:5rem;"
                            v-else-if="request.citizenship == 'Philippines' && (request.tos == 'transferee' || request.tos == 'second degree')">
                            <div class="col-md-3">

                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Transcript of Records.</small></p>
                                    <p v-if="uploaded_paths.transcript_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.transcript_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="transcript" @change="uploadReq('transcript',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>

                            <!-- <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Copy of Alien Certificate of Registration (i-CARD) if any</small></p>
                                    <p v-if="uploaded_paths.acr_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.acr_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>

                                </div>

                                <input ref="file_acr" @change="uploadReq('acr',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>
                            <div class="col-md-3">

                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Quarantine Medical Examination by the Bureau of Quarantine</small></p>
                                    <p v-if="uploaded_paths.acr_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.acr_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>

                                </div>

                                <input ref="file_quarantine" @change="uploadReq('quarantine_med_exam',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div> -->
                            <!-- birthcert -->
                            <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Copy of Birth Certificate.</small></p>
                                    <p v-if="uploaded_paths.birth_certificate_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.birth_certificate_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_birthcert" @change="uploadReq('birthcert',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>
                            <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Digital Copy of Current School ID</small></p>
                                    <p v-if="uploaded_paths.digital_school_id_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.digital_school_id_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_id" @change="uploadReq('school_id',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">

                            </div>
                            <div class="col-md-3">

                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>2x2 ID picture (white background with name tag below)</small></p>
                                    <p v-if="uploaded_paths.foreign_2x2_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.foreign_2x2_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_2x2_foreign" @change="uploadReq('2x2_foreign',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">


                            </div>
                            <!-- end -->
                        </div>
                        <div class="row" style="margin-top:3rem"
                            v-else>

                            <!-- 2x2 -->
                            <div class="col-md-4">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Digital Copy of 2x2 Photo</small></p>
                                    <p v-if="uploaded_paths.digital_2x2_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.digital_2x2_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>
                                </div>

                                <input ref="file_2x2" @change="uploadReq('2x2',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>
                            <!-- end -->

                            <!-- PSA / NSO -->
                            <div class="col-md-4">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Digital Copy of PSA or NSO Birth Certificate</small></p>
                                    <p v-if="uploaded_paths.digital_psa_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.digital_psa_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_nso" @change="uploadReq('psa',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div>
                            <!-- end -->

                            <!-- school id -->
                            <div class="col-md-4">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Digital Copy of Current School ID</small></p>
                                    <p v-if="uploaded_paths.digital_school_id_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.digital_school_id_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_id" @change="uploadReq('school_id',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">

                            </div>
                            <!-- end -->
                        </div>


                        
                        <div class="row" v-if="request.email && request.citizenship != 'Philippines'">
                            <!-- scholastic records  -->
                            <!-- <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Original Copy of Scholastic Records</small></p>
                                    <p v-if="uploaded_paths.scholastic_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.scholastic_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_schrecords" @change="uploadReq('schrecords',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div> -->
                            <!-- end -->

                            <!-- recommendation -->
                            <!-- <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Recommendation letter from the Principal or Guidance Counselor, or class
                                            Adviser</small></p>
                                    <p v-if="uploaded_paths.recommendation_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.recommendation_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_recommendation" @change="uploadReq('recommendation',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div> -->
                            <!-- end -->

                            <!-- financial support -->
                            <!-- <div class="col-md-3">
                                <div class="file-upload-box text-center">
                                    <svg viewBox="0 -0.5 17 17" version="1.1" xmlns="http://www.w3.org/2000/svg"
                                        width="60px" xmlns:xlink="http://www.w3.org/1999/xlink"
                                        class="si-glyph si-glyph-file-upload" fill="#034fb3">
                                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                                        <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round">
                                        </g>
                                        <g id="SVGRepo_iconCarrier">
                                            <title>1126</title>
                                            <defs> </defs>
                                            <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                                <g transform="translate(1.000000, 1.000000)" fill="#034fb3">
                                                    <path
                                                        d="M14,8.047 L14,12.047 L2,12.047 L2,8.047 L0,8.047 L0,15 L15.969,15 L15.969,8.047 L14,8.047 Z"
                                                        class="si-glyph-fill"> </path>
                                                    <path
                                                        d="M7.997,0 L5,3.963 L7.016,3.984 L7.016,8.969 L8.953,8.969 L8.953,3.984 L10.953,3.984 L7.997,0 Z"
                                                        class="si-glyph-fill"> </path>
                                                </g>
                                            </g>
                                        </g>
                                    </svg>
                                    <p> <small>Proof of adequate financial support to cover expenses for the student's
                                            accommodation and subsistence, as well as school dues and other incidental
                                            expenses.</small></p>
                                    <p v-if="uploaded_paths.financial_filepath"><a class="font-weight-bold"
                                            :href="uploaded_paths.financial_filepath" target="_blank"><u>View
                                                Uploaded
                                                File</u></a></p>


                                </div>

                                <input ref="file_financial_support" @change="uploadReq('financial_support',$event)"
                                    class="bg-gray-200 form-control appearance-none border-2 border-gray-200 rounded w-full py-2 px-4 text-gray-700 leading-tight focus:outline-none focus:bg-white focus:border-blue-500"
                                    type="file" style="margin-bottom:2rem;">
                            </div> -->
                            
                            </span>
                        </div>
                    </div>
                </div>


                <!-- <div class="text-center" v-if="request.email">
                    <div v-if="loading_spinner" class="lds-ring"><div></div><div></div><div></div><div></div></div> 
                    <div v-else>
                        <button  type="submit" class="btn btn-primary">Submit</button>
                    </div>
               </div> -->


            </form>
        </div>
    </section>
</div>
<style>
.loader {
    border-top-color: #3498db;
    -webkit-animation: spinner 1.5s linear infinite;
    animation: spinner 1.5s linear infinite;
}

@-webkit-keyframes spinner {
    0% {
        -webkit-transform: rotate(0deg);
    }

    100% {
        -webkit-transform: rotate(360deg);
    }
}

@keyframes spinner {
    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }

}

.file-upload-box {
    padding-top: 10px;
    background: #034fb33d;
    border: 2px dashed #034fb3;
    padding: 20px;
    color: #034fb3;
    font-weight: medium;
    margin-bottom: 20px;
    min-height: 280px;
}
</style>
<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/axios.min.js"></script>
<script>
new Vue({
    el: "#adminssions-form",
    data: {
        request: {
            type_id: "",
            date_of_birth: "",
            uploaded_requirements: [],

        },
        uploaded_paths: {
            digital_school_id_filepath: "",
            digital_2x2_filepath: "",
            digital_psa_filepath: "",
            acr_filepath: "",
            passport_filepath: "",
            qme_filepath: "",
            birth_certificate_filepath: "",
            scholastic_filepath: "",
            recommendation_filepath: "",
            foreign_2x2_filepath: "",
            transcript_filepath:""
        },
        programs: [],
        programs_group: [],
        loading_spinner: false,
        types: [],
        uploads: {
            requirements: [
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
                {
                    "file_id": ""
                },
            ]
        },
        slug: '<?php echo $this->uri->segment('3'); ?>'
    },
    mounted() {


        axios.get(api_url + 'admissions/student-info/' + this.slug)
            .then((data) => {
                this.request = data.data.data;
               
                for(i in this.request.uploaded_requirements){
                    if (this.request.uploaded_requirements[i].type == '2x2') {
                        this.uploads.requirements[0].file_id = this.request.uploaded_requirements[i].id;
                        this.uploaded_paths.digital_2x2_filepath = this.request.uploaded_requirements[i].path;
                    }

                    if (this.request.uploaded_requirements[i].type == 'psa') {
                        this.uploads.requirements[1].file_id = this.request.uploaded_requirements[i].id;
                        this.uploaded_paths.digital_psa_filepath = this.request.uploaded_requirements[i].path;
                    }

                    if (this.request.uploaded_requirements[i].type == 'school_id') {
                        this.uploads.requirements[2].file_id = this.request.uploaded_requirements[i].id;
                        this.uploaded_paths.digital_school_id_filepath = this.request.uploaded_requirements[i].path;
                    }

                    if (this.request.uploaded_requirements[i].type == 'passport') {
                        this.uploads.requirements[3].file_id = this.request.uploaded_requirements[i].id;
                        this.uploaded_paths.passport_filepath = this.request.uploaded_requirements[i].path;
                    }


                    // if (this.request.uploaded_requirements[i].type == 'arc') {
                    //     this.uploads.requirements[1].file_id = this.request.uploaded_requirements[i].id;
                    //     this.uploaded_paths.acr_filepath = this.request.uploaded_requirements[i].path;
                    // }

                    // if (this.request.uploaded_requirements[i].type == 'qme') {
                    //     this.uploads.requirements[2].file_id = this.request.uploaded_requirements[i].id;
                    //     this.uploaded_paths.qme_filepath = this.request.uploaded_requirements[i].path;
                    // }

                    if (this.request.uploaded_requirements[i].type == 'birthcert') {
                        this.uploads.requirements[4].file_id = this.request.uploaded_requirements[i].id;
                        this.uploaded_paths.birth_certificate_filepath = this.request.uploaded_requirements[i].path;
                    }

                    // if (this.request.uploaded_requirements[i].type == 'schrecords') {
                    //     this.uploads.requirements[4].file_id = this.request.uploaded_requirements[i].id;
                    //     this.uploaded_paths.scholastic_filepath = this.request.uploaded_requirements[i].path;
                    // }

                    // if (this.request.uploaded_requirements[i].type == 'recommendation') {
                    //     this.uploads.requirements[5].file_id = this.request.uploaded_requirements[i].id;
                    //     this.uploaded_paths.recommendation_filepath =this.request.uploaded_requirements[i].path;
                    // }

                    // if (this.request.uploaded_requirements[i].type == 'financial_support') {
                    //     this.uploads.requirements[6].file_id = this.request.uploaded_requirements[i].id;
                    //     this.uploaded_paths.financial_filepath = this.request.uploaded_requirements[i].path;
                    // }

                    if (this.request.uploaded_requirements[i].type == '2x2_foreign') {
                        this.uploads.requirements[5].file_id = this.request.uploaded_requirements[i].id;
                        this.uploaded_paths.foreign_2x2_filepath = this.request.uploaded_requirements[i].path;
                    }

                    if (this.request.uploaded_requirements[i].type == 'transcript') {
                        this.uploads.requirements[6].file_id = this.request.uploaded_requirements[i].id;
                        this.uploaded_paths.transcript_filepath = this.request.uploaded_requirements[i].path;
                    }
                }
                
            })
            .catch((error) => {
                console.log(error);
            })

    },

    methods: {
        
        uploadReq: function(type, event) {

            this.loading_spinner = true;
            let formDataUp = "";
            formDataUp = new FormData();

            let file = '';

            if (type == 'school_id') {
                file = this.$refs.file_id.files[0];
            } else if (type == 'psa') {
                file = this.$refs.file_nso.files[0];
            } else if (type == '2x2') {
                file = this.$refs.file_2x2.files[0];
            } else if (type == 'passport') {
                file = this.$refs.file_passport.files[0];
            } else if (type == 'acr') {
                file = this.$refs.file_acr.files[0];
            } else if (type == 'quarantine_med_exam') {
                file = this.$refs.file_quarantine.files[0];
            } else if (type == 'birthcert') {
                file = this.$refs.file_birthcert.files[0];
            } else if (type == 'schrecords') {
                file = this.$refs.file_schrecords.files[0];
            } else if (type == 'recommendation') {
                file = this.$refs.file_recommendation.files[0];
            } else if (type == 'financial_support') {
                file = this.$refs.file_financial_support.files[0];
            } else if (type == '2x2_foreign') {
                file = this.$refs.file_2x2_foreign.files[0];
            } else if (type == 'transcript') {
                file = this.$refs.transcript.files[0];
            }else {
                file = '';
            }

            formDataUp.append("file", file);
            formDataUp.append("type", type);
            formDataUp.append("slug", this.slug);

             Swal.fire({
                title: "Update Requirements",
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
                            .post(api_url + 'admissions/student-info/update-requirements',
                                formDataUp, {
                                    headers: {
                                        Authorization: `Bearer ${window.token}`
                                    }
                                })
                            .then(data => {
                                if (data.data.success) {
                                    // this.successMessageApi(data.data.message);
                                    // location.reload();
                                    Swal.fire(
                                        'Success!',
                                        data.data.message,
                                        'success'
                                    )

                                    if (type == '2x2') {
                                        this.uploads.requirements[0].file_id = data.data.data.id;
                                        this.uploaded_paths.digital_2x2_filepath = data.data.data.path;
                                    }

                                    if (type == 'psa') {
                                        this.uploads.requirements[1].file_id = data.data.data.id;
                                        this.uploaded_paths.digital_psa_filepath = data.data.data.path;
                                    }

                                    if (type == 'school_id') {
                                        this.uploads.requirements[2].file_id = data.data.data.id;
                                        this.uploaded_paths.digital_school_id_filepath = data.data.data.path;
                                    }

                                    if (type == 'passport') {
                                        this.uploads.requirements[3].file_id = data.data.data.id;
                                        this.uploaded_paths.passport_filepath = data.data.data.path;
                                    }

                               

                                    if (type == 'birthcert') {
                                        this.uploads.requirements[4].file_id = data.data.data.id;
                                        this.uploaded_paths.birth_certificate_filepath = data.data.data.path;
                                    }
                                 
                                    

                                    if (type == '2x2_foreign') {
                                        this.uploads.requirements[5].file_id = data.data.data.id;
                                        this.uploaded_paths.foreign_2x2_filepath = data.data.data.path;
                                    }

                                    if (type == 'transcript') {
                                        this.uploads.requirements[6].file_id = data.data.data.id;
                                        this.uploaded_paths.transcript = data.data.data.path;
                                    }


                                    this.uploads.slug = this.slug;
                                    this.loading_spinner = false;

                                } else {
                                    Swal.fire(
                                        'Failed!',
                                        data.data.message,
                                        'error'
                                    )
                                    event.target.value = null;
                                    this.loading_spinner = false;
                                }
                            });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {}
            })

            


        }
    }
});
</script>