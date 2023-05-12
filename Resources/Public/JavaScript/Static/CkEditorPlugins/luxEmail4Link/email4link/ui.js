import {Core, UI} from "@typo3/ckeditor5-bundle.js";
import View from "./view.js";
export default class Email4LinkUI extends Core.Plugin {
  static get requires() {
    return [UI.ContextualBalloon];
  }

  init() {
    const editor = this.editor;

    // Create the balloon and the form view.
    this._balloon = this.editor.plugins.get(UI.ContextualBalloon);
    this.formView = this._createFormView();

    editor.ui.componentFactory.add('email4link', (editor) => {
      const button = new UI.ButtonView(editor);
      button.set({
        label: 'Email4Link',
        icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 70.9 70.9" width="100"><title>LUX email4link</title><path d="M27.4 2.7s-2.4 6.7.9 9.3 4.8 7.1 4.3 9.1c0 0 12.7-.5 16.4 7.7 0 0-.2 1.3 7.7 4.7 0 0 2.7.9-2.4 6.9 0 0 .8 1.7-2.8 2.9 0 0 .8 4.4-3.9 3.6-4.7-.8-11.1-3.8-6.9 3.3 0 0 7.9 7.8 6.4 14.6 0 0-4.9-12.4-20.5-14.1 0 0 14 3.2 19.1 17.4 0 0-2.8-5.7-15.2-9.1 0 0 3.6 1.3 4.9 5.4 0 0-1-1.9-12.4-5.2s-9.6-14.8-9-17.1c.9-3.5 3.5-12.4 8.9-17.2 0 0-3.1-2.7 2.1-13.5-.1.1-.9-4.7 2.4-8.7z"/></svg>',
        withText: false,
        isToggleable: true
      });

      const email4linkCommand = this.editor.commands.get('email4link');

      button.bind('isEnabled').to(email4linkCommand, 'isEnabled');

      // Show the UI on button click.
      this.listenTo(button, 'execute', () => {
        this._showUI();
      });

      return button;
    });
  }

  _createFormView() {
    const editor = this.editor;
    const formView = new View(editor.locale);

    // On submit
    this.listenTo(formView, 'submit', () => {
      editor.model.change(writer => {
        const value = {
          sendEmail: String(formView.checkboxInputView.isChecked),
          title: String(formView.titleInputView.fieldView.element.value),
          text: String(formView.descriptionInputView.fieldView.element.value)
        }

        editor.execute('email4link', value);
      });

      this._hideUI();
    } );

    // On abort
    this.listenTo(formView, 'cancel', () => {
      this._hideUI();
    });

    // On outside click
    UI.clickOutsideHandler( {
      emitter: formView,
      activator: () => this._balloon.visibleView === formView,
      contextElements: [ this._balloon.view.element ],
      callback: () => this._hideUI()
    });

    return formView;
  }

  _showUI() {
    this._balloon.add({
      view: this.formView,
      position: this._getBalloonPositionData()
    });

    const link = this._getSelectedLink();

    if (link) {
      this.formView.titleInputView.fieldView.element.value = link.getAttribute('emailTitle') || '';
      this.formView.descriptionInputView.fieldView.element.value = link.getAttribute('emailText') || '';
      this.formView.checkboxInputView.isChecked = link.getAttribute('sendEmail') || false;
    }

    this.formView.focus();
  }

  _hideUI() {
    this.formView.titleInputView.fieldView.element.value = '';
    this.formView.descriptionInputView.fieldView.element.value = '';
    this.formView.checkboxInputView.isChecked = false;
    this.formView.element.reset();

    this._balloon.remove(this.formView);

    this.editor.editing.view.focus();
  }

  _getBalloonPositionData() {
    const view = this.editor.editing.view;
    const viewDocument = view.document;
    let target = null;
    target = () => view.domConverter.viewRangeToDom( viewDocument.selection.getFirstRange() );
    return {target};
  }

  _getSelectedLink() {
    const model = this.editor.model;
    const selection = model.document.selection;

    if (selection.hasAttribute('linkHref')) {
      return selection;
    }

    return null;
  }
}
