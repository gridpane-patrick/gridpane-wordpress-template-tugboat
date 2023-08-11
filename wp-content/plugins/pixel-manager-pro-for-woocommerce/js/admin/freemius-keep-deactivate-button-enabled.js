(function () {
	try {

		let observer = new MutationObserver(function (mutations) {
			mutations.forEach(function (mutation) {
				if (mutation.attributeName === "class") {
					let attributeValue = jQuery(mutation.target).prop(mutation.attributeName);
					if (attributeValue.includes('disabled')) {
						jQuery('.fs-modal').find('.button-deactivate').removeClass('disabled');
					}
				}
			});
		});

		observer.observe(jQuery('.fs-modal').find('.button-deactivate')[0], {
			attributes: true
		});

	} catch (error) {
		console.error(error);
	}
})();
