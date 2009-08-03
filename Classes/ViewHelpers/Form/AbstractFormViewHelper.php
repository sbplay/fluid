<?php

/*                                                                        *
 * This script belongs to the FLOW3 package "Fluid".                      *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU Lesser General Public License as published by the *
 * Free Software Foundation, either version 3 of the License, or (at your *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser       *
 * General Public License for more details.                               *
 *                                                                        *
 * You should have received a copy of the GNU Lesser General Public       *
 * License along with the script.                                         *
 * If not, see http://www.gnu.org/licenses/lgpl.html                      *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * Abstract Form View Helper. Bundles functionality related to direct property access of objects in other Form ViewHelpers.
 *
 * If you set the "property" attribute to the name of the property to resolve from the object, this class will
 * automatically set the name and value of a form element.
 *
 * @version $Id: AbstractFormViewHelper.php 2914 2009-07-28 18:26:38Z bwaidelich $
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 * @scope prototype
 */
abstract class Tx_Fluid_ViewHelpers_Form_AbstractFormViewHelper extends Tx_Fluid_Core_ViewHelper_TagBasedViewHelper {

	/**
	 * @var \Tx_Extbase_Persistence_ManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * Injects the FLOW3 Persistence Manager
	 *
	 * @param Tx_Extbase_Persistence_ManagerInterface $persistenceManager
	 * @return void
	 * @author Robert Lemke <robert@typo3.org>
	 */
	public function injectPersistenceManager(Tx_Extbase_Persistence_ManagerInterface $persistenceManager) {
		$this->persistenceManager = $persistenceManager;
	}

	/**
	 * Initialize arguments.
	 *
	 * @return void
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @api
	 */
	public function initializeArguments() {
		parent::initializeArguments();
		$this->registerArgument('name', 'string', 'Name of input tag');
		$this->registerArgument('value', 'mixed', 'Value of input tag');
		$this->registerArgument('property', 'string', 'Name of Object Property. If used in conjunction with <f:form object="...">, "name" and "value" properties will be ignored.');
	}

	/**
	 * Get the name of this form element.
	 * Either returns arguments['name'], or the correct name for Object Access.
	 *
	 * @return string Name
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Karsten Dambekalns <karsten@typo3.org>
	 */
	protected function getName() {
		$name = ($this->isObjectAccessorMode()) ? $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formName') . '[' . $this->arguments['property'] . ']' : $this->arguments['name'];
		if (is_object($this->arguments['value']) && NULL !== $this->persistenceManager->getBackend()->getIdentifierByObject($this->arguments['value'])
				&& (!$this->persistenceManager->getBackend()->isNewObject($this->arguments['value']))) {
			$name .= '[uid]';
		}
		return $name;
	}

	/**
	 * Get the value of this form element.
	 * Either returns arguments['value'], or the correct value for Object Access.
	 *
	 * @return mixed Value
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 * @author Robert Lemke <robert@typo3.org>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function getValue() {
		$value = NULL;
		if ($this->arguments->hasArgument('value')) {
			$value = $this->arguments['value'];
		} elseif ($this->isObjectAccessorMode() && $this->viewHelperVariableContainer->exists('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObject')) {
			$value = $this->getPropertyValue();
		}
		if (is_object($value)) {
			$identifier = $this->persistenceManager->getBackend()->getIdentifierByObject($value);
			if ($identifier !== NULL) {
				$value = $identifier;
			}
		}
		return $value;
	}

	/**
	 * Get the current property of the object bound to this form.
	 *
	 * @return mixed Value
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function getPropertyValue() {
		$formObject = $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formObject');
		$propertyName = $this->arguments['property'];
		if (is_array($formObject)) {
			return isset($formObject[$propertyName]) ? $formObject[$propertyName] : NULL;
		}
		return Tx_Extbase_Reflection_ObjectAccess::getProperty($formObject, $propertyName);
	}

	/**
	 * Internal method which checks if we should evaluate a domain object or just output arguments['name'] and arguments['value']
	 *
	 * @return boolean TRUE if we should evaluate the domain object, FALSE otherwise.
	 * @author Sebastian Kurfürst <sebastian@typo3.org>
	 */
	protected function isObjectAccessorMode() {
		return (($this->arguments['property'] !== NULL) && $this->viewHelperVariableContainer->exists('Tx_Fluid_ViewHelpers_FormViewHelper', 'formName')) ? TRUE : FALSE;
	}


	/**
	 * Add an CSS class if this view helper has errors
	 *
	 * @return void
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function setErrorClassAttribute() {
		if ($this->arguments->hasArgument('class')) {
			$cssClass = $this->arguments['class'] . ' ';
		} else {
			$cssClass = '';
		}
		$errors = $this->getErrorsForProperty();
		if (count($errors) > 0) {
			if ($this->arguments->hasArgument('errorClass')) {
				$cssClass .= $this->arguments['errorClass'];
			} else {
				$cssClass .= 'f3-form-error';
			}
			$this->tag->addAttribute('class', $cssClass);
		}
	}

	/**
	 * Get errors for the property and form name of this view helper
	 *
	 * @return array An array of Tx_Fluid_Error_Error objects
	 * @author Christopher Hlubek <hlubek@networkteam.com>
	 * @author Bastian Waidelich <bastian@typo3.org>
	 */
	protected function getErrorsForProperty() {
		if (!$this->arguments->hasArgument('property')) {
			return array();
		}
		$errors = $this->controllerContext->getRequest()->getErrors();
		$formName = $this->viewHelperVariableContainer->get('Tx_Fluid_ViewHelpers_FormViewHelper', 'formName');
		$propertyName = $this->arguments['property'];
		$formErrors = array();
		foreach ($errors as $error) {
			if ($error instanceof Tx_Extbase_Validation_PropertyError && $error->getPropertyName() === $formName) {
				$formErrors = $error->getErrors();
				foreach ($formErrors as $formError) {
					if ($formError instanceof Tx_Extbase_Validation_PropertyError && $formError->getPropertyName() === $propertyName) {
						return $formError->getErrors();
					}
				}
			}
		}
		return array();
	}
}

?>