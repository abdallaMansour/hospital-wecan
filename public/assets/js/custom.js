(function($) {
	'use strict';

	//Navbar JS
	$(window).on('scroll',function() {
		if ($(this).scrollTop()>200){  
			$('.navbar-area').addClass("is-sticky");
		}
		else{
			$('.navbar-area').removeClass("is-sticky");
		}
	});

	$('.navbar-nav li a').on('click', function(e){
		var anchor = $(this);
		$('html, body').stop().animate({
			scrollTop: $(anchor.attr('href')).offset().top - 10
		}, 30);
		e.preventDefault();
	});

	$(document).on('click','.navbar-collapse.show',function(e){
		if( $(e.target).is('a') && $(e.target).attr('class')!= 'dropdown-toggle' ){
			$(this).collapse('hide');
		}
	});	

	//Screenshot JS
	$('.screenshot-slider').owlCarousel({
		loop:true,
		margin:30,
		nav:false,
		dots:true,
		rtl:true,
		center:true,
		smartSpeed:2000,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:1
			},
			1000:{
				items:5
			}
		}
	})

	//How Use JS
	$('.how-use-slider').owlCarousel({
		loop:true,
		margin:10,
		nav:false,
		rtl:true,
		dots:true,
		center:true,
		smartSpeed:2000,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:3
			},
			992:{
				items:1
			}
		}
	})


	//Tsetimonial Slider JS
	$('.testimonial-slider').owlCarousel({
		loop:true,
		margin:10,
		rtl:true,
		nav:true,
		navText:[
			"<i class='flaticon-left-arrow'></i>",
			"<i class='flaticon-right-arrow'></i>"
		],
		dots:false,
		items:1,
		smartSpeed:2000,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:2
			},

			992: {
				items:1,
			}
		}
	})

	//Home Popup Video
	$('.popup-vimeo').magnificPopup({
		disableOn:300,
		type: 'iframe',
		mainClass: 'mfp-fade',
		removalDelay: 160,
		preloader: false,

		fixedContentPos: false
	});

	//Company Slider JS
	$('.company-slider').owlCarousel({
		loop:true,
		margin:10,
		rtl:true,
		nav:false,
		dots:false,
		smartSpeed:2000,
		autoplay:true,
		autolayTimeout:1500,
		responsive:{
			0:{
				items:2
			},
			600:{
				items:3
			},
			1000:{
				items:5
			}
		}
	})

	//Testimonial Slider JS
	$('.testimonial-slider-area').owlCarousel({
		loop:true,
		margin:10,
		nav:true,
		rtl:true,
		navText:[
			"<i class='flaticon-left-arrow'></i>",
			"<i class='flaticon-right-arrow'></i>"
		],
		dots:false,
		smartSpeed:2000,
		items:1
	});

	//Company Slider JS
	$('.case-study-slider').owlCarousel({
		loop:true,
		margin:30,
		nav:false,
		dots:true,
		rtl:true,
		smartSpeed:2000,
		stagePadding:20,
		autoplayHoverPause:true,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:2
			},
			1000:{
				items:3
			}
		}
	})

	//Testimonial Slider JS
	$('.testimonial-slider-wrapper').owlCarousel({
		loop:true,
		margin:10,
		nav:true,
		rtl:true,
		dots:false,
		smartSpeed:2000,
		items:1
	})

	//Team Slider JS
	$('.team-slider').owlCarousel({
		loop:true,
		margin:10,
		nav:false,
		rtl:true,
		dots:true,
		smartSpeed:2000,
		responsive:{
			0:{
				items:1
			},
			600:{
				items:3
			},
			1000:{
				items:4
			}
		}
	})

	//Slick Slider Testimonial
	$('.slider-for').slick({
		slidesToShow: 1,
		slidesToScroll: 1,
		arrows: false,
		rtl:true,
		draggable: false,
		fade: true,
		asNavFor: '.slider-nav'
	});

	$('.slider-nav').slick({
		slidesToShow: 3,
		slidesToScroll: 1,
		asNavFor: '.slider-for',
		dots: false,
		arrows: true,
		rtl:true,
		centerMode: true,
		focusOnSelect: true,
		centerPadding: '20px',
		responsive: [
			{
				breakpoint: 450,
				settings: {
				dots: false,
				slidesToShow: 3,  
				centerPadding: '0px',
				}
			},
			{
				breakpoint: 420,
				settings: {
				autoplay: true,
				dots: false,
				slidesToShow: 1,
				centerMode: false,
				}
			}
		]
	});

	
	//Back To Top
	$(window).on('load',function(){
		$('.top-btn').fadeOut();
	});
	
	$(window).scroll(function () {
		if ($(this).scrollTop() != 0) {
				$('.top-btn').fadeIn();
			}
		else {
			$('.top-btn').fadeOut();
		}
	});

	$('.top-btn').on('click',function(){
		$("html, body").animate({ scrollTop: 0 }, 0);
		return false;
	});
	
	// Subscribe form
	$(".newsletter-form").validator().on("submit", function (event) {
		if (event.isDefaultPrevented()) {
		// handle the invalid form...
			formErrorSub();
			submitMSGSub(false, "Please enter your email correctly.");
		} else {
			// everything looks good!
			event.preventDefault();
		}
	});
	function callbackFunction (resp) {
		if (resp.result === "success") {
			formSuccessSub();
		}
		else {
			formErrorSub();
		}
	}
	function formSuccessSub(){
		$(".newsletter-form")[0].reset();
		submitMSGSub(true, "Thank you for subscribing!");
		setTimeout(function() {
			$("#validator-newsletter").addClass('hide');
		}, 4000)
	}
	function formErrorSub(){
		$(".newsletter-form").addClass("animated shake");
		setTimeout(function() {
			$(".newsletter-form").removeClass("animated shake");
		}, 1000)
	}
	function submitMSGSub(valid, msg){
		if(valid){
			var msgClasses = "validation-success";
		} else {
			var msgClasses = "validation-danger";
		}
		$("#validator-newsletter").removeClass().addClass(msgClasses).text(msg);
	}
	
	// AJAX MailChimp
	$(".newsletter-form").ajaxChimp({
		url: "https://hibootstrap.us20.list-manage.com/subscribe/post?u=60e1ffe2e8a68ce1204cd39a5&amp;id=42d6d188d9", // Your url MailChimp
		callback: callbackFunction
	});

	// Showcase Portfolio
	try {
        var mixer = mixitup('#Container', {
            controls: {
                toggleDefault: 'none'
            }
        });
    } catch (err) {}

	//Showcase Popup
	$('.popup-gallery').magnificPopup({
		type: 'image'
	});
	
	//WOW JS
	new WOW().init();

	//Pre Loader
	$(window).on('load',function(){
		$(".loader-content").fadeOut(1000);
	});

	// Switch Btn
	$('body').append("<div class='switch-box'><label id='switch' class='switch'><input type='checkbox' onchange='toggleTheme()' id='slider'><span class='slider round'></span></label></div>");

})(jQuery);

// function to set a given theme/color-scheme
function setTheme(themeName) {
	localStorage.setItem('landset_theme', themeName);
	document.documentElement.className = themeName;
}
// function to toggle between light and dark theme
function toggleTheme() {
	if (localStorage.getItem('landset_theme') === 'theme-dark') {
		setTheme('theme-light');
	} else {
		setTheme('theme-dark');
	}
}
// Immediately invoked function to set the theme on initial load
(function () {
	if (localStorage.getItem('landset_theme') === 'theme-dark') {
		setTheme('theme-dark');
		document.getElementById('slider').checked = false;
	} else {
		setTheme('theme-light');
	document.getElementById('slider').checked = true;
	}
})();


