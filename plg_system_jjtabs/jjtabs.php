<?php
/**
 * @copyright (C) 2013 JoomJunk. All rights reserved.
 * @package    JJ Tabs
 * @license    http://www.gnu.org/licenses/gpl-3.0.html
 **/

// No direct access
defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.plugin.plugin');

// Import JLog class
jimport('joomla.log.log');

class plgSystemJJTabs extends JPlugin
{
	function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}
	
	function onAfterDispatch()
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}
		
		$group='jjtabs';
		$params=array();
		self::_loadBehavior($group, $params);
	}

	function onAfterRender()
	{
		// Check that we are in the site application.
		if (JFactory::getApplication()->isAdmin())
		{
			return true;
		}
		
		$identity="";
		$group = 'jjtabs';
		//Retrieve system body
		$body = JResponse::getBody();
		$body_initial=$body;
		
		// Finds Initial Tab(s) in body
		$matches_start=array();
		$regex_start='/{JJTabs Start\s*.*?}/i';
		preg_match_all($regex_start, $body, $matches_start);
		$count_start = count($matches_start[0]);
		
		// Retrieve parameters for tabs from start tab
		for($i=0;$i<$count_start;$i++)
		{	
			$parameters = "";
			$parameters = str_replace('JJTabs Start', '',  $matches_start[0][$i]);
			$parameters = str_replace('{', '', $parameters);
			$parameters = str_replace('}', '', $parameters);
			$parameters = str_replace('|', '', $parameters);
			$parameters = trim($parameters);
			
			if($parameters)
			{
				$params = array();
				$array = array();
				$array = explode(",", $parameters);
				$j=0;
				if($array)
				{
					foreach($array as $param)
					{
						$value[$j] = explode(":", $param);
						if(isset($value[$j][1]) && $value[$j][1])
						{
							$params[$value[$j][0]]=$value[$j][1];
						}
						else
						{
							// Throw exception and stop if there is no value for a parameter
							throw new Exception(JText::_( 'PLG_JJTABS_INVALID_TABS_START' ));
							return false;
						}
						$j++;
					}
				}
			}
		}
		
		// Finds Panels in body
		$matches_panel=array();
		$regex_panel='/{JJTabs Panel\s*.*?}/i';
		preg_match_all($regex_panel, $body, $matches_panel);
		$count_panel = count($matches_panel[0]);
		
		// Finds End Tab(s) in body
		$matches_end=array();
		$regex_end='/{JJTabs End}/i';
		preg_match_all($regex_end, $body, $matches_end);
		$count_end = count($matches_end[0]);
		
		if($count_start && $count_panel && $count_end)
		{
			// Look at default cookie value from plugin parameters if not set in start tab
			if(isset($params['useCookie']))
			{
				if($params['useCookie'] == 'true')
				{
					$params['useCookie']= true;
				}
				else
				{
					$params['useCookie']= '';
				}
			}
			else
			{
				if($this->params->get('cookies', 'false') === 'true')
				{
					$params['useCookie'] = $this->params->get('cookies', 'false');
				}
			}
			
			// Look at default click/hover value from plugin parameters if not set in start tab
			if(!isset($params['click']) && ($this->params->get('click', 'true')==='true'))
			{
				$params['click'] = $this->params->get('click', 'true');
			}
			
			//TO DO Add Support for Cookie's, offset, onActive and onBackground in start tab
			$opt=array();
			//$opt['onActive'] = (isset($params['onActive'])) ? $params['onActive'] : null;
			//$opt['onBackground'] = (isset($params['onBackground'])) ? $params['onBackground'] : null;
			//$opt['display'] = (isset($params['startOffset'])) ? (int) $params['startOffset'] : null;
			$opt['useStorage'] = (isset($params['useCookie']) && $params['useCookie']) ? 'true' : 'false';
			$opt['useClick'] = (isset($params['click']) && $params['click']) ? 'true' : 'false';
			
			// Compact options down
			$options = json_encode($opt);
			
			try
			{
				// If no compulsory options throw an exception
				if(!$options)
				{
					throw new Exception(JText::_( 'PLG_JJTABS_NO_OPTIONS' ));				
				}
				// Try's to replace tags with div structure for js
				$this->_processStart($body, $matches_start, $count_start, $group);
				$this->_processPanel($body, $matches_panel, $count_panel);
				$this->_processEnd($body, $matches_end, $count_end, $options);
			}
			catch (Exception $e)
			{
				// Throws a warning message from any exceptions generated
				if(version_compare(JVERSION,'3.0.0','ge'))
				{
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
				else
				{
					JError::raiseWarning(null, $e->getMessage());
				}
				// Log tabs errors to specific file.
				JLog::addLogger(
					array(
						'text_file' => 'plg_system_jjtabs.errors.php'
					),
					JLog::ALL,
					'plg_system_jjtabs'
				);
				JLog::add($e->getMessage(), JLog::WARNING, 'plg_system_jjtabs');
				// Remove the processing tags - just leaving the text for
				// a nice fallback
				$this->_processRemove($body_initial, $matches_start, $count_start, $matches_panel, $count_panel, $matches_end, $count_end);
				JResponse::setBody($body_initial);
				return true;
			}
			JResponse::setBody($body);
		}
	}
	
	protected function _processRemove(&$body_initial, $matches_start, $count_start, $matches_panel, $count_panel, $matches_end, $count_end)
	{
		for($i=0;$i<$count_start;$i++)
		{
			$body_initial = str_replace($matches_start[0][$i], '', $body_initial);
		}
		for($i=0;$i<$count_panel;$i++)
		{
			$body_initial = str_replace($matches_panel[0][$i], '', $body_initial);
		}
		for($i=0;$i<$count_end;$i++)
		{
			$body_initial = str_replace($matches_end[0][$i], '', $body_initial);
		}
	}

	
	protected function _processStart(&$body, $matches, $count, $group)
	{
		for($i=0;$i<$count;$i++)
		{
			$start_replace='<div id="'.$group.'" class="responsive-tabs"><div>';
			$body = str_replace($matches[0][$i], $start_replace, $body);
		}
	}
	
	protected function _processPanel(&$body, $matches, $count)
	{
		for($i=0;$i<$count;$i++)
		{
			$parameters = "";
			$parameters = str_replace('JJTabs Panel', '', $matches[0][$i]);
			$parameters = str_replace('{', '', $parameters);
			$parameters = str_replace('}', '', $parameters);
			$parameters = str_replace('|', '', $parameters);
			$parameters = trim($parameters);
			
			$params = array();
			// Set the id to null, colour to the parameter default.
			$params['identity'] = '';
			$params['colour'] = $this->params->get('colour', 'blue');

			if($parameters)
			{
				$array=array();
				$array = explode(",", $parameters);
				$j=0;
				if($array) {
					foreach($array as $param)
					{
						$value[$j] = explode(":", $param);
						if(isset($value[$j][1]) && $value[$j][1]) {
							$params[$value[$j][0]]=$value[$j][1];
						} else {
							// Throw exception and stop if there is no value for a parameter
							throw new Exception(JText::_( 'PLG_JJTABS_INVALID_TABS_PANEL' ));
							return false;
						}
						$j++;
					}
				}
			}
			
			// Throw exception and stop process if no title for tab
			if(!isset($params['title']))
			{
				throw new Exception(JText::_( 'PLG_JJTABS_NO_TITLE' ));
				return false;
			}
			
			$panel_replace='</div><'.$this->params->get('tag', 'h2').'>'.$params['title'].'</'.$this->params->get('tag', 'h2').'><div id="'.$params['identity'] .'" data-tabcolour="'. $params['colour'] .'" >';
			$body = str_replace( $matches[0][$i] , $panel_replace, $body);
		}
	}

	protected function _processEnd(&$body, $matches, $count, $options)
	{
		for($i=0;$i<$count;$i++)
		{
			// Replace with end tags and javascript to active the tabs.
			$end_replace='</div></div><script type="text/javascript">(function($){
				$(document).ready(function() {
					RESPONSIVEUI.responsiveTabs('.$options.');
				});
			})(jQuery);</script>';
			$body = str_replace( $matches[0][$i], $end_replace, $body);
		}
	}

	protected function _loadBehavior($group)
	{
		static $loaded = array();

		if (!array_key_exists((string) $group, $loaded))
		{
			// Include jQuery framework natively on Joomla 3.0, Load from Plugin in Joomla 2.5
			if(version_compare(JVERSION,'3.0.0','ge')) {
				JHtml::_('jquery.framework');
			} else {
				JHtml::_('script', 'plugins/system/jjtabs/js/jquery-1.8.3.min.js');	
			}
									
			$document = JFactory::getDocument();
			JHtml::_('script', 'plugins/system/jjtabs/js/cookie.js');
			JHtml::_('script', 'plugins/system/jjtabs/js/tabs.js');
			JHtml::_('stylesheet', 'plugins/system/jjtabs/css/tabs.css');

			$loaded[(string) $group] = true;
		}
	}
}