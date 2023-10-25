jQuery(document).ready(function() {
	jQuery(document).on("click", "#img_1", function(ev){
		var bigimage = jQuery( ev.target ).closest('a').data('image');
		jQuery(".zoom-image").attr('src',bigimage);
	});
});