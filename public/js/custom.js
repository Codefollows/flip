$(document).ready(function() {

	$('[data-toggle="tooltip"]').tooltip();

	$('#toTop').on('click',function (e) {
		e.preventDefault();

		var target = this.hash;
		var $target = $(target);

		$('html, body').stop().animate({
			'scrollTop': 0
		}, 900, 'swing');
	});

	$('.custom-nav .expand').on('click', function(e) {
		e.preventDefault();
		let parent = $(this).closest('.custom-nav');
		parent.toggleClass("custom-nav-extended");
	});

	let slides    = $(document).find('.slide');
	let slide_cnt = slides.length;
	let slide_dur = 3000;
	let current_slide;

	for (let i = 0; i < slides.length; i++) {
		let element = $(slides[i]);
		if (element.hasClass("slide-active"))
			current_slide = i;
	}

	let slider_func = function() {
		$(slides[current_slide]).removeClass("slide-active");

		current_slide++;

		if (current_slide > (slide_cnt - 1)) {
			current_slide = 0;
		}

		let next_slide = slides[current_slide];

		$(slides[current_slide]).addClass('slide-active');
	};

	let interval = setInterval(slider_func, slide_dur);

	$('.slider').mouseenter(function() { clearInterval(interval) });
	$('.slider').mouseleave(function() { interval = setInterval(slider_func, slide_dur) });

	$('.slider-next').click(function(e) {
		e.preventDefault();
		advanceSlides(1);
	});
	
	$('.slider-prev').click(function(e) {
		e.preventDefault();
		advanceSlides(-1);
	})

	function advanceSlides(value) {
		$(slides[current_slide]).removeClass("slide-active");

		current_slide += value;

		if (current_slide > (slide_cnt - 1)) {
			current_slide = 0;
		}

		if (current_slide < 0)
			current_slide = slide_cnt - 1;

		let next_slide = slides[current_slide];

		$(slides[current_slide]).addClass('slide-active');
	};
});