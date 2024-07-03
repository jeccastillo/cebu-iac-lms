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