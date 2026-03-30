
var swiper = new Swiper(".slider-container", {
  spaceBetween: 30,
  loop: true,
  effect: "fade",
  autoplay: {
    delay: 3500,
    disableOnInteraction: false,
  }
});

var swiper = new Swiper(".swiper-testimony", {
  effect: "coverflow",
  grabCursor: true,
  centeredSlides: true,
  slidesPerView: "auto",
  slidesPerView: "3",
  autoplay: {
    delay: 6500,
    disableOnInteraction: false,
  },
  coverflowEffect: {
    rotate: 50,
    stretch: 0,
    depth: 100,
    modifier: 1,
    slideShadows: false,
  },
  pagination: {
    el: ".swiper-pagination",
  },
  breakpoints: {
    320: {
      slidesPerView: 1,
    },
    640: {
      slidesPerView: 1,
    },
    768: {
      slidesPerView: 2,
    },
    1024: {
      slidesPerView: 3,
    },
  }
});


