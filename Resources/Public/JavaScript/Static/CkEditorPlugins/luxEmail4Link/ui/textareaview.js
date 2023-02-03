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
export default class TextareaView extends UI.View {
  /**
   * @inheritDoc
   */
  constructor( locale ) {
    super( locale );

    const bind = this.bindTemplate;

    /**
     * (Optional) The additional CSS class set on the button.
     *
     * @observable
     * @member {String} #class
     */
    this.set( 'class' );

    /**
     * Controls whether the checkbox view is enabled, i.e. it can be clicked and can execute an action.
     *
     * @observable
     * @default true
     * @member {Boolean} #isEnabled
     */
    this.set( 'isEnabled', true );

    /**
     * Controls whether the checkbox view is visible. Visible by default, the checkboxes are hidden
     * using a CSS class.
     *
     * @observable
     * @default true
     * @member {Boolean} #isVisible
     */
    this.set( 'isVisible', true );

    /**
     * The text of the label associated with the checkbox view.
     *
     * @observable
     * @member {String} #label
     */
    this.set( 'label' );

    /**
     * The HTML `id` attribute to be assigned to the checkbox.
     *
     * @observable
     * @default null
     * @member {String|null} #id
     */
    this.set( 'id', null );

    /**
     * (Optional) Controls the `tabindex` HTML attribute of the checkbox. By default, the checkbox is focusable
     * but is not included in the <kbd>Tab</kbd> order.
     *
     * @observable
     * @default -1
     * @member {String} #tabindex
     */
    this.set( 'tabindex', -1 );

    /**
     * The collection of the child views inside of the checkbox {@link #element}.
     *
     * @readonly
     * @member {module:ui/viewcollection~ViewCollection}
     */
    this.children = this.createCollection();

    /**
     * The label of the checkbox view. It is configurable using the {@link #label label attribute}.
     *
     * @readonly
     * @member {module:ui/view~View} #labelView
     */
    this.labelView = this._createLabelView( );

    /**
     * The input of the checkbox view.
     *
     * @readonly
     * @member {module:ui/view~View} #checkboxInputView
     */
    this.textareaView = this._createTextareaView();

    this.setTemplate( {
      tag: 'div',

      attributes: {
        class: [
          bind.to( 'class' ),
          bind.if( 'isEnabled', 'ck-disabled', value => !value ),
          bind.if( 'isVisible', 'ck-hidden', value => !value )
        ],
        tabindex: bind.to( 'tabindex' )
      },

      children: this.children
    } );
  }

  /**
   * @inheritDoc
   */
  render() {
    super.render();

    this.children.add( this.labelView );
    this.children.add( this.textareaView );
  }

  /**
   * Focuses the {@link #element} of the checkbox.
   */
  focus() {
    this.element.focus();
  }

  /**
   * Creates a checkbox input view instance and binds it with checkbox attributes.
   *
   * @private
   * @returns {module:ui/view~View}
   */
  _createTextareaView() {
    const textareaView = new UI.View();
    const bind = this.bindTemplate;

    textareaView.setTemplate( {
      tag: 'textarea',
      attributes: {
        class: 'ck ck-input',
        id: bind.to( 'id' ),
        'disabled': bind.if( 'isEnabled', true, value => !value ),
        'aria-disabled': bind.if( 'isEnabled', true, value => !value )
      },
    } );

    return textareaView;
  }

  /**
   * Creates a label view instance and binds it with checkbox attributes.
   *
   * @private
   * @returns {module:ui/view~View}
   */
  _createLabelView() {
    const labelView = new UI.View();

    labelView.setTemplate( {
      tag: 'label',

      attributes: {
        class: 'ck ck-label',
        for: this.bindTemplate.to( 'id' )
      },

      children: [
        {
          text: this.bindTemplate.to( 'label' )
        }
      ]
    } );

    return labelView;
  }
}
