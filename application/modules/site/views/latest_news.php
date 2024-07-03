<div class="" id="fullpages">
    <section id="hero" class="section section_port relative overflow-x-hidden">
        <div class="w-full">
            <div
                class="custom-container relative h-full mb-[100px] md:mb-[10px] md:px-[150px] md:pt-[190px] md:pb-[80px] pb-[50px]">
                <img src="<?php echo $img_dir; ?>home-poly/blue-poly.png"
                    class="absolute top-0 md:right-[15%] hidden md:block" alt="" />
                <img src="<?php echo $img_dir; ?>home-poly/yellow-poly.png"
                    class="absolute top-[5%] md:left-[13%] hidden md:block" alt="" />
                <img src="<?php echo $img_dir; ?>home-poly/red-poly.png"
                    class="absolute top-[41%] md:left-[-9%] hidden md:block" alt="" />

                <img src="<?php echo $img_dir; ?>home-poly/peach-poly.png" class="absolute bottom-[6%] hidden md:block"
                    alt="" />

                <img src="<?php echo $img_dir; ?>home-poly/lyellow-poly.png"
                    class="absolute top-[60%] md:right-[2%] hidden md:block" alt="" />

                <img src="<?php echo $img_dir; ?>home-poly/lblue-poly.png"
                    class="absolute top-[30%] md:right-[6%] hidden md:block" alt="" />

                <!-- parallax object end -->
                <div class="block mt-[100px] md:mt-0 h-full max-w-[980px] mx-auto">
                    <img src="<?php echo $img_dir; ?>p3/latest_news/latest_news.png" alt="" class="block mx-auto" />
                    <h3 class="text-center md:text-2xl">
                        See what's going on in our
                        <span class="font-bold color-primary">Game Changing</span>
                        world.
                    </h3>
                </div>
            </div>
        </div>
    </section>
    <!-- end -->

    <section class="my-[50px]" id="blogs">
        <div id="blog_container" class=" custom-container relative">

        </div>
        <div id="pagination" class="flex justify-center">
        </div>
    </section>
</div>

<!-- end -->

<style>
.J-paginationjs-page.active {
    color: #014fb3;
    text-decoration: underline;
    font-weight: bold;
}

.paginationjs ul {
    list-style: none;
    display: flex;
}

.paginationjs li {
    padding: 0.5rem;
    font-size: 18px;
}
</style>

<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script src="<?php echo $js_dir; ?>jquery.min.js"></script>
<script src="<?php echo $js_dir; ?>pagination.min.js"></script>

<script>
var dataContainer = $('#blog_container');

$('#pagination').pagination({
    dataSource: main_api_url + 'osea/external-news?count_content=3',
    locator: 'data',
    pageSize: 1,
    autoHidePrevious: true,
    autoHideNext: true,
    showFirstOnEllipsisShow: true,
    showLastOnEllipsisShow: true,
    pageNumber: 100,
    alias: {
        pageNumber: 'page',
        pageSize: 'limit'
    },
    totalNumberLocator: function(response) {

        return response.meta.last_page;
    },

    ajax: {
        beforeSend: function() {
            dataContainer.html(
                '<div class="d-flex w-100 justify-content-center mt-3 mb-3"><div class="lds-ripple"><div></div><div></div></div></div>'
            );
        }
    },

    callback: function(response, pagination) {

        $("html").animate({
            scrollTop: 0
        }, "slow");
        var dataHtml = '<div class="milestones_articles">';

        $.each(response, function(index, item) {



            var html_a =
                `
                  <a href="<?php echo base_url() ?>site/article_details?id=` +
                item.id + `"
                            class="color-primary font-medium relative after:content-[''] after:absolute after:h-[2px] after:left-0 after:bottom-[-5px] after:w-full after:bg-[#014fb3]">
                            Read More
                   </a>
               `



            dataHtml += `<div class="lg:flex lg:space-x-6 space-x-0 items-center mb-10" v-for="x in 3">
            <div class="lg:w-1/2">
                <img class="mx-auto block mb-2" style="max-height:380px" src="` + item.image_url + `"  onerror="this.src='<?php echo $img_dir; ?>missing.jpg';" alt="" />
            </div>
            <div class="lg:w-1/2 text-center lg:text-left">
                <h2 class="md:text-[30px] text-1xl font-bold color-primary mb-2 mt-4" style="line-height:1">
                    ` + item.title + `
                </h2>
                <h3 class="font-bold mt-[-3px]">` + item.date + `</h3>

                <div class="mt-6">
                  ` + item.short_description + `

                    <div class="mt-7">
                       ` + html_a + `
                    </div>
                </div>
            </div>
        </div>`;
        });

        dataHtml += '</div>';
        dataContainer.html(dataHtml);
    }
})
</script>
<!-- end -->