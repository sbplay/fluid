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
 * A view helper for creating URIs to TYPO3 pages.
 *
 * = Examples =
 * 
 * <code title="URI to the current page">
 * <f:uri.page>page link</f:uri.page>
 * </code>
 *
 * Output:
 * index.php?id=123
 * (depending on the current page and your TS configuration)
 * 
 * <code title="query parameters">
 * <f:uri.page pageUid="1" additionalParams="{foo: 'bar'}" />
 * </code>
 *
 * Output:
 * index.php?id=1&foo=bar
 * (depending on your TS configuration)
 *
 * @package Fluid
 * @subpackage ViewHelpers
 * @version $Id$
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
class Tx_Fluid_ViewHelpers_Uri_PageViewHelper extends Tx_Fluid_Core_AbstractViewHelper {

	/**
	 * @param integer $page target PID
	 * @param array $additionalParams query parameters to be attached to the resulting URI
	 * @param integer $pageType type of the target page. See typolink.parameter
	 * @param boolean $noCache set this to disable caching for the target page. You should not need this.
	 * @param boolean $noCacheHash set this to supress the cHash query parameter created by TypoLink. You should not need this.
	 * @param string $section the anchor to be added to the URI
	 * @param boolean $linkAccessRestrictedPages If set, links pointing to access restricted pages will still link to the page even though the page cannot be accessed.
	 * @return string Rendered page URI
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	public function render($pageUid = NULL, array $additionalParams = array(), $pageType = 0, $noCache = FALSE, $noCacheHash = FALSE, $section = '', $linkAccessRestrictedPages = FALSE) {
		if ($pageUid === NULL) {
			$pageUid = $GLOBALS['TSFE']->id;
		}
		$uriHelper = $this->variableContainer->get('view')->getViewHelper('Tx_Extbase_MVC_View_Helper_URIHelper');
		$uri = $uriHelper->typolinkURI($pageUid, $additionalParams, $pageType, $noCache, $noCacheHash, $section, $linkAccessRestrictedPages);
		return $uri;
	}
}


?>