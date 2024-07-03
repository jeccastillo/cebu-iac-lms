<div class="news_details_container article__wrapper" id="article_container">


    <div class="custom-container mt-[150px]">

        <div v-if="loader_spinner" class="text-center mt-3 mb-3">
            <span class="artx_loader mb-2"><span class="artx_loader-inner"></span></span>
            Loading please wait..
        </div>

        <div v-else>

            <h2 class="px-4 color-primary text-3xl font-bold mb-4 uppercase">{{type_name}}</h2>

            <div class="p-4">
                <div v-if="type == 'se'">
                    <p class="mb-2">
                        The Bachelor of Science in Computer Science (BSCS) program is designed
                        to
                        provide
                        students with a thorough and advanced understanding of the various computing concepts and
                        theories,
                        algorithm development and analysis, and applying strong design and development principles in the
                        construction of software systems to solve complex, real-world problems.
                    </p>

                    <p>
                        By specializing in Software Engineering, students will be able to apply engineering concepts and
                        methods
                        in the development and improvement of software systems. They will be exposed to new technologies
                        and
                        computing techniques, which they can apply to devise new ways of using computers. They will
                        acquire
                        the
                        necessary skills to analyze, plan, and create software systems instead of merely writing code
                        for
                        computer programs. In addition to these, the students will also have a strong background in
                        modern
                        management techniques as applied to software development. iACADEMY’s Computer Science Program
                        takes
                        11
                        trimesters to finish.
                    </p>
                </div>

                <div v-if="type == 'gd'">
                    <p class="mb-2">
                        The Bachelor of Science in Entertainment and Multimedia Computing (BSEMC) program is designed to
                        provide students with a thorough and advanced understanding of the various study and use of
                        concepts, principles, and techniques of computing in the design and development of multimedia
                        products and solutions. It includes various applications such as in science, entertainment,
                        education, simulations and advertising.

                    </p>

                    <p>
                        By specializing in Game Development, students will be able to apply fundamental and advanced
                        theories in game design, scientific simulations, use and development of gaming technology and
                        tools, and production of commercially acceptable digital games and viable solutions for use in
                        entertainment and scientific applications. In addition to these, the students will be prepared
                        to be game development professionals with specialized knowledge, competencies and values in
                        designing, developing, and producing digital games and / or tools, and in managing game
                        development projects for various applications.

                    </p>
                </div>

                <div v-if="type == 'animation'">
                    <p class="mb-2">
                        The Bachelor of Science in Animation is an 11-term program which addresses the technical
                        production needs of the industry. The program provides students with skills training in both 2D
                        and 3D animation, including the use of state-of-the-art tools and software such as Toon Boom
                        Animation software, to produce pipeline-ready graduates. The program prepares students to become
                        globally competitive animators, directors and content creators to contribute to the uplifting of
                        both the local and global Animation industry.
                    </p>

                </div>

                <div v-if="type == 'mma'">
                    <p class="mb-2">
                        The Bachelor of Arts in Multimedia Arts and Design is an 11 term program designed to address the
                        growing need for highly qualified, multidisciplinary professionals in the creative industries.
                        Within the heart of the program is the goal of expanding the students’ talents in various
                        creative fields developing the students potential to create innovative content for print, web,
                        and audiovisual communication. The MAD Program at SODA develops students into well rounded
                        creatives who are technology adept critical thinkers and industry ready graduates.
                    </p>
                </div>

                <div v-if="type == 'rem'">
                    <p class="mb-2">

                    </p>
                </div>

            </div>
            <hr>

            <h2 class="px-4 color-primary text-3xl font-bold mb-4 mt-10"> Articles</h2>

            <div class="md:flex flex-wrap">
                <div class="lg:w-1/3 p-3 md:w-1/2 w-full" v-for="article in all_news">
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
        </div>



    </div>
</div>

<style media="screen">
img {
    max-width: 100%;
    height: auto;
}

.spanRes {
    width: 100% !important;
    height: inherit !important;
}

.news__content__text * {
    font-family: 'Poppins' !important;
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
    el: '#article_container',
    data: {
        all_news: [],
        loader_spinner: true,
        type: '',
        type_name: '',
    },

    mounted() {

        var url_string = window.location.href;
        var url = new URL(url_string);
        this.type = url.searchParams.get("type");

        if (this.type == 'se') {
            this.type_name = 'Software Engineering'
        } else if (this.type == 'gd') {
            this.type_name = 'Game Development'
        } else if (this.type == 'animation') {
            this.type_name = 'Animation'
        } else if (this.type == 'mma') {
            this.type_name = 'Multimedia Arts & Design'
        } else if (this.type == 'rem') {
            this.type_name = 'Real Estate Management'
        }



        $('.latest_news_filter li a').on('click', function() {
            $(this).parent().addClass('active').siblings().removeClass('active');
        });

        this.filterNews("all");

    },

    methods: {


        filterNews: function(type) {
            this.all_news = [];
            this.loader_spinner = true;
            axios.get(api_url_article + 'osea/external-news?count_content=15&branch=Cebu&course=' + this
                    .type)
                .then((data) => {
                    this.all_news = data.data.data;
                    this.loader_spinner = false;
                })
                .catch((error) => {
                    console.log(error);
                })
        },


        replaceThis: function(str, width) {
            if (str.length < width) {
                return str.substring(0, width);
            } else {
                return str.substring(0, width) + "...";
            }
        }

    }

})
</script>