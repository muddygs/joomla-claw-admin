jQuery(function () {
	if (jQuery("#category_id").length && jQuery("#subject").length ) {
		jQuery("#category_id").on("change", function() {
			var subject = jQuery("#category_id :selected").text();
			(document.getElementById("subject") as HTMLInputElement).value = subject.replace(/\s+\-\s/,"");
		})
	}
});


