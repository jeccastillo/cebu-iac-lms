$(function () {
  //init owl for shs slider
  var outerrowl = $(".owl-carousel-outer.owl-carousel.owl-se");
  outerrowl.owlCarousel({
    loop: true,
    margin: 10,
    dots: true,
    smartSpeed: 1000,
    responsiveClass: true,
    autoHeight: true,
    touchDrag: false,
    mouseDrag: false,
    responsive: {
      0: {
        items: 1,
        nav: false,
        loop: false,
      },
      600: {
        items: 1,
        nav: false,
        lopp: false,
      },
      1000: {
        items: 1,
        nav: false,
        loop: false,
        margin: 20,
      },
    },
  });

  var innerowl = $(".owl-carousel-inner.owl-carousel.owl-se");
  innerowl.owlCarousel({
    loop: true,
    navs: true,
    navText: ["<", ">"],
    smartSpeed: 500,
    margin: 10,
    responsiveClass: true,
    responsive: {
      0: {
        items: 2,
        nav: true,
      },
      600: {
        items: 2,
        nav: true,
      },
      1000: {
        items: 2,
        nav: true,
      },
    },
  });

  outerrowl.on("change.owl.carousel", function (event) {
    if (event.property.name == "position" && event.item.count == 2) {
      console.log(1);
      $(this).find(".rotate").toggleClass("start-rot");

      if (event.page.index == 0) {
        $("html, body").animate(
          {
            scrollTop: $("#shs-three-slider").offset().top,
          },
          1000
        );
      }
    }
    console.log(event.property.name);
  });

  //end shs

  //   soc slider
  var outerrowlsoc = $(".owl-carousel-outer.owl-carousel.owl-gd");
  outerrowlsoc.owlCarousel({
    loop: true,
    margin: 10,
    dots: true,
    smartSpeed: 1000,
    responsiveClass: true,
    autoHeight: true,
    touchDrag: false,
    mouseDrag: false,
    responsive: {
      0: {
        items: 1,
        nav: true,
        loop: false,
      },
      600: {
        items: 1,
        nav: true,
        lopp: false,
      },
      1000: {
        items: 1,
        nav: false,
        loop: false,
        margin: 20,
      },
    },
  });

  var innerowlsoc = $(".owl-carousel-inner.owl-carousel.owl-gd");
  innerowlsoc.owlCarousel({
    loop: true,
    navs: true,
    navText: ["<", ">"],
    smartSpeed: 500,
    margin: 10,
    responsiveClass: true,
    responsive: {
      0: {
        items: 2,
        nav: true,
      },
      600: {
        items: 2,
        nav: true,
      },
      1000: {
        items: 2,
        nav: true,
      },
    },
  });

  outerrowlsoc.on("change.owl.carousel", function (event) {
    if (event.property.name == "position" && event.item.count == 2) {
      $(this).find(".rotate").toggleClass("start-rot");
      console.log("soc");
      if (event.page.index == 0) {
        $("html, body").animate(
          {
            scrollTop: $("#gd-two-slider").offset().top,
          },
          1000
        );
      }
    }
  });
  //end soc

  //   sobla slider
  var outerrowlsobla = $(".owl-carousel-outer.owl-carousel.owl-ani");
  outerrowlsobla.owlCarousel({
    loop: true,
    margin: 10,
    dots: true,
    smartSpeed: 1000,
    responsiveClass: true,
    autoHeight: true,
    touchDrag: false,
    mouseDrag: false,
    responsive: {
      0: {
        items: 1,
        nav: true,
        loop: false,
      },
      600: {
        items: 1,
        nav: true,
        lopp: false,
      },
      1000: {
        items: 1,
        nav: false,
        loop: false,
        margin: 20,
      },
    },
  });

  var innerowlsobla = $(".owl-carousel-inner.owl-carousel.owl-ani");
  innerowlsobla.owlCarousel({
    loop: true,
    navs: true,
    navText: ["<", ">"],
    smartSpeed: 500,
    margin: 10,
    responsiveClass: true,
    responsive: {
      0: {
        items: 2,
        nav: true,
      },
      600: {
        items: 2,
        nav: true,
      },
      1000: {
        items: 2,
        nav: true,
      },
    },
  });

  outerrowlsobla.on("change.owl.carousel", function (event) {
    if (event.property.name == "position" && event.item.count == 2) {
      $(this).find(".rotate").toggleClass("start-rot");
      if (event.page.index == 0) {
        $("html, body").animate(
          {
            scrollTop: $("#ani-two-slider").offset().top,
          },
          1000
        );
      }
    }
  });
  //end sobla

  //   soda slider
  var outerrowlsoda = $(".owl-carousel-outer.owl-carousel.owl-mma");
  outerrowlsoda.owlCarousel({
    loop: true,
    margin: 10,
    dots: true,
    smartSpeed: 1000,
    responsiveClass: true,
    autoHeight: true,
    touchDrag: false,
    mouseDrag: false,
    responsive: {
      0: {
        items: 1,
        nav: true,
        loop: false,
      },
      600: {
        items: 1,
        nav: true,
        lopp: false,
      },
      1000: {
        items: 1,
        nav: false,
        loop: false,
        margin: 20,
      },
    },
  });

  var innerowlsoda = $(".owl-carousel-inner.owl-carousel.owl-mma");
  innerowlsoda.owlCarousel({
    loop: true,
    navs: true,
    navText: ["<", ">"],
    smartSpeed: 500,
    margin: 10,
    responsiveClass: true,
    responsive: {
      0: {
        items: 2,
        nav: true,
      },
      600: {
        items: 2,
        nav: true,
      },
      1000: {
        items: 2,
        nav: true,
      },
    },
  });

  outerrowlsoda.on("change.owl.carousel", function (event) {
    if (event.property.name == "position" && event.item.count == 2) {
      $(this).find(".rotate").toggleClass("start-rot");
      if (event.page.index == 0) {
        $("html, body").animate(
          {
            scrollTop: $("#mma-two-slider").offset().top,
          },
          1000
        );
      }
    }
  });
  //end soda
});
