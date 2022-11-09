<div class="news_details_container article__wrapper" id="article_container">


    <div class="custom-container mt-[150px]">

        <div v-if="loader_spinner" class="text-center mt-3 mb-3">
            <span class="artx_loader mb-2"><span class="artx_loader-inner"></span></span>
            Loading please wait..
        </div>

        <div v-else>
            <h2 class="px-4 color-primary text-3xl font-bold mb-4">{{type_name}} Articles</h2>
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
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.12"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.21/lodash.min.js"
    integrity="sha512-WFN04846sdKMIP5LKNphMaWzU7YpMyCU245etK3g/2ARYbPK9Ub18eG+ljU96qKRCWh+quCY7yefSmlkQw1ANQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

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