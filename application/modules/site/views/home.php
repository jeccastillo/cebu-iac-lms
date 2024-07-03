<!-- Add all page content inside this div if you want the side nav to push page content to the right (not used if you only want the sidenav to sit on top of the page -->
<div id="main">
    <div class="" id="fullpages">
        <section id="hero" class="section section_port relative">
            <div class="w-full md:h-[100vh] relative z-1">
                <!-- parallax object? -->
                <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png"
                    class="absolute top-0 md:right-[360px] hidden md:block" alt="" data-scroll-speed="4"
                    data-aos="zoom-in" />
                <img src="<?php echo $img_dir; ?>home-poly/yellow-poly.png"
                    class="absolute top-[20%] md:left-[27%] hidden md:block" alt="" data-scroll-speed="4"
                    data-aos="zoom-in" />
                <img src="<?php echo $img_dir; ?>home-poly/red-poly.png"
                    class="absolute top-[50%] md:left-[15%] hidden md:block" alt="" data-scroll-speed="4"
                    data-aos="zoom-in" />

                <img src="<?php echo $img_dir; ?>home-poly/peach-poly.png"
                    class="absolute top-[65%] md:left-[33%] hidden md:block" alt="" data-scroll-speed="4"
                    data-aos="zoom-in" />

                <img src="<?php echo $img_dir; ?>home-poly/lyellow-poly.png"
                    class="absolute top-[60%] md:right-[21%] hidden md:block" alt="" data-scroll-speed="4"
                    data-aos="zoom-in" />

                <img src="<?php echo $img_dir; ?>home-poly/lblue-poly.png"
                    class="absolute top-[20%] md:right-[22%] hidden md:block" alt="" data-scroll-speed="4"
                    data-aos="zoom-in" />

                <!-- parallax object end -->
                <div class="custom-container relative h-full mb-[100px] md:mb-[10px]">
                    <div class="md:flex mt-[100px] md:mt-0 h-full items-center">
                        <div class="md:w-3/12"></div>
                        <div class="md:w-6/12 py-3">
                            <img src="<?php echo $img_dir; ?>cebu/home/tfn.png"
                                class="max-w-full h-auto block mx-auto w-[600px] md:mt-[-80px] relative z-0" alt=""
                                data-aos="zoom-in" />
                            <div class="max-w-[500px] block mx-auto mt-[-40px]" data-aos="fade-up">
                                <p class="text-center mt-2">
                                    <span class="font-bold color-primary">
                                        Game changing
                                    </span>
                                    course you can take at
                                </p>
                                <h1 class="text-5xl font-[900] text-center color-primary">
                                    iACADEMY Cebu
                                </h1>
                                <a href="#se-section" class="w-full">
                                    <img src="<?php echo $img_dir; ?>cebu/home/ btn-arrow copy.png"
                                        class="img-btn block mx-auto mt-5" />
                                </a>
                            </div>
                        </div>
                        <div class="md:w-3/12 w-full mt-10 md:mt-0 flex justify-between items-center"></div>
                    </div>
                </div>
            </div>
        </section>

        <!--se start -->
        <section class="bg-gray-50 anchor-section" id="se-section">
            <div class="custom-container lg:flex items-center relative z-10">
                <div class="lg:w-2/4 w-full">
                    <div class="py-[100px] md:text-center lg:text-left">
                        <!-- <h1 class="text-2xl">What’s going on in</h1> -->
                        <h1 class="text-5xl color-primary font-black">
                            Sofware <br />
                            Engineering
                        </h1>
                        <a href="<?php echo base_url(); ?>site/articles?type=se" class=" btn img-btn mt-4">
                            <span>View Articles</span>
                        </a>
                    </div>
                </div>
                <div class="lg:w-2/4 relative w-full">
                    <div class="owl-carousel-inner owl-carousel relative z-2 owl-se">
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/se/1.png" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/se/2.png" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/se/3.png" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/se/4.png" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/se/5.png" class="mx-auto block" alt=""
                                title="" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- se end -->

        <!--gd start -->
        <section id="gd-section">
            <div class="custom-container lg:flex items-center relative z-10">
                <div class="lg:w-2/4 w-full">
                    <div class="py-[100px] md:text-center lg:text-left">
                        <!-- <h1 class="text-2xl">What’s going on in</h1> -->
                        <h1 class="text-5xl color-primary font-black">
                            Game <br />
                            Development
                        </h1>
                        <a href="<?php echo base_url(); ?>site/articles?type=gd" class=" btn img-btn mt-4">
                            <span>View Articles</span>
                        </a>
                    </div>
                </div>
                <div class="lg:w-2/4 relative w-full">
                    <div class="owl-carousel-inner owl-carousel relative z-2 owl-gd">
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/gd/1.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/gd/2.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/gd/3.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/gd/4.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/gd/5.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- gd end -->

        <!--ani start -->
        <section class="bg-gray-50" id="ani-section">
            <div class="custom-container lg:flex items-center relative z-10">
                <div class="lg:w-2/4 w-full">
                    <div class="py-[100px] md:text-center lg:text-left">
                        <!-- <h1 class="text-2xl">What’s going on in</h1> -->
                        <h1 class="text-5xl color-primary font-black">Animation</h1>
                        <a href="<?php echo base_url(); ?>site/articles?type=animation" class=" btn img-btn mt-4">
                            <span>View Articles</span>
                        </a>
                    </div>
                </div>
                <div class="lg:w-2/4 relative w-full">
                    <div class="owl-carousel-inner owl-carousel relative z-2 owl-ani">
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/ani/1.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/ani/2.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/ani/3.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/ani/4.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/ani/5.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- ani end -->

        <!--mma start -->
        <section id="mma-section">
            <div class="custom-container lg:flex items-center relative z-10">
                <div class="lg:w-2/4 w-full">
                    <div class="py-[100px] md:text-center lg:text-left">
                        <!-- <h1 class="text-2xl">What’s going on in</h1> -->
                        <h1 class="text-5xl color-primary font-black">
                            Multimedia <br />
                            Arts & Design
                        </h1>
                        <a href="<?php echo base_url(); ?>site/articles?type=mma" class=" btn img-btn mt-4">
                            <span>View Articles</span>
                        </a>
                    </div>
                </div>
                <div class="lg:w-2/4 relative w-full">
                    <div class="owl-carousel-inner owl-carousel relative z-2 owl-mma">
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/mma/1.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/mma/2.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/mma/3.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/mma/4.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/mma/5.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/mma/6.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- mma end -->

        <!--rem start -->
        <section class="bg-gray-50" id="rem-section">
            <div class="custom-container lg:flex items-center relative z-10">
                <div class="lg:w-2/4 w-full">
                    <div class="py-[100px] md:text-center lg:text-left">
                        <!-- <h1 class="text-2xl">What’s going on in</h1> -->
                        <h1 class="text-5xl color-primary font-black">
                            Real Estate <br />
                            Management
                        </h1>
                        <a href="<?php echo base_url(); ?>site/articles?type=rem" class=" btn img-btn mt-4">
                            <span>View Articles</span>
                        </a>
                    </div>
                </div>
                <div class="lg:w-2/4 relative w-full">
                    <div class="owl-carousel-inner owl-carousel relative z-2 owl-rem">
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/1.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/2.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/3.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/4.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/5.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/6.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/7.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                        <div class="inner-carousel-item md:flex items-center">
                            <img src="<?php echo $img_dir; ?>home/course/rem/8.jpg" class="mx-auto block" alt=""
                                title="" />
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- rem end -->


        <!-- latest news -->
        <div id="article-section" class="custom-container my-12">
            <h2 class="px-4 color-primary text-5xl font-black mb-4 mt-10"> Latest News</h2>

            <div class="md:flex flex-wrap">
                <div class="lg:w-1/3 p-3 md:w-1/2 w-full" v-for="article in articles">
                    <div class="h-full bg-white rounded-lg border border-gray-200 shadow-md ">
                        <div
                            class="h-[220px] w-full rounded-t-lg   block mx-auto flex items-center bg-no-repeat bg-center bg-cover bg-[url('https://i.ibb.co/b7kG8JF/bg-art.jpg')]">
                            <img class=" max-w-full h-auto h-[170px] mx-auto block" :src="article.image_url" alt="" />
                        </div>
                        <hr>
                        <div class="p-5">
                            <a href="#">
                                <h5 class="mb-2 text-[20px] font-bold tracking-tight text-gray-900 min-h-[60px]">
                                    {{replaceThis(article.title, 55)}}</h5>
                            </a>
                            <p class="mb-3 font-normal text-gray-700 min-h-[100px]">
                                {{replaceThis(article.short_description, 120)}}
                            </p>
                            <a :href=" '<?php echo base_url(); ?>site/article_details?id=' + article.id"
                                class="inline-flex items-center py-2 px-3 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
                                Read more
                                <svg aria-hidden="true" class="ml-2 -mr-1 w-4 h-4" fill="currentColor"
                                    viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z"
                                        clip-rule="evenodd"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="<?php echo base_url(); ?>site/latest_news" class=" btn img-btn mt-4">
                    <span>View More >></span>
                </a>
            </div>


        </div>

        <!-- end -->




        <!-- join game changer section -->
        <section class="relative z-10">
            <div
                class="lg:min-h-[100vh] join-contents text-center md:py-[180px] py-[200px] join-iac-bg relative z-10 p-4">
                <h1 data-aos="fade-up" class="md:text-4xl mb-2 text-2xl">
                    Join the league of
                </h1>

                <div class="px-6" data-aos="fade-up">
                    <img src="<?php echo $img_dir; ?>home/join-iacademy/ GC.png" alt=""
                        class="mx-auto block max-w-full h-auto mb-2" />
                </div>

                <div class="flex items-center my-6">
                    <a href="<?php echo base_url(); ?>site/student_application" class="block mx-auto">
                        <img src="<?php echo $img_dir; ?>cebu/home/ btn-ApplyNow.png" class="img-btn" alt="">
                    </a>
                </div>
            </div>
            <div class="bouncy-side absolute right-0 top-0 w-full h-full z-1 justify-end flex">
                <div class="w-[40%] relative hidden lg:block bouncy-div">
                    <img src="<?php echo $img_dir; ?>home/join-iacademy/ Market Launch.png"
                        class="absolute top-[240px] right-[30%] img-bounces" alt="" />
                    <img src="<?php echo $img_dir; ?>home/join-iacademy/ polyhedron 02 copy 8.png"
                        class="absolute bottom-[25%] left-[10px] img-bounces" alt="" />
                    <img src="<?php echo $img_dir; ?>home/join-iacademy/ polyhedron 02 copy 3.png"
                        class="absolute bottom-[20%] right-[30%] img-bounces" alt="" />
                    <img src="<?php echo $img_dir; ?>home/join-iacademy/ polyhedron 02.png"
                        class="absolute top-[130px] left-[14%] img-bounces" alt="" />
                    <img src="<?php echo $img_dir; ?>home/join-iacademy/ polyhedron 01 copy 2.png"
                        class="absolute top-[6%] right-[35%] img-bounces" alt="" />
                </div>
            </div>
        </section>
        <!-- end join game changer section -->

        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.12/vue.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>
        <script>
        new Vue({
            el: "#article-section",
            data: {
                articles: [],
            },

            mounted() {
                axios
                    .get(
                        api_url_article + "osea/external-news?count_content=3"
                    )
                    .then((data) => {
                        this.articles = data.data.data;
                    })
                    .catch((error) => {
                        console.log(error);
                    });

            },

            methods: {
                replaceThis: function(str, width) {
                    if (str.length < width) {
                        return str.substring(0, width);
                    } else {
                        return str.substring(0, width) + "...";
                    }
                },
            },
        });
        </script>