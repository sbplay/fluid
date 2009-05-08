<?php

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

/**
 * 
 * @todo documentation
 * 
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 */

/**
 *
 * @package
 * @subpackage
 * @version $Id$
 */
class Tx_Fluid_ViewHelpers_ImageViewHelper extends Tx_Fluid_Core_TagBasedViewHelper {

	/**
	 * @var	tslib_cObj
	 */
	protected $contentObject;

	/**
	 * @var string
	 */
	protected $tagName = 'img';

	/**
	 * Constructor. Used to create an instance of tslib_cObj used by the render() method.
	 * 
	 * @param tslib_cObj $contentObject injector for tslib_cObj (optional)
	 * @return void
	 */
	public function __construct($contentObject = NULL) {
		$this->contentObject = $contentObject !== NULL ? $contentObject : $GLOBALS['TSFE']->cObj;
	}

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerUniversalTagAttributes();
		$this->registerTagAttribute('alt', 'string', 'Specifies an alternate text for an image', TRUE);
		$this->registerTagAttribute('ismap', 'string', 'Specifies an image as a server-side image-map. Rarely used. Look at usemap instead', FALSE);
		$this->registerTagAttribute('longdesc', 'string', 'Specifies the URL to a document that contains a long description of an image', FALSE);
		$this->registerTagAttribute('usemap', 'string', 'Specifies an image as a client-side image-map', FALSE);
	}

	/**
	 * Render the img tag.
	 * @see http://typo3.org/documentation/document-library/references/doc_core_tsref/4.2.0/view/1/5/#id4164427
	 * 
	 * @param string $src
	 * @param string $width width of the image. See imgResource.width for possible values.
	 * @param string $height height of the image. See imgResource.height for possible values.
	 * @param integer $minWidth minimum width of the image
	 * @param integer $minHeight minimum height of the image
	 * @param integer $maxWidth maximum width of the image
	 * @param integer $maxHeight maximum height of the image
	 *
	 * @return string rendered tag.
	 * @author Sebastian Böttger <sboettger@cross-content.com>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render($src, $width = NULL, $height = NULL, $minWidth = NULL, $minHeight = NULL, $maxWidth = NULL, $maxHeight = NULL) {
		$setup = array(
			'width' => $width,
			'height' => $height,
			'minW' => $minWidth,
			'minH' => $minHeight,
			'maxW' => $maxWidth,
			'maxH' => $maxHeight
		);
		$imageInfo = $this->contentObject->getImgResource($src, $setup);
		$GLOBALS['TSFE']->lastImageInfo = $imageInfo;
		if (!is_array($imageInfo)) {
			// @todo throw exception
			return '';
		}
		$imageInfo[3] = t3lib_div::png_to_gif_by_imagemagick($imageInfo[3]);
		$GLOBALS['TSFE']->imagesOnPage[] = $imageInfo[3];

		$imageSource = $GLOBALS['TSFE']->absRefPrefix . t3lib_div::rawUrlEncodeFP($imageInfo[3]);
		$this->tag->addAttribute('src', $imageSource);
		$this->tag->addAttribute('width', $imageInfo[0]);
		$this->tag->addAttribute('height', $imageInfo[1]);
		if ($this->arguments['title'] === '') {
			$this->tag->addAttribute('title', $this->arguments['alt']);
		}

		return $this->tag->render();
	}
}