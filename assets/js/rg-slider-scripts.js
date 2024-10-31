;(function($) {

	$(function() {

		var browse = rg_l10n_object.browse;

		$('#add_new_slide')
			.hide()
			.after($('<input>', { 
				'type' : 'button', 
				'name' : 'upload_dummy_browse', 
				'id' : 'upload_dummy_browse',
				'class' : 'button-secondary action',
				'value' : browse
			}))
			.after($('<input>', { 
				'type' : 'text', 
				'name' : 'upload_dummy_input', 
				'id' : 'upload_dummy_input'
			}));

		$('#add_new_slide').after();

		//------------------------------------------------------------------------

		$('#upload_dummy_input, #upload_dummy_browse').on('click', function(e) {
			e.preventDefault();

			$('#add_new_slide').click();
		});

		//------------------------------------------------------------------------

		$('#add_new_slide').on('change', function() {
			$('#upload_dummy_input').val($(this).val());
		});

		//------------------------------------------------------------------------

		$('.code_snippet').on('click', function() {
			if (window.getSelection && document.createRange) {
				var sel = window.getSelection();
				var range = document.createRange();
				range.selectNodeContents(this);
				sel.removeAllRanges();
				sel.addRange(range);
			} else if (document.selection && document.body.createTextRange) {
				var textRange = document.body.createTextRange();
				textRange.moveToElementText(this);
				textRange.select();
			}
		});

	});

})(jQuery);