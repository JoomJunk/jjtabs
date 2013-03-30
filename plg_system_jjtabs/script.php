<?php
/**
 * @copyright (C) 2013 JoomJunk. All rights reserved.
 * @package    JJ Tabs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 **/
 
 /**
 * Script file of plg_jjTabs plugin
 */
class plgSystemJJTabsInstallerScript
{
	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) {
		JFactory::getLanguage()->load('plg_system_jjtabs', JPATH_ADMINISTRATOR);
		echo '<table width="100%">
				<tr>
					<td width="4%">
						<img src="../plugins/system/jjtabs/images/cpanel_48.png" height="48px" width="48px">
					</td>
					<td width="76%">
						<h2>'.JText::_("PLG_JJTABS") . ' 1.0.0 </h2>
					</td>
				</tr>
			</table>

			<table width="100%">
				<tr>
					<td width="50%">' . JText::_("PLG_JJTABS_DESC") . '</td>
					'.JText::_("PLG_JJTABS_DESC_RIGHT") .'
				</tr>
			</table>';
	}
}
?>