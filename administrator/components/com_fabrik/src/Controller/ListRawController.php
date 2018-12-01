<?php
/**
 * Raw List controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       1.6
 */

namespace Joomla\Component\Fabrik\Administrator\Controller;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Component\Fabrik\Administrator\Model\FabModel;
use Joomla\Component\Fabrik\Site\Model\ConnectionModel;
use Joomla\Component\Fabrik\Site\Model\ListModel;
use \Joomla\Registry\Registry;
use Fabrik\Helpers\StringHelper as FStringHelper;

/**
 * Raw List controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       4.0
 */
class ListRawController extends AbstractFormController
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 *
	 * @since 4.0
	 */
	protected $text_prefix = 'COM_FABRIK_LIST';

	/**
	 * @var string
	 *
	 * @since since 4.0
	 */
	protected $context = 'list';

	/**
	 * Ajax load drop down of all columns in a given table
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function ajax_loadTableDropDown()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$conn  = $input->getInt('conn', 1);
		/** @var ConnectionModel $oCnn */
		$oCnn = FabModel::getInstance(ConnectionModel::class);
		$oCnn->setId($conn);
		$oCnn->getConnection();
		$db         = $oCnn->getDb();
		$table      = $input->get('table', '');
		$fieldNames = array();
		$name       = $input->get('name', 'jform[params][table_key][]', '', 'string');

		if ($table != '')
		{
			$table = FStringHelper::safeColName($table);
			$sql   = 'DESCRIBE ' . $table;
			$db->setQuery($sql);
			$aFields = $db->loadObjectList();

			if (is_array($aFields))
			{
				foreach ($aFields as $oField)
				{
					$fieldNames[] = HTMLHelper::_('select.option', $oField->Field);
				}
			}
		}

		$fieldDropDown = HTMLHelper::_('select.genericlist', $fieldNames, $name, "class=\"inputbox\"  size=\"1\" ", 'value', 'text', '');
		echo $fieldDropDown;
	}

	/**
	 * Delete list items
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function delete()
	{
		// Check for request forgeries
		Session::checkToken() or die('Invalid Token');
		$app   = Factory::getApplication();
		$input = $app->input;
		/** @var ListModel $model */
		$model  = FabModel::getInstance(ListModel::class);
		$listId = $input->getInt('listid');
		$model->setId($listId);
		$ids        = $input->get('ids', array(), 'array');
		$limitStart = $input->getInt('limitstart' . $listId);
		$length     = $input->getInt('limit' . $listId);
		$oldTotal   = $model->getTotalRecords();
		$model->deleteRows($ids);
		$total = $oldTotal - count($ids);

		if ($total >= $limitStart)
		{
			$newLimitStart = $limitStart - $length;

			if ($newLimitStart < 0)
			{
				$newLimitStart = 0;
			}

			$context = 'com_fabrik.list' . $model->getRenderContext() . '.list.';
			$app->setUserState($context . 'limitstart' . $listId, $newLimitStart);
		}

		$input->set('view', 'list');
		$this->view();
	}

	/**
	 * Show the lists data in the admin
	 *
	 * @param   object $model list model
	 *
	 * @return  void
	 *
	 * @since 4.0
	 */
	public function view($model = null)
	{
		$app     = Factory::getApplication();
		$input   = $app->input;
		$cid     = $input->get('cid', array(0), 'array');
		$cid     = $cid[0];
		$cid     = $input->getInt('listid', $cid);
		$listRef = $input->getString('listref');

		if (is_null($model))
		{
			$cid = $app->input->getInt('listid', $cid);

			// Grab the model and set its id
			/** @var ListModel $model */
			$model = FabModel::getInstance(ListModel::class);
			$model->setState('list.id', $cid);
		}

		if (strstr($listRef, 'mod_'))
		{
			$bits     = explode('_', $listRef);
			$moduleId = array_pop($bits);
			$this->bootFromModule($moduleId, $model);
		}

		$viewType = Factory::getDocument()->getType();

		// Use the front end renderer to show the table
		// @todo refactor to j4
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout = $input->get('layout', 'default');
		$view       = $this->getView($this->view_item, $viewType, 'FabrikView');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);
		$view->display();
	}

	/**
	 * Load up module prefilters etc
	 *
	 * @param   int       $moduleId Module id
	 * @param   ListModel $model    List model
	 *
	 * @return  void
	 *
	 * @since 4.0
	 */
	private function bootFromModule($moduleId, ListModel $model)
	{
		// @todo update after modules have been updated to namespaces
		require_once JPATH_ADMINISTRATOR . '/modules/mod_fabrik_list/helper.php';

		// Load module parameters
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('params')->from('#__modules')->where('id = ' . (int) $moduleId);
		$db->setQuery($query);
		$params = $db->loadResult();
		$params = new Registry($params);

		\ModFabrikListHelper::applyParams($params, $model);
		$model->setRenderContext($moduleId);
	}

	/**
	 * Order the lists
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function order()
	{
		// Check for request forgeries
		Session::checkToken() or die('Invalid Token');
		$app   = Factory::getApplication();
		$input = $app->input;
		$model = FabModel::getInstance(ListModel::class);
		$id    = $input->getInt('listid');
		$model->setId($id);
		$input->set('cid', $id);
		$model->setOrderByAndDir();

		// $$$ hugh - unset 'resetfilters' in case it was set on QS of original table load.
		$input->set('resetfilters', 0);
		$input->set('clearfilters', 0);
		$this->view();
	}

	/**
	 * Clear filters
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function clearfilter()
	{
		$app = Factory::getApplication();
		$app->enqueueMessage(Text::_('COM_FABRIK_FILTERS_CLEARED'));
		$app->input->set('clearfilters', 1);
		$this->filter();
	}

	/**
	 * Filter list items
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function filter()
	{
		// Check for request forgeries
		Session::checkToken() or die('Invalid Token');
		$app   = Factory::getApplication();
		$model = FabModel::getInstance(ListModel::class);
		$id    = $app->input->getInt('listid');
		$model->setId($id);
		$app->input->set('cid', $id);
		$request = $model->getRequestData();
		$model->storeRequestData($request);

		// Pass in the model otherwise display() rebuilds it and the request data is rebuilt
		$this->view($model);
	}

	/**
	 * Called via ajax when element selected in advanced search popup window
	 * OR in update_col plugin
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function elementFilter()
	{
		$app   = Factory::getApplication();
		$input = $app->input;
		$id    = $input->getInt('id');
		$model = $this->getModel(ListModel::class);
		$model->setId($id);
		echo $model->getAdvancedElementFilter();
	}

	/**
	 * Run a list plugin
	 *
	 * @return  null
	 *
	 * @since 4.0
	 */
	public function doPlugin()
	{
		$app   = Factory::getApplication();
		$input = $this->input;
		$cid   = $input->get('cid', array(0), 'array');
		$cid   = $cid[0];
		$model = $this->getModel(ListModel::class);
		$model->setId($input->getInt('listid', $cid));

		// $$$ rob need to ask the model to get its data here as if the plugin calls $model->getData
		// then the other plugins are recalled which makes the current plugins params incorrect.
		$model->setLimits();
		$model->getData();

		// If showing n tables in article page then ensure that only activated table runs its plugin
		if ($input->getInt('id') == $model->get('id') || $input->get('origid', '', 'string') == '')
		{
			$messages = $model->processPlugin();

			if ($input->get('format') == 'raw')
			{
				$input->set('view', 'list');
			}
			else
			{
				foreach ($messages as $msg)
				{
					$this->app->enqueueMessage($msg);
				}
			}
		}

		$format = $input->get('format', 'html');
		$ref    = 'index.php?option=com_fabrik&task=list.view&listid=' . $model->getId() . '&format=' . $format;
		$app->redirect($ref);
	}
}