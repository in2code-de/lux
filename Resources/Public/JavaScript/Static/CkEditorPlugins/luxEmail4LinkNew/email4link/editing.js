import {Core} from "@typo3/ckeditor5-bundle.js";

export default class Email4LinkEditing extends Core.Plugin {
  init() {
    this._defineConverters();
  }

  _defineConverters() {
    const conversion = this.editor.conversion;

    // Conversion from a model attribute to a view element
    conversion.for('downcast').attributeToElement( {
      model: 'email4link',

      view: (modelAttributeValue, conversionApi) => {
        const {writer} = conversionApi;
        return writer.createAttributeElement('abbr', {
          title: modelAttributeValue
        } );
      }
    } );

    // Conversion from a view element to a model attribute
    conversion.for('upcast').elementToAttribute( {
      view: {
        name: 'abbr',
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
