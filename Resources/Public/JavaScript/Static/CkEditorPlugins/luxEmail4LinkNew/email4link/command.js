import {Core, Typing} from "@typo3/ckeditor5-bundle.js";

export default class Email4LinkCommand extends Core.Command {
  refresh() {
    const model = this.editor.model;
    const selection = model.document.selection;

    this.isEnabled = this._getSelectedLink() !== null;
  }

  execute({ title, text, sendEmail }) {
    this.editor.model.change( writer => {
      const link = this._getSelectedLink();

      if (link) {
        const linkRange = Typing.findAttributeRange(link.getFirstPosition(), 'linkHref', link.getAttribute('linkHref'), editor.model);

        writer.setAttribute('sendEmail', String(sendEmail), linkRange)
        writer.setAttribute('emailTitle', String(title), linkRange)
        writer.setAttribute('emailText', String(text), linkRange)
      }
    });
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
