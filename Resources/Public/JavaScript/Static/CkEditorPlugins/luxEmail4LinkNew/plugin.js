import {Core, Utils} from "@typo3/ckeditor5-bundle.js";
import Email4LinkEditing from './email4link/editing.js';
import Email4LinkUI from "./email4link/ui.js";

/*
Utils.add('en', {
  'Title': 'Title',
  'Description': 'Description',
});
Utils.add('de', {
  'Title': 'Überschrift',
  'Description': 'Beschreibung',
});
*/


export default class Email4Link extends Core.Plugin {
  static get requires() {
    return [ Email4LinkEditing, Email4LinkUI ];
  }
}
Email4Link.pluginName = 'Email4Link';
