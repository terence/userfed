<?php
/**
 * Twitter Bootstrap Form Row View Helper
 */
namespace AppCore\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormRow;

class TbFormRow extends FormRow
{
	/**
	 * Utility form helper that renders a label (if it exists), an element and errors
	 *
	 * @param ElementInterface $element
	 * @return string
	 * @throws \Zend\Form\Exception\DomainException
	 */
	public function render(ElementInterface $element)
	{
		/**
		 * Test netbean tab/space issue
		 */
		/**
		 * Test netbean tab/space issue
		 */
		$escapeHtmlHelper    = $this->getEscapeHtmlHelper();
		$labelHelper         = $this->getLabelHelper();
		$elementHelper       = $this->getElementHelper();
		$elementErrorsHelper = $this->getElementErrorsHelper();
		$elementErrorsHelper->setMessageCloseString('');
		$elementErrorsHelper->setMessageOpenFormat('');
		$elementErrorsHelper->setMessageSeparatorString('');
	
		$label           = $element->getLabel();
		$inputErrorClass = $this->getInputErrorClass();
		$elementErrors   = $elementErrorsHelper->render($element);
	
		// Does this element have errors ?
		if (!empty($elementErrors) && !empty($inputErrorClass)) {
			$classAttributes = ($element->hasAttribute('class') ? $element->getAttribute('class') . ' ' : '');
			$classAttributes = $classAttributes . $inputErrorClass;
	
			$element->setAttribute('class', $classAttributes);
		}
		
		$attrClass = $element->getAttribute('class');
		if (strpos($attrClass, 'form-control') === false) {
			$attrClass .= ' form-control';
			$element->setAttribute('class', $attrClass);
		}
	
		$elementString = $elementHelper->render($element);

// 		$requiredIcon = "";
// 		if ($element->getAttribute('required')) {
// 			$requiredIcon = ' <i class="icon-asterisk" style="color:red;"></i>';
// 		}
// 		} else {
// 			$requiredIcon = ' <i class="icon-asterisk" style="color:white;visibility:none;"></i>';
// 		}
		
		if (isset($label) && '' !== $label) {
			// Translate the label
			if (null !== ($translator = $this->getTranslator())) {
				$label = $translator->translate(
						$label, $this->getTranslatorTextDomain()
				);
			}
	
			$label = $escapeHtmlHelper($label);
			$labelAttributes = $element->getLabelAttributes();
	
			if (empty($labelAttributes)) {
				$labelAttributes = $this->labelAttributes;
			}
	
			// Multicheckbox elements have to be handled differently as the HTML standard does not allow nested
			// labels. The semantic way is to group them inside a fieldset
			$type = $element->getAttribute('type');
			if ($type === 'multi_checkbox' || $type === 'radio') {
				$markup = sprintf(
						'<fieldset><legend>%s</legend>%s</fieldset>',
						$label,
						$elementString);
			} else {
				if ($element->hasAttribute('id')) {
					$labelOpen = $labelHelper($element);
					$labelClose = '';
					$label = '';
				} else {
					$labelOpen  = $labelHelper->openTag($labelAttributes);
					$labelClose = $labelHelper->closeTag();
				}

				switch ($this->labelPosition) {
					case self::LABEL_PREPEND:
					case self::LABEL_APPEND:
					default:
						$markup = $this->twitterBootstrapFormElement($element, $elementString, $elementErrors, $label);
						break;
				}
			}
		} else {
		/**
		 * test tab/space issue
		 */	
			if ($this->renderErrors) {
// 				$markup = $this->twitterBootstrapFormElement($element, $elementString, $elementErrors, null, $requiredIcon);
				$markup = $this->twitterBootstrapFormElement($element, $elementString, $elementErrors, null);
			} else {
// 				$markup = $this->twitterBootstrapFormElement($element, $elementString, null, null, $requiredIcon);
				$markup = $this->twitterBootstrapFormElement($element, $elementString);
			}
		}
	
		return $markup;
	}
	
	/**
	 * Wrap element with twitter bootstrap html
	 * 
	 * @param string $element
	 * @param string $elementString
	 * @param string $elementErrors
	 * @param string $label
	 * @param string $requiredIcon
	 * 
	 * @return string
	 */
	// Test netBean tab issue
	protected function twitterBootstrapFormElement($element, $elementString, $elementErrors = null, $label = null, $requiredIcon = null)
	{
		/**
		 * Test netbean tab/space issue
		 */
		$requiredIcon = "";
		if ($element->getAttribute('required')) {
			$requiredIcon = ' fa fa-asterisk';
		}
		
		$errorClass = "";
		if ($this->renderErrors && !empty($elementErrors)) {
			$errorClass = "error";
		}
		$markup =
			"<div class='form-group has-feedback $errorClass'>";
		
		if (!empty($label)) {
			$markup .= '<p class="control-label" for="' . $element->getAttribute('name') . '">' . $label . '</p>';
			$markup .= $elementString;
			$markup .= '<span style="color:red;" class="' . $requiredIcon .' form-control-feedback"></span>' ;
		} else {
			$markup .=	$elementString . $requiredIcon;
		}
		
		
// 		if ($label && '' !== $label) {
// 			$markup .= '<label class="control-label" for="' . $element->getAttribute('name') . '">' . $label . $requiredIcon . '</label>';
// 			$markup .=	'<div class="controls">' . $elementString;
// 		} else {
// 			//$markup .= '<label class="control-label" for="' . $element->getAttribute('name') . '"></label>';
// 			$markup .=	'<div class="controls">' . $elementString . $requiredIcon;
// 		}
		
		$helpText = "";
		
		$options = $element->getOptions();
		if (array_key_exists('description', $options) && !empty($options['description'])) {
			$helpText .= $options['description'];
		}
		
		if ($this->renderErrors && !empty($elementErrors)) {
			$helpText .= $elementErrors;
		}
		
		if (!empty($helpText)) {
			$markup .= "<span class='help-inline'>$helpText</span>";
		}
		
		$markup .= '</div>';
		return $markup;
	}
}