$(window).scroll(function () {
  if ($(window).scrollTop() >= 60) {
    $(".horizontal-nav").addClass("shadowed");
  } else {
    $(".horizontal-nav").removeClass("shadowed");
  }
});

// $(function () {
//   $(".dot")
//     .not(".is-selected")
//     .click(function () {
//       $(".rotate").toggleClass("down");
//     });
// });

function openNav() {
  document.getElementById("mySidenav").style.width = "250px";
  document.getElementById("main").style.marginLeft = "250px";
}

function closeNav() {
  document.getElementById("mySidenav").style.width = "0";
  document.getElementById("main").style.marginLeft = "0";
  document.body.style.backgroundColor = "white";
}

function openNav() {
  document.getElementById("mySidenav").style.width = "270px";
  document.getElementById("bodycontainer").style.width = "100%";
}

function closeNav() {
  document.getElementById("mySidenav").style.width = "0";
  document.getElementById("bodycontainer").style.width = "0";
}
