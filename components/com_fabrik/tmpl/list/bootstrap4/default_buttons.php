<?php
/**
 * Bootstrap List Template - Buttons
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.1
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Joomla\CMS\Language\Text;

?>
<div class="fabrikButtonsContainer row-fluid">
<ul class="nav nav-pills  pull-left">

<?php if ($this->showAdd) :?>

	<li><a class="addbutton addRecord" href="<?php echo $this->addRecordLink;?>">
		<?php echo Html::icon('icon-plus', $this->addLabel);?>
	</a></li>
<?php
endif;

if ($this->showToggleCols) :
	echo $this->loadTemplate('togglecols');
endif;

if ($this->canGroupBy) :

	$displayData = new \stdClass;
	$displayData->icon = Html::icon('icon-list-view');
	$displayData->label = Text::_('COM_FABRIK_GROUP_BY');
	$displayData->links = array();
	foreach ($this->groupByHeadings as $url => $obj) :
		$displayData->links[] = '<a data-groupby="' . $obj->group_by . '" href="' . $url . '">' . $obj->label . '</a>';
	endforeach;

	$layout = $this->getModel()->getLayout('fabrik-nav-dropdown');
	echo $layout->render($displayData);
	?>


<?php endif;
if (($this->showClearFilters && (($this->filterMode === 3 || $this->filterMode === 4))  || $this->bootShowFilters == false)) :
	$clearFiltersClass = $this->gotOptionalFilters ? "clearFilters hasFilters" : "clearFilters";
?>
	<li>
		<a class="<?php echo $clearFiltersClass; ?>" href="#">
			<?php echo Html::icon('icon-refresh', Text::_('COM_FABRIK_CLEAR'));?>
		</a>
	</li>
<?php endif;
if ($this->showFilters && $this->toggleFilters) :?>
	<li>
		<?php if ($this->filterMode === 5) :
		?>
			<a href="#filter_modal" data-toggle="modal">
				<?php echo $this->buttons->filter;?>
				<span><?php echo Text::_('COM_FABRIK_FILTER');?></span>
			</a>
				<?php
		else:
		?>
		<a href="#" class="toggleFilters" data-filter-mode="<?php echo $this->filterMode;?>">
			<?php echo $this->buttons->filter;?>
			<span><?php echo Text::_('COM_FABRIK_FILTER');?></span>
		</a>
			<?php endif;
		?>
	</li>
<?php endif;
if ($this->advancedSearch !== '') : ?>
	<li>
		<a href="<?php echo $this->advancedSearchURL?>" class="advanced-search-link">
			<?php echo Html::icon('icon-search', Text::_('COM_FABRIK_ADVANCED_SEARCH'));?>
		</a>
	</li>
<?php endif;
if ($this->showCSVImport || $this->showCSV) :?>
	<?php
	$displayData = new \stdClass;
	$displayData->icon = Html::icon('icon-upload');
	$displayData->label = Text::_('COM_FABRIK_CSV');
	$displayData->links = array();
	if ($this->showCSVImport) :
		$displayData->links[] = '<a href="' . $this->csvImportLink . '" class="csvImportButton">' . Html::icon('icon-download', Text::_('COM_FABRIK_IMPORT_FROM_CSV'))  . '</a>';
	endif;
	if ($this->showCSV) :
		$displayData->links[] = '<a href="#" class="csvExportButton">' . Html::icon('icon-upload', Text::_('COM_FABRIK_EXPORT_TO_CSV')) . '</a>';
	endif;
	$layout = $this->getModel()->getLayout('fabrik-nav-dropdown');
	echo $layout->render($displayData);
	?>

<?php endif;
if ($this->showRSS) :?>
	<li>
		<a href="<?php echo $this->rssLink;?>" class="feedButton">
		<?php echo Html::image('feed.png', 'list', $this->tmpl);?>
		<?php echo Text::_('COM_FABRIK_SUBSCRIBE_RSS');?>
		</a>
	</li>
<?php
endif;
if ($this->showPDF) :?>
			<li><a href="<?php echo $this->pdfLink;?>" class="pdfButton">
				<?php echo Html::icon('icon-file', Text::_('COM_FABRIK_PDF'));?>
			</a></li>
<?php endif;
if ($this->emptyLink) :?>
		<li>
			<a href="<?php echo $this->emptyLink?>" class="doempty">
			<?php echo $this->buttons->empty;?>
			<?php echo Text::_('COM_FABRIK_EMPTY')?>
			</a>
		</li>
<?php
endif;
?>
</ul>
<?php if (array_key_exists('all', $this->filters) || $this->filter_action != 'onchange') {
?>
<ul class="nav pull-right">
	<li>
	<div <?php echo $this->filter_action != 'onchange' ? 'class="input-append"' : ''; ?>>
	<?php if (array_key_exists('all', $this->filters)) {
		echo $this->filters['all']->element;

	if ($this->filter_action != 'onchange') {?>

		<input type="button" class="btn fabrik_filter_submit button" value="<?php echo Text::_('COM_FABRIK_GO');?>" name="filter" >

	<?php
	};?>

	<?php };
	?>
	</div>
	</li>
</ul>
<?php
}
?>
</div>
