/**
 * @license Copyright (c) 2003-2021, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or https://ckeditor.com/legal/ckeditor-oss-license
 */

/**
 * @module find-and-replace/ui/checkboxview
 */

import { UI } from "@typo3/ckeditor5-bundle.js";

/**
 * The checkbox view class.
 *
 * @extends module:ui/view~View
 */
export default class TextareaView extends UI.InputView {
  constructor(locale) {
    super(locale);
    this.template.tag = 'textarea';
    this.extendTemplate({
      attributes: {
        class: [
          'ck-input-textarea'
        ]
      }
    });
  }
}
