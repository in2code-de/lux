import {Core} from "@typo3/ckeditor5-bundle.js";

export default class Email4LinkEditing extends Core.Plugin {
  init() {
    this._defineConverters();
  }

  _defineConverters() {
    const conversion = this.editor.conversion;

    this.editor.model.schema.extend( '$text', { allowAttributes: 'data-test' } );

    conversion.for('downcast').attributeToAttribute({
      model: {
        key: 'data-test',
        name: 'linkHref'
      },
      view: 'data-test'
    })

    // Conversion from a view element to a model attribute
    conversion.for('upcast').elementToAttribute( {
      view: {
        name: 'a',
        attributes: ['title']
      },
      model: {
        key: 'email4link',

        // Callback function provides access to the view element
        value: viewElement => {
          const title = viewElement.getAttribute('title');
          return title;
        }
      }
    } );
  }
}
