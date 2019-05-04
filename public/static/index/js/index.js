(function($) {
	$.init();
	$.ready(function() {
		$.previewImage();
		// 图片懒加载 
		$('.mui-content').imageLazyload({
			effect: "fadeIn",
			threshold: 200,
			destroy: false,
			duration: 500,
			placeholder: 'https://timgsa.baidu.com/timg?image&quality=80&size=b9999_10000&sec=1543818677013&di=c996f4a2753a44514e9dcc724445636b&imgtype=0&src=http%3A%2F%2Fbpic.588ku.com%2Felement_origin_min_pic%2F01%2F30%2F60%2F74573b1bd2f3a24.jpg'
		});
		// 监听输入框
		window.addEventListener('resize', function() {
			if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA') {
				window.setTimeout(function() {
					if ('scrollIntoView' in document.activeElement) {
						document.activeElement.scrollIntoView();
					} else {
						document.activeElement.scrollIntoViewIfNeeded();
					}
				}, 0);
			}
		});
		

		// 判断手机还是电脑
		var ua = navigator.userAgent;
		var ipad = ua.match(/(iPad).*OS\s([\d_]+)/),
			isIphone = !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/),
			isAndroid = ua.match(/(Android)\s+([\d.]+)/),
			isMobile = isIphone || isAndroid,
			rows = $('.row')[0],
			content = $('.mui-content')[0]
		if (isMobile) {
			document.addEventListener('touchmove', function(e) {
				e.preventDefault()
			}, false)
			content.className += ' mui-scroll-wrapper';
			rows.className += ' mui-scroll';
			$('.mui-scroll-wrapper').scroll({
				scrollY: true,
				scrollX: false,
				startX: 0,
				startY: 0,
				indicators: true,
				deceleration: 0.0006,
				bounce: true
			});
		} else {
			content.className = ' mui-content';
			rows.className = ' row';
		}


	});
})(mui);
