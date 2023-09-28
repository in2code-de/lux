import {Core} from "@typo3/ckeditor5-bundle.js";
import Email4LinkCommand from "./command.js";

export default class Email4LinkEditing extends Core.Plugin {
  init() {
    this._defineConverters();

    this.editor.commands.add(
      'email4link', new Email4LinkCommand(this.editor)
    );
  }

  _defineConverters() {
    const conversion = this.editor.conversion;

    conversion.for('downcast').attributeToElement({
      model: 'sendEmail',
      view: (value, { writer }) => {
        const linkElement = writer.createAttributeElement('a', { 'data-lux-email4link-sendemail': value }, { priority: 5 });
        writer.setCustomProperty('sendEmail', true, linkElement);
        return linkElement;
      }
    });
    editor.conversion.for('upcast').elementToAttribute({
      view: { name: 'a', attributes: { 'data-lux-email4link-sendemail': true } },
      model: { key: 'sendEmail', value: (viewElement) => viewElement.getAttribute('data-lux-email4link-sendemail') }
    });

    conversion.for('downcast').attributeToElement({
      model: 'emailTitle',
      view: (value, { writer }) => {
        const linkElement = writer.createAttributeElement('a', { 'data-lux-email4link-title': value }, { priority: 5 });
        writer.setCustomProperty('emailTitle', true, linkElement);
        return linkElement;
      }
    });
    editor.conversion.for('upcast').elementToAttribute({
      view: { name: 'a', attributes: { 'data-lux-email4link-title': true } },
      model: { key: 'emailTitle', value: (viewElement) => viewElement.getAttribute('data-lux-email4link-title') }
    });

    conversion.for('downcast').attributeToElement({
      model: 'emailText',
      view: (value, { writer }) => {
        const linkElement = writer.createAttributeElement('a', { 'data-lux-email4link-text': value }, { priority: 5 });
        writer.setCustomProperty('emailText', true, linkElement);
        return linkElement;
      }
    });
    editor.conversion.for('upcast').elementToAttribute({
      view: { name: 'a', attributes: { 'data-lux-email4link-text': true } },
      model: { key: 'emailText', value: (viewElement) => viewElement.getAttribute('data-lux-email4link-text') }
    });
  }
}
