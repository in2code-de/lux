import {Core, Utils} from "@typo3/ckeditor5-bundle.js";
import Email4LinkEditing from './email4link/editing.js';
import Email4LinkUI from "./email4link/ui.js";

window.CKEDITOR_TRANSLATIONS = window.CKEDITOR_TRANSLATIONS || {};
window.CKEDITOR_TRANSLATIONS['en'] = window.CKEDITOR_TRANSLATIONS['en'] || {};
window.CKEDITOR_TRANSLATIONS['en'].dictionary = window.CKEDITOR_TRANSLATIONS['en'].dictionary || {};
window.CKEDITOR_TRANSLATIONS['de'] = window.CKEDITOR_TRANSLATIONS['de'] || {};
window.CKEDITOR_TRANSLATIONS['de'].dictionary = window.CKEDITOR_TRANSLATIONS['de'].dictionary || {};

Object.assign(window.CKEDITOR_TRANSLATIONS['en'].dictionary, {
  'Title': 'Ask visitor for email address to get the link',
  'LabelTitle': 'Title',
  'LabelText': 'Additional information text for visitors',
  'LabelEmail': 'Send document as email (only for filelinks)',
  'LabelSave': 'save',
  'LabelCancel': 'cancel',
  'TextExplanation': 'Note: With this feature "email4link" you increase the identification rate of your leads.',
});
Object.assign(window.CKEDITOR_TRANSLATIONS['de'].dictionary, {
  'Title': 'Besucher um E-Mail-Adresse für Link bitten',
  'LabelTitle': 'Überschrift',
  'LabelText': 'Ergänzender Informationstext für Besucher',
  'LabelEmail': 'Dokument per E-Mail versenden (nur Links auf Dateien)',
  'LabelSave': 'speichern',
  'LabelCancel': 'abbrechen',
  'TextExplanation': 'Hinweis: Mit diesem Feature "email4link" erhöhen Sie die Identifikationsrate Ihrer Leads.',
});

export default class Email4Link extends Core.Plugin {
  static get requires() {
    return [ Email4LinkEditing, Email4LinkUI ];
  }
}
Email4Link.pluginName = 'Email4Link';
