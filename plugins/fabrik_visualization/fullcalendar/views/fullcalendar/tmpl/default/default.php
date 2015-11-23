<?php
/**
 * * Calendar Viz: Default Tmpl
 *
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.visualization.calendar
 * @copyright	Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license		GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

//@TODO if we ever get calendars inside packages then the ids will need to be
// Replaced with classes contained within a distinct id

$row = $this->row;
?>
<div id="<?php echo $this->containerId;?>" class="fabrik_visualization" style="border:1px sold;margin:5px;">
	<?php if ($this->params->get('show-title', 0))
	{?>
		<h1><?php echo $row->label;?></h1>
	<?php }?>
	<?php if ($row->intro_text != '')
	{?>
	<div><?php echo $row->intro_text;?></div>
	<?php }
	?>
	<div class='calendar-message'>

	</div>

	<div id="calendar">
	</div>
	<div class="row-fluid">
		<div class="span2">
	<?php echo $this->loadTemplate('filter'); ?>
		<?php if ($this->canAdd && $this->params->get('add_type', 'both') != 'dblClickOnly')
		{
		?>
		<a href="#" class="addEventButton" title="Add an event"><?php echo FText::_('PLG_VISUALIZATION_FULLCALENDAR_ADD') ?></a>
	<?php }
	?>
		</div>
	</div>
</div>