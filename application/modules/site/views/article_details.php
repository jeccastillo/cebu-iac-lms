<div class="news_details_container article__wrapper" id="news_details_container">


    <div class="custom-container mt-[150px]">

        <div v-if="loader_spinner" class="text-center mt-3 mb-3">
            <span class="artx_loader mb-2"><span class="artx_loader-inner"></span></span>
            Loading please wait..
        </div>

        <div class="row" v-else>
            <div :class="news.type == 'blog' ? 'col-lg-9 col-sm-12' : 'col-lg-12 col-sm-12'">

                <h2 class="news__title color-primary text-2xl">{{ news.title }}</h2>
                <!-- <div class="" v-if="news.type == 'internal' || news.type == 'blog'"> -->
                <span class="span__nw" v-if="!loader_spinner"> <span>{{ news.date }} </span>
                    <!-- </div> -->
                    <hr class="w-100 h-[4px] bg-blue-700 my-5">

                    <img v-if="news.type == 'blog'" onerror="this.src='<?php echo $img_dir; ?>missing.jpg';"
                        :src="news.header_image" alt="" class="mb-2 max-w-full h-auto mx-auto block h-[200px]">
                    <div class="d-block mx-auto logo_single_page_ mb-2 mt-2">
                        <img v-if="news.type == 'external'" onerror="this.src='<?php echo $img_dir; ?>missing.jpg';"
                            :src="news.logo" alt="" class="mb-2 max-w-full h-auto mx-auto block h-[200px]">
                    </div>
                    <div class="news__content__text mt-12" id="news__content__text" v-html="news.content"></div>

                    <div class="mt-4" v-if="news.type == 'internal' && (news.attachment)">
                        <a :href="news.download_link" class="btn btn-iac btn-sm" target="_blank"> <i
                                class="fa fa-arrow-down"></i> Download PDF version </a>
                    </div>
            </div>
            <div class="col-lg-3 col-sm-12" v-if="news.type == 'blog'">
                <div class="mb-2" style="margin-top:60px">
                    <h4 class="news__title text-blue"> More Articles </h4>
                </div>
                <div class="w-100" v-for="article in more_articles">
                    <img onerror="this.src='<?php echo $img_dir; ?>missing.jpg';" :src="article.image_url" alt=""
                        class="mb-2 img-fluid mt-2 d-block mx-auto">
                    <div><a :target="article.type == 'external' ? '_blank' : ''"
                            :href="article.type == 'blog' ? '<?php echo base_url(); ?>homev4/news_details?id=' + article.id  : article.link"
                            class="mb-2"> {{ article.title }} </a></div>
                </div>
            </div>
        </div>

        <hr class="w-100 h-[4px] bg-blue-700 my-5">

        <div class="text-right news__details_back mt-5 col-lg-12" v-if="!loader_spinner">
            <a v-if="news.type == 'internal'" href="<?php echo base_url(); ?>homev4/latest_news"
                class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 hover:border-blue-500 rounded"
                @click="goBack()">See all news</a>
            <a v-if="news.type == 'external' && news.template_type == 'article'" target="_blank" :href="news.link"
                class="bg-blue-500 hover:bg-blue-400 text-white font-bold py-2 px-4 border-b-4 border-blue-700 hover:border-blue-500 rounded">
                View original article</a>
            <a style="cursor:pointer;"
                class="bg-gray-200 hover:bg-gray-300 text-gray-500 font-bold py-2 px-4 border-b-4 border-gray-700 hover:border-gray-500 rounded"
                @click="goBack()">Back</a>
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

p {
    margin-bottom: 1rem;
}
</style>

<script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>assets/themes/default/js/script.js"></script>
<script src="<?php echo base_url(); ?>assets/themes/default/js/vue.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
setTimeout(function() {
    $('.loader__running').css("display", "none");

    if (screen.width > 600) {}
    $("#card-sign-up").css("display", "flex");
}, 2000);

$('.navbar-expand-lg').addClass("shadowed");

$(".btn_view_art_modal").click(function() {
    var card = $('.card-holder');
    card.css('display', 'flex');
});
</script>



<script>
new Vue({
    el: '#news_details_container',
    data: {
        news: [],
        loader_spinner: true,
        more_articles: []
    },

    metaInfo() {
        return {
            htmlAttrs: {
                lang: 'en',
                amp: true
            },
            meta: [
                // Facebook OpenGraph
                {
                    'property': 'og:title',
                    'content': 'Vue Social Cards Example'
                },
                {
                    'property': 'og:description',
                    'content': 'Vue sample site showing off Twitter and Facebook Cards.',
                    'vmid': 'og:description'
                }
            ]
        }
    },

    mounted() {

        $('.latest_news_filter li a').on('click', function() {
            $(this).parent().addClass('active').siblings().removeClass('active');
        });

        var url_string = window.location.href;
        var url = new URL(url_string);
        var param = url.searchParams.get("id");

        // https://employeeportal.iacademy.edu.ph/api/v1/osea/
        // http://222.127.137.134:8081/api/v1/osea/

        this.loader_spinner = true;
        axios.get(api_url_article + 'osea/news/' + param)
            .then((data) => {
                this.news = data.data.news;
                this.more_articles = data.data.more_articles;

                setTimeout(() => {
                    this.loader_spinner = false;
                    $("img").closest("span").addClass("spanRes");
                }, 500)

            })
            .catch((error) => {
                console.log(error);
            })


    },

    methods: {

        filterNews: function(type) {
            this.all_news = [];
            this.loader_spinner = true;
            axios.get('https://employeeportal.iacademy.edu.ph/api/v1/osea/exhibits/49/courses/20/artworks')
                .then((data) => {
                    this.all_news = data.data.courses;
                    this.loader_spinner = false;
                })
                .catch((error) => {
                    console.log(error);
                })
        },

        goBack: function() {
            window.history.back();
        },

    }

})
</script>