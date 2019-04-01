<?php
/**
 * Fabrik Element Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Fabrik\Component\Fabrik\Site\Controller;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Component\Fabrik\Site\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * Fabrik Element Controller
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       4.0
 */
class ElementController extends AbstractSiteController
{
	/**
	 * Is the view rendered from the J content plugin
	 *
	 * @var  bool
	 *
	 * @since 4.0
	 */
	public $isMambot = false;

	/**
	 * Should the element be rendered as readonly
	 *
	 * @var  string
	 *
	 * @since 4.0
	 */
	public $mode = false;

	/**
	 * Id used from content plugin when caching turned on to ensure correct element rendered
	 *
	 * @var  int
	 *
	 * @since 4.0
	 */
	public $cacheId = 0;

	/**
	 * Display the view
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function display()
	{
		$app      = $this->app;
		$document = $app->getDocument();

		$input    = $app->input;
		$viewName = $input->get('view', 'element', 'cmd');
		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		// $$$ rob 04/06/2011 don't assign a model to the element as its only a plugin

		$view->editable = ($this->mode == 'readonly') ? false : true;

		// Display the view
		$view->error = $this->getError();

		return $view->display();
	}

	/**
	 * Save an individual element value to the fabrik db
	 * used in inline edit table plugin
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function save()
	{
		$app       = Factory::getApplication();
		$input     = $app->input;
		$listModel = $this->getModel(ListModel::class);
		$listModel->setId($input->getInt('listid'));
		$rowId = $input->get('rowid');
		$key   = $input->get('element');
		$key   = array_pop(explode('___', $key));
		$value = $input->get('value');
		$listModel->storeCell($rowId, $key, $value);
		$this->mode = 'readonly';
		$this->display();
	}
}