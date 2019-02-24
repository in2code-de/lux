CKEDITOR.plugins.add('luxEmail4Link', {
	lang: [
		'en', 'de'
	],
	init: function(editor) {

		/**
		 * Add buttons
		 *
		 * @param context
		 * @returns {void}
		 */
		var addButtons = function (context) {
			editor.ui.addButton('Email4Link', {
				label: editor.lang.luxEmail4Link.button,
				command: 'luxEmail4Link',
				toolbar: 'links',
				icon: context.path + 'icons/email4link.svg'
			});
		};

		/**
		 * Add click events to buttons
		 *
		 * @param context
		 * @returns {void}
		 */
		var addButtonListeners = function(context) {
			editor.addCommand('luxEmail4Link', new CKEDITOR.dialogCommand('luxEmail4LinkDialog'));
			CKEDITOR.dialog.add('luxEmail4LinkDialog', context.path + 'dialogs/dialog.js');
		};

		addButtons(this);
		addButtonListeners(this);

		// Disable button on start
		editor.on('instanceReady', function(event) {
			editor.getCommand('luxEmail4Link').setState(CKEDITOR.TRISTATE_DISABLED);
		});

		// Enable or disable button depending on selection
		editor.on('selectionChange', function(event) {
			var parent = getParentElement('a', editor.getSelection().getStartElement());
			if (parent !== null) {
				if (parent.getAttribute('data-lux-email4link-title') === null) {
					editor.getCommand('luxEmail4Link').setState(CKEDITOR.TRISTATE_OFF);
				} else {
					editor.getCommand('luxEmail4Link').setState(CKEDITOR.TRISTATE_ON);
				}
			} else {
				editor.getCommand('luxEmail4Link').setState(CKEDITOR.TRISTATE_DISABLED);
			}
		});

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
	}
});
