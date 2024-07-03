<footer class="my-[100px] text-center md:text-left">
    <div class="custom-container">
        <div class="md:flex justify-center md:space-x-4">
            <div class="md:w-[300px]">
                <img src="<?php echo $img_dir?>footer/ bldg.png" class="mx-auto block max-w-full h-auto mb-7" alt=""
                    data-aos="zoom-in" />
            </div>
            <div class="md:w-[calc(100%-300px)] md:p-10 p-4 md:pt-0">
                <div>
                    <h3 class="text-2xl md:text-3xl" data-aos="fade-up">
                        Dare to be
                    </h3>
                    <p class="text-3xl md:text-5xl font-black color-primary" data-aos="fade-up">
                        different.
                    </p>
                    <div class="my-8 md:flex md:space-x-4 space-y-8 md:space-y-0">
                        <div class="md:w-1/3">
                            <h4 class="uppercase color-primary font-bold text-2xl" data-aos="fade-up">
                                <img src="<?php echo $img_dir?>footer/ pin.png" class="inline-block" alt="" />
                                Visit Us
                            </h4>
                            <p class="text-[16px] mt-2" data-aos="fade-up">
                                Filinvest Cebu Cyberzone Tower 2 Salinas Drive corner W. Geonzon St., Brgy. Apas, Lahug,
                                Cebu City
                            </p>
                        </div>

                        <div class="md:w-1/2">
                            <h4 class="uppercase color-primary font-bold text-2xl" data-aos="fade-up">
                                Contact Us
                            </h4>
                            <p class="text-[16px] mt-2" data-aos="fade-up">
                            Email:
                            <br /><br />                            
                            inquirecebu@iacademy.edu.ph<br />
                            admissionscebu@iacademy.edu.ph
                            </p>
                            <p class="text-[16px] mt-2" data-aos="fade-up">
                            Landline:
                            <br /><br />
                            +63 32 520 4888
                            </p>
                        </div>

                        <div class="md:w-1/3">
                            <h4 class="uppercase color-primary font-bold text-2xl" data-aos="fade-up">
                                <a href="https://iacademy.edu.ph/privacypolicy.htm" target="_blank"><u>Privacy Policy</u></a>
                            </h4>
                            <p class="text-[19px] mt-2" data-aos="fade-up">

                            </p>
                        </div>
                    </div>
                </div>
                <div class="md:w-1/3 hidden">
                    <h4 class="uppercase color-primary font-bold text-2xl" data-aos="fade-up">
                        Stay Connected
                    </h4>
                    <div class="text-[19px] mt-2 flex space-x-2 items-center justify-center md:justify-start"
                        data-aos="fade-up">
                        <a href="https://www.facebook.com/iACADEMY" target="_blank">
                            <img class="img-btn" src="<?php echo $img_dir?>footer/ Facebook.png" data-aos="zoom-in" />
                        </a>
                        <a href="https://www.instagram.com/iacademy_edu/" target="_blank">
                            <img class="img-btn" src="<?php echo $img_dir?>footer/ Instagram.png" data-aos="zoom-in" />
                        </a>
                        <a href="https://www.youtube.com/user/iacademycollege" target="_blank">
                            <img class="img-btn" src="<?php echo $img_dir?>footer/ Youtube.png"
                                data-aos="zoom-in" /></a>
                        <a href="https://twitter.com/iacademy_edu" target="_blank">
                            <img class="img-btn" src="<?php echo $img_dir?>footer/ Twitter.png" data-aos="zoom-in" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
</div>
</div>
<script src="<?php echo base_url(); ?>assets/themes/default/js/jquery.min.js"></script>
<script src="<?php echo $js_dir?>script.js"></script>
<script src="<?php echo $js_dir?>flickity.js"></script>
<script src="<?php echo $js_dir?>owl.carousel.min.js"></script>
<script src="<?php echo $js_dir?>initOwl.js"></script>
<script src="<?php echo $js_dir?>aos.js"></script>
<script>
$(document).ready(function() {
    AOS.init();

    let scrollRef = 0;
    window.addEventListener("scroll", function() {
        scrollRef <= 10 ? scrollRef++ : AOS.refresh();
    });

    // $("#fullpage").fullpage({
    //   anchors: [
    //     "home",
    //     "future",
    //     "programs",
    //     "partners",
    //     "video",
    //     "footer",
    //   ],
    //   navigation: true,
    //   navigationPosition: "right",
    //   navigationTooltips: [
    //     "Game Changing",
    //     "Future",
    //     "Programs",
    //     "Partners",
    //     "Video",
    //     "Contacts",
    //   ],
    //   // onLeave: function () {
    //   //   $(".section [data-aos]").removeClass("aos-animate");
    //   // },
    //   // onSlideLeave: function () {
    //   //   $(".slide [data-aos]").removeClass("aos-animate");
    //   // },
    //   // afterSlideLoad: function () {
    //   //   $(".slide.active [data-aos]").addClass("aos-animate");
    //   // },
    //   // afterLoad: function () {
    //   //   $(".section.active [data-aos]").addClass("aos-animate");
    //   // },
    //   // afterResize: function (width, height) {
    //   //   $(".section.active [data-aos]").addClass("aos-animate");
    //   // },
    // });
});

$.fn.moveIt = function() {
    var $window = $(window);
    var instances = [];

    $(this).each(function() {
        instances.push(new moveItItem($(this)));
    });

    window.onscroll = function() {
        var scrollTop = $window.scrollTop();
        instances.forEach(function(inst) {
            inst.update(scrollTop);
        });
    };
};

var moveItItem = function(el) {
    this.el = $(el);
    this.speed = parseInt(this.el.attr("data-scroll-speed"));
};

moveItItem.prototype.update = function(scrollTop) {
    var pos = scrollTop / this.speed;
    this.el.css("transform", "translateY(" + -pos + "px)");
};

$(function() {
    $("[data-scroll-speed]").moveIt();
});

//  data-flickity='{ "freeScroll": false, "wrapAround": true , "prevNextButtons": false, "selectedAttraction": 0.01, "friction": 0.20 }'
</script>
</body>

</html>