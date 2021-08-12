CKEDITOR.dialog.add('luxEmail4LinkDialog', function(editor)
{
	/**
	 * pendant to jQuery function "closest"
	 *
	 * @param elementType
	 * @param currentSelection
	 * @returns {*}
	 */
	var getParentElement = function(elementType, currentSelection) {
		var parentElements = currentSelection.getParents();
		for (var i = 0; i < parentElements.length; i++) {
			if (parentElements[i].getName() === elementType) {
				return parentElements[i];
			}
		}
		return null;
	};

	return {
		title: editor.lang.luxEmail4Link.dialog,
		minWidth: 800,
		minHeight: 500,
		contents: [
			{
				id: 'general',
				label: 'Settings',
				elements: [
					{
						type: 'text',
						id: 'title',
						label: editor.lang.luxEmail4Link.dialogTitle,
						validate: CKEDITOR.dialog.validate.notEmpty(editor.lang.luxEmail4Link.validationNotEmpty),
						required: true,
						commit: function(data) {
							data.title = this.getValue();
						}
					},
					{
						type: 'textarea',
						id: 'text',
						minHeight: 300,
						label: editor.lang.luxEmail4Link.dialogText,
						commit: function(data) {
							data.text = this.getValue();
						}
					},
					{
						type: 'checkbox',
						id: 'sendEmail',
						label: editor.lang.luxEmail4Link.dialogSendEmail,
						commit: function(data) {
							data.sendEmail = this.getValue();
						}
					},
					{
						type: 'html',
						html : '<p>' + editor.lang.luxEmail4Link.dialogExplanation + '</p>'
					},
					{
						type: 'html',
						html : '<p><a href="https://www.in2code.de/produkte/lux-typo3-marketing-automation/" target="_blank" rel="noopener" style="font-weight:bold;text-decoration:underline;cursor:pointer;">' + editor.lang.luxEmail4Link.dialogPoweredBy + '</a></p>'
					}
				]
			}
		],

		// If editor submits the dialog
		onOk: function() {
			var data = {};
			this.commitContent(data);
			var parent = getParentElement('a', editor.getSelection().getStartElement());
			parent.setAttribute('data-lux-email4link-title', data.title);
			parent.setAttribute('data-lux-email4link-text', data.text);
			parent.setAttribute('data-lux-email4link-sendEmail', data.sendEmail);
		},

		// On opening dialog box
		onShow: function() {
			var parent = getParentElement('a', editor.getSelection().getStartElement());
			this.setValueOf('general', 'title', parent.getAttribute('data-lux-email4link-title') || '');
			this.setValueOf('general', 'text', parent.getAttribute('data-lux-email4link-text') || '');
			var sendEmailStatus = parent.getAttribute('data-lux-email4link-sendEmail') || '';
			this.setValueOf('general', 'sendEmail', sendEmailStatus === 'true');
		}
	};
});
