<?php
/**
 * Fabrik Element Validator Model
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Fabrik\Helpers;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\String\String;
use Fabrik\Helpers\Worker;
use Fabrik\Helpers\ArrayHelper;
use \JEventDispatcher as JEventDispatcher;
use \JPluginHelper as JPluginHelper;
use Fabrik\Helpers\HTML as HelperHTML;


/**
 * Fabrik Element Validator Model
 * - Helper class for dealing with groups of attached validation rules.
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.5
 */
class Validator extends \JModelBase
{
	/**
	 * Validation objects associated with the element
	 *
	 * @var array
	 */
	protected $validations = null;

	/**
	 * Element model
	 *
	 * @var PlgFabrik_Element
	 */
	protected $elementModel = null;

	/**
	 * Icon image render options
	 *
	 * @var array
	 */
	protected $iconOpts = array('icon-class' => 'small');

	/**
	 * Set the element model - an instance of this class is linked to one element model
	 *
	 * @param   JModel  &$elementModel  Element model
	 *
	 * @return  void
	 */

	public function setElementModel(&$elementModel)
	{
		$this->elementModel = $elementModel;
	}

	/**
	 * Loads in elements published validation objects
	 *
	 * @return  array	validation objects
	 */

	public function findAll()
	{
		if (isset($this->validations))
		{
			return $this->validations;
		}

		$element = $this->elementModel->getElement();
		$params = $this->elementModel->getParams();
		$validations = (array) $params->get('validations', 'array');
		$usedPlugins = (array) ArrayHelper::getValue($validations, 'plugin', array());
		$published = ArrayHelper::getValue($validations, 'plugin_published', array());
		$showIcon = ArrayHelper::getValue($validations, 'show_icon', array());
		$validateIn = ArrayHelper::getValue($validations, 'validate_in', array());
		$validationOn = ArrayHelper::getValue($validations, 'validation_on', array());

		$pluginManager = Worker::getPluginManager();
		$pluginManager->getPlugInGroup('validationrule');
		$c = 0;
		$this->validations = array();
		$dispatcher = JEventDispatcher::getInstance();
		$ok = JPluginHelper::importPlugin('fabrik_validationrule');
		$i = 0;

		foreach ($usedPlugins as $usedPlugin)
		{
			if ($usedPlugin !== '')
			{
				$isPublished = ArrayHelper::getValue($published, $i, true);

				if ($isPublished)
				{
					$class = 'PlgFabrik_Validationrule' . String::ucfirst($usedPlugin);
					$conf = array();
					$conf['name'] = String::strtolower($usedPlugin);
					$conf['type'] = String::strtolower('fabrik_Validationrule');
					$plugIn = new $class($dispatcher, $conf);
					$oPlugin = JPluginHelper::getPlugin('fabrik_validationrule', $usedPlugin);
					$plugIn->elementModel = $this->elementModel;
					$this->validations[] = $plugIn;

					// Set params relative to plugin render order
					$plugIn->setParams($params, $i);

					$plugIn->getParams()->set('show_icon', ArrayHelper::getValue($showIcon, $i, true));
					$plugIn->getParams()->set('validate_in', ArrayHelper::getValue($validateIn, $i, 'both'));
					$plugIn->getParams()->set('validation_on', ArrayHelper::getValue($validationOn, $i, 'both'));

					$c++;
				}
			}

			$i ++;
		}

		return $this->validations;
	}

	/**
	 * Should the icon be shown
	 *
	 * @return boolean
	 */

	private function showIcon()
	{
		$validations = $this->findAll();

		foreach ($validations as $v)
		{
			if ($v->getParams()->get('show_icon'))
			{
				return true;
			}
		}

		$internal = $this->elementModel->internalValidationIcon();

		if ($internal !== '')
		{
			return true;
		}

		return false;
	}

	/**
	 * Get the icon
	 * - If showIcon() false - show question-sign for hover tip txt indicator
	 * - If one validation - use the icon specified in the J fabrik_validation settings (default to star)
	 * - If more than one return default j2.5/j3 img
	 *
	 * @param   int  $c  Validation plugin render order
	 *
	 * @return string
	 */

	public function getIcon($c = null)
	{
		$validations = $this->findAll();

		if (!$this->showIcon())
		{
			return '';
		}

		if (!empty($validations))
		{
			if (is_null($c))
			{
				return $validations[0]->iconImage();
			}
			else
			{
				return $validations[$c]->iconImage();
			}
		}

		$internal = $this->elementModel->internalValidationIcon();

		if ($internal !== '')
		{
			return $internal;
		}

		return 'star.png';
	}

	/**
	 * Get the array data use to set up the javascript watch element
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return multitype:stdClass
	 */
	public function jsWatchElements($repeatCounter = 0)
	{
		$validationEls = array();
		$validations = $this->findAll();

		if (!empty($validations) && $this->elementModel->isEditable())
		{
			$watchElements = $this->elementModel->getValidationWatchElements($repeatCounter);

			foreach ($watchElements as $watchElement)
			{
				$o = new stdClass;
				$o->id = $watchElement['id'];
				$o->triggerEvent = $watchElement['triggerEvent'];
				$validationEls[] = $o;
			}
		}

		return $validationEls;
	}

	/**
	 * Get the main validation icon to show next to the element's label
	 *
	 * @return string
	 */

	public function labelIcons()
	{
		$tmpl = $this->elementModel->getFormModel()->getTmpl();
		$validations = array_unique($this->findAll());
		$emptyIcon = $this->getIcon();
		$icon = empty($emptyIcon) && empty($validations) ? "" : HelperHTML::image($emptyIcon, 'form', $tmpl, $this->iconOpts) . ' ';
		
		return $icon;
	}

	/**
	 * Does the element have validations - checks assigned and internal validations
	 *
	 * @return boolean
	 */
	public function hasValidations()
	{
		$validations = $this->findAll();

		if (!empty($validations) || $this->elementModel->internalValidataionText() !== '')
		{
			return true;
		}

		return false;
	}

	/**
	 * Create hover tip text for validations
	 *
	 * @return  array  Messages
	 */
	public function hoverTexts()
	{
		$texts = array();

		if ($this->elementModel->isEditable())
		{
			$tmpl = $this->elementModel->getFormModel()->getTmpl();
			$validations = array_unique($this->findAll());

			foreach ($validations as $c => $validation)
			{
				$texts[] = $validation->getHoverText($c, $tmpl);
			}

			$internal = $this->elementModel->internalValidataionText();

			if ($internal !== '')
			{
				$i = $this->elementModel->internalValidationIcon();
				$icon = HelperHTML::image($i, 'form', $tmpl, $this->iconOpts);
				$texts[] = $icon . ' ' . $internal;
			}
		}

		return $texts;
	}
}