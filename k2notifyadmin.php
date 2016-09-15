<?php

/*
  // "Notify of K2 submission" Plugin by Joomkit Ltd for K2 v2.5 - Version 1.0
  // Copyright (c) 2010 Joomkit Ltd. All rights reserved.
  // Released under the GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
  // More info at http://www.joomkit.com/extensions
  // *** Last update: Sunday 22 April ***
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

//jimport('joomla.plugin.plugin');
JLoader::register('K2Plugin', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_k2' . DS . 'lib' . DS . 'k2plugin.php');

/**
 * K2 Plugin to render notifyadmin URLs entered in backend.
 */
class plgK2K2Notifyadmin extends K2Plugin {

    // Some params
    var $pluginName = 'k2notifyadmin';
    var $pluginNameHumanReadable = 'Email Notification Settings';

    function plgK2K2Notifyadmin(& $subject, $params) {
        parent::__construct($subject, $params);
        $this->loadLanguage();
    }

    /**
     * Below we list all available FRONTEND events, to trigger K2 plugins.
     * Watch the different prefix "onK2" instead of just "on" as used in Joomla! already.
     * Most functions are empty to showcase what is available to trigger and output. A few are used to actually output some code for example reasons.
     */
    function onK2PrepareContent(&$item, &$params, $limitstart) {
        return;
    }

    function onK2AfterDisplay(&$item, &$params, $limitstart) {
        return;
    }

    function onK2BeforeDisplay(&$item, &$params, $limitstart) {
        return;
    }

    function onK2AfterDisplayTitle(&$item, &$params, $limitstart) {
        return;
    }

    function onK2BeforeDisplayContent(&$item, &$params, $limitstart) {
        return;
    }

    // Event to display in the frontend the notifyadmin URL as entered in the item form
    function onK2AfterDisplayContent(& $item, & $params, $limitstart) {
        return;
    }

    // Event to display in the frontend the YouTube URL as entered in the category form
    function PPmonK2CategoryDisplay(& $category, & $params, $limitstart) {

//		global $mainframe;
//		$plugin = & JPluginHelper::getPlugin('k2', 'notifyadmin');
//		$pluginParams = new JParameter($plugin->params);
//		$plugins = new K2Parameter($category->plugins, '', $this->pluginName);
//	
//		$output = $plugins->get('notifyemail_cat');
//                //echo 'catdisplay';
//		return $output;
    }

    // Event to display in the frontend the notifyadmin URL as entered in the category form
    // Event to display in the frontend the notifyadmin URL as entered in the user form
    function onK2UserDisplay(&$user, &$params, $limitstart) {
        return;
    }

    // Function to render plugin parameters in the backend - no need to change anything
//	function onRenderAdminForm( & $item, $type, $tab='') {
//		global $mainframe;
//		$form = new K2Parameter($item->plugins, JPATH_SITE.DS.'plugins'.DS.'k2'.DS.$this->pluginName.'.xml', $this->pluginName);
//		
//               
//        if ( !empty ($tab)) {
//			$path = $type.'-'.$tab;
//		}
//		else {
//			$path = $type;
//		}
//		$fields = $form->render('plugins', $path);
//		if ($fields){
//			$plugin = new JObject;
//			$plugin->set('name', $this->pluginNameHumanReadable);
//			$plugin->set('fields', $fields);
//			$output = $plugin;
//		}
//                return $output;var_dump($fields);
//                
//	}
    // This function ONLY for compatibility with K2 Usert system plugin - it use this function instead onRenderAdminForm
    function onRenderUserForm(& $user) {
        /*
         *      global $mainframe;
          $form = new K2Parameter($user->plugins, JPATH_SITE.DS.'plugins'.DS.'k2'.DS.$this->pluginName.'.xml', $this->pluginName);
          $fields = $form->render('plugins', 'user');
          $plugin = new JObject;
          $plugin->set('name', $this->pluginNameHumanReadable);
          $plugin->set('fields', $fields);
          return $plugin;
         */
    }

    //uber mail function!
    //function onK2CategoryDisplay( & $category, & $params, $limitstart) {

    function onAfterK2Save(&$item, $isNew) {

        //jimport( 'joomla.application.application' );
        $mainframe = JFactory::getApplication();
        if ($mainframe->isAdmin()) {
            return; // Dont run in admin
        }

		$body = null;
        // check & load category email params for this submission

        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables');
        $category = JTable::getInstance('K2Category', 'Table');
        $category->load($item->catid);

        $catName = $category->name;

        // Get the K2 plugin params (the stuff you see when you edit the plugin in the plugin manager)
        $plugin = JPluginHelper::getPlugin('k2', $this->pluginName);
        $pluginParams = $this->params;

        // Get the output of the K2 plugin fields (the data entered by your site maintainers)
        $catPluginParams = new K2Parameter($category->plugins, '', $this->pluginName);

        //check if we are supposed to run for edited items globally
        if ($isNew == false):

            //check specific category settings for on edit   cat_EmailSendEdit
            if ((!$catPluginParams->get('cat_EmailSendEdit')) && (!$pluginParams->get('notifyglobalEdit')))
                return;

        endif;

        //SET RECIPIENTS from global plugin settings or category specific
        if ($pluginParams->get('notifyGlobal') && (!$catPluginParams->get('cat_EmailSendStatus'))) {

            $globalEmailList = trim($pluginParams->get('notifyglobalEmailList'));
            //var_dump($globalEmailList);
            $recipient = explode(",", $globalEmailList);
            //$recipient = "alan@joomkit.com";
        } elseif ($catPluginParams->get('cat_EmailSendStatus')) { //check if category set to yes

            //set recipients just from this category           
            $categoryNotifyItemSubmittedList = trim($catPluginParams->get('cat_NotifyEmailList'));
            $recipient = explode(",", $categoryNotifyItemSubmittedList);
            //$recipient = "info@joomkit.com";
        }

        //setup recipients for CC - BCC is to restricted on mail setps and seems flaky with jmailer /phpmailer
        // if recipients more than 1
        if (count($recipient) > 1):

            $i = 1;
            foreach ($recipient as $email):
                echo $i;
                if ($i == 1):
                    $to = "{$email}";
                elseif ($i > 1):
                    $CC[] = "{$email}";
                endif;
                $i++;
            endforeach;
        //remove last comma
        // $CC = substr($CC, 0, -1);
        else:
            $to = "{$recipient[0]}";
            $CC = "";
        endif;

//var_dump($recipient); die();

        //add title via submission type check eg edited or new
        $title = ($isNew == true) ? JText::_('JK_K2NOTIFY_NEW_SUBMISSION'). $item->title : Jtext::_('JK_K2NOTIFY_EDITED_SUBMISSION') . " ".$item->title;


        //stuff data into output for passing to mail body
        $body .= '<table cellpadding="10" style="border-collapse:collapse;border:1px solid #CCC;text-align:left;width:100%;font-family:helvetica,arial;">';
        $body .= '<tr style="background:#D5E7FA;border-bottom: 2px solid #96B0CB;margin:14px"><th colspan="2"> ' . $title . '</th></tr>';


        //user data
        $user = JFactory::getUser();
        $body.='<tr><td width="25%" style="padding:10px">'.JText::_('JK_K2NOTIFY_EDIT_FROM_LABEL').'</td><td>' . $user->username . ', ' . $user->email . '</td></tr>';

        $approveurl = JURI::base() . 'administrator/index.php?option=com_k2&view=item&cid=' . $item->id;

        $body.='<tr><td width="25%" style="padding:10px">'.JText::_('JK_K2NOTIFY_EDIT_APPROVE').'</td><td><a href="' . $approveurl . '">'.JText::_('JK_K2NOTIFY_EDIT_APPROVE_LINK_LABEL').'</a></td></tr>';

        //notifyglobalIncludeK2ItemInBody
//        $notifyglobalIncludeK2ItemInBody = $pluginParams->get(notifyglobalIncludeK2ItemInBody);
        if ($pluginParams->get('notifyglobalIncludeK2ItemInBody')):
            $body.='<tr><td valign"top"  style="padding:10px">Article</td><td>' . $item->introtext . $item->fulltext . '</td></tr>';
            $body.='<tr><td valign"top"  style="padding:10px">'.JText::_('JK_K2NOTIFY_EDIT_APPROVE').'</td><td><a href="' . $approveurl . '">'.JText::_('JK_K2NOTIFY_EDIT_APPROVE_LINK_LABEL').'</a></td></tr>';

        endif;

        //get extrafield data for email body

        $itemID = JRequest::getInt('cid', NULL);


        
        if(count($item->extra_fields)) {
	//	$extra_fields=K2ModelItem::getItemExtraFields($this->item->extra_fields);
		$extra_fields =		$this->getExtraFields($item->extra_fields, $item);
			if (count($extra_fields)) {
			$body.='<tr style="background:#D5E7FA;border-bottom: 2px solid #96B0CB;"><td width="15%">'.JText::_('JK_K2EXFNAME').'</td><td>'.JText::_('JK_K2EXFVALUE').'</td>';
				foreach ($extra_fields as $key=>$extraField) {
	
						$body.='<tr><td align="left" class="key">' . $extraField->name . '</td>';
                            $body.='<td>' . $extraField->value . '</td></tr>';

				}
			}
		}
       
        $body.='</table>';



        $subject = ($isNew == true) ? $pluginParams->get('notifyglobalEmailSubject') . ":" . $item->title : "Submission edited:" . $item->title;

        if ($pluginParams->get('notifyglobalEmailFromName')):
            $fromname = $pluginParams->get('notifyglobalEmailFromName');
        else:
            $fromname = $mainframe->getCfg('fromname');

        endif;
        $fromemail = $mainframe->getCfg('mailfrom');

        $mail = JFactory::getMailer();

        $config = JFactory::getConfig();
        $mail->addRecipient($to);
        $mail->addCC($CC);
        $mail->setSender($fromemail, $fromname);
        $mail->setSubject($subject);
        $mail->IsHTML(true);
        $mail->setBody($body);
        if ($mail->Send()) {
          //  echo "Mail sent successfully.";
        } else {
          //  echo "An error occurred.  Mail was not sent.";
        }

        return;
    }

    //$userName,$commentEmail,$commentText,$commentURL,$row,$response->message,$item->itemID));                

    function onK2CommentSave($userName, $commentEmail, $commentText, $commentURL, $row, $message, $itemid, $catid) {

       global $mainframe;
        // Get the K2 plugin params (the stuff you see when you edit the plugin in the plugin manager)
        $plugin = JPluginHelper::getPlugin('k2', $this->pluginName);
        $pluginCommParams = $this->params;


        $commrecipient ='';
        // check & load category email params for this submission

        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . DS . 'tables');
        $category = &JTable::getInstance('K2Category', 'Table');
        $category->load($catid);
        $catPluginParams = new K2Parameter($category->plugins, '', $this->pluginName);

        //set recipients from global plugin settings or category specific
        // if ($pluginCommParams->get('notifyglobalComm') ) {
        if ($pluginCommParams->get('notifyglobalComm') && (!$catPluginParams->get('cat_EmailCommSendStatus'))) {


            $globalCommEmailList = trim($pluginCommParams->get('notifyglobalCommEmailList'));
            $commrecipient = explode(",", $globalCommEmailList);
        } elseif ($catPluginParams->get('cat_EmailCommSendStatus')) { //check if category set on
            //set recipients just from this category           
            $categoryNotifyItemCommList = trim($catPluginParams->get('cat_NotifyEmailCommList'));
            $commrecipient = explode(",", $categoryNotifyItemCommList);
        }
        
        //setup mailer object
        $mailer = JFactory::getMailer();

        // if recipients more than 1
        if (count($commrecipient) > 1):

            $i = 1;

            foreach ($commrecipient as $email):
               
                if ($i == 1):

                    $to = $email;

                elseif ($i > 1):
                        //build as string not array otherwisemailer fails
                    $CC .= $email.",";
                endif;

                $i++;

            endforeach;
            
                  
            $CC = substr($CC, 0, -1);
            
            //add the cc to mailer 
            $mailer->addCC($CC);  
        else:
            $to = $commrecipient[0];
            
        endif;
        //output
        $body .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
        $body .= '<tr style="background: #eee;"><th valign="top" width="15%">'.JText::_('JK_K2NOTIFY_COMMENT_NOTIFICATION').'</th><th> ' . JText::_('JK_K2NOTIFY_EDIT_COMMENT_HEADING') . '</th></tr>';
        $body .='<tr><td valign="top" width="15%">'.JText::_('JK_K2NOTIFY_EDIT_FROM_LABEL').'</td><td valign="top">' . $userName . '</td></tr>';
        $body .='<tr><td valign="top" width="15%">'.JText::_('JK_K2NOTIFY_EDIT_EMAIL_LABEL').'</td><td valign="top">' . $commentEmail . '</td></tr>';

        $body .='<tr><td valign="top" width="15%">'.JText::_('JK_K2NOTIFY_EDIT_COMMENT_LABEL').'</td><td valign="top">' . $commentText . '<p></td></tr>';
        if($commentURL):
        $body .='<tr><td valign="top" width="15%">'.JText::_('JK_K2NOTIFY_EDIT_COMMENT_URL_LABEL').'</td><td valign="top"><a href="' . $commentURL . '">' . $commentURL . '</a></td></tr>';
        endif;
        $body .='<tr><td valign="top" width="15%">'.JText::_('JK_K2NOTIFY_EDIT_COMMENT_EDIT_APPROVE_LABEL').'</td><td><a href="' . JURI::base() . 'administrator/index.php?option=com_k2&view=comments">Admin Link</a></td></tr>';

        $body .= "</td></tr></table>";


        //GET / Set from name
        //joomkit reverts to get confogi call for new 3.3.3 api
        $setfromname = $pluginCommParams->get('notifyglobalCommEmailFromName');
        $config = JFactory::getConfig();
        if (isset($setfromname)):
            $fromname = $setfromname;

        else:
           // $fromname = $config->get('fromname');

        endif;

        //pass to sender array        
        $sender = array($config->get('mailfrom'), $fromname);

         //set subject
        $CommEmailsubject = $pluginCommParams->get('notifyglobalCommEmailSubject');

        $mailer->setSender($sender);
        $mailer->setSubject($CommEmailsubject);
        $mailer->addRecipient($to);
              
        $mailer->IsHTML(true);
        $mailer->setBody($body);
        $send = $mailer->Send();        
        
        
        //return $message;
        return;
        
        
    }

    function getXFVal($id, $currentValue) {
        global $mainframe;
        $db = JFactory::getDBO();
        $db->setQuery('SELECT * FROM #__k2_extra_fields WHERE id = ' . $id);
        $row = $db->loadObject();
//var_dump($row);
        require_once(JPATH_ROOT . '/administrator/components/com_k2/lib/JSON.php');
        $json = new Services_JSON;
        $jsonObject = $json->decode($row->value);

        $xfvalue = '';
        $currentValue = '';
        if ($row->type == 'textfield' || $row->type == 'textarea' || $row->type == 'labels') {
            $xfvalue = $currentValue;
        } else if ($row->type == 'multipleSelect') {
            foreach ($jsonObject as $option) {
                if (in_array($option->value, $currentValue))
                    $xfvalue.=$option->name . ' ';
            }
        }
        else if ($row->type == 'link') {
            $xfvalue.=$currentValue[0] . ' ';
            $xfvalue.=$currentValue[1] . ' ';
        } else {
            if (!is_array($jsonObject))
                return '';
            foreach ($jsonObject as $option) {
                if ($option->value == $currentValue)
                    $xfvalue.=$option->name;
            }
        }
        var_dump($xfvalue); //die();
        return $xfvalue;
    }
    
    function getExtraFields($itemExtraFields, &$item = null)
	{

		static $K2ItemExtraFieldsInstances = array();
		if ($item && isset($K2ItemExtraFieldsInstances[$item->id]))
		{
			$this->buildAliasBasedExtraFields($K2ItemExtraFieldsInstances[$item->id], $item);
			return $K2ItemExtraFieldsInstances[$item->id];
		}

		jimport('joomla.filesystem.file');
		$db = JFactory::getDBO();
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_k2'.DS.'lib'.DS.'JSON.php');
		$json = new Services_JSON;
		$jsonObjects = $json->decode($itemExtraFields);
		$imgExtensions = array(
			'jpg',
			'jpeg',
			'gif',
			'png'
		);
		$params = K2HelperUtilities::getParams('com_k2');

		if (count($jsonObjects) < 1)
		{
			return NULL;
		}

		foreach ($jsonObjects as $object)
		{
			$extraFieldsIDs[] = $object->id;
		}
		JArrayHelper::toInteger($extraFieldsIDs);
		$condition = @implode(',', $extraFieldsIDs);

		$query = "SELECT extraFieldsGroup FROM #__k2_categories WHERE id=".(int)$item->catid;
		$db->setQuery($query);
		$group = $db->loadResult();

		$query = "SELECT * FROM #__k2_extra_fields WHERE `group` = ".(int)$group." AND published=1 AND (id IN ({$condition}) OR `type` = 'header') ORDER BY ordering ASC";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$size = count($rows);

		for ($i = 0; $i < $size; $i++)
		{

			$value = '';
			$values = array();
			foreach ($jsonObjects as $object)
			{
				if ($rows[$i]->id == $object->id)
				{
					if ($rows[$i]->type == 'textfield' || $rows[$i]->type == 'textarea' || $rows[$i]->type == 'date')
					{
						$value = $object->value;
						if ($rows[$i]->type == 'date' && $value)
						{
							$offset = (K2_JVERSION != '15') ? null : 0;
							$value = JHTML::_('date', $value, JText::_('K2_DATE_FORMAT_LC'), $offset);
						}

					}
					else if ($rows[$i]->type == 'image')
					{
						if ($object->value)
						{
							$src = '';
							if (JString::strpos('http://', $object->value) === false)
							{
								$src .= JURI::root(true);
							}
							$src .= $object->value;
							$value = '<img src="'.$src.'" alt="'.$rows[$i]->name.'" />';
						}
						else
						{
							$value = false;
						}

					}
					else if ($rows[$i]->type == 'labels')
					{
						$labels = explode(',', $object->value);
						if (!is_array($labels))
						{
							$labels = (array)$labels;
						}
						$value = '';
						foreach ($labels as $label)
						{
							$label = JString::trim($label);
							$label = str_replace('-', ' ', $label);
							$value .= '<a href="'.JRoute::_('index.php?option=com_k2&view=itemlist&task=search&searchword='.urlencode($label)).'">'.$label.'</a> ';
						}

					}
					else if ($rows[$i]->type == 'select' || $rows[$i]->type == 'radio')
					{
						foreach ($json->decode($rows[$i]->value) as $option)
						{
							if ($option->value == $object->value)
							{
								$value .= $option->name;
							}

						}
					}
					else if ($rows[$i]->type == 'multipleSelect')
					{
						foreach ($json->decode($rows[$i]->value) as $option)
						{
							if (@in_array($option->value, $object->value))
							{
								$values[] = $option->name;
							}

						}
						$value = @implode(', ', $values);
					}
					else if ($rows[$i]->type == 'csv')
					{
						$array = $object->value;
						if (count($array))
						{
							$value .= '<table cellspacing="0" cellpadding="0" class="csvTable">';
							foreach ($array as $key => $row)
							{
								$value .= '<tr>';
								foreach ($row as $cell)
								{
									$value .= ($key > 0) ? '<td>'.$cell.'</td>' : '<th>'.$cell.'</th>';
								}
								$value .= '</tr>';
							}
							$value .= '</table>';
						}

					}
					else
					{

						switch ($object->value[2])
						{
							case 'same' :
							default :
								$attributes = '';
								break;

							case 'new' :
								$attributes = 'target="_blank"';
								break;

							case 'popup' :
								$attributes = 'class="classicPopup" rel="{\'x\':'.$params->get('linkPopupWidth').',\'y\':'.$params->get('linkPopupHeight').'}"';
								break;

							case 'lightbox' :

								// Joomla! modal required
								if (!defined('K2_JOOMLA_MODAL_REQUIRED'))
									define('K2_JOOMLA_MODAL_REQUIRED', true);

								$filename = @basename($object->value[1]);
								$extension = JFile::getExt($filename);
								if (!empty($extension) && in_array($extension, $imgExtensions))
								{
									$attributes = 'class="modal"';
								}
								else
								{
									$attributes = 'class="modal" rel="{handler:\'iframe\',size:{x:'.$params->get('linkPopupWidth').',y:'.$params->get('linkPopupHeight').'}}"';
								}
								break;
						}
						$object->value[0] = JString::trim($object->value[0]);
						$object->value[1] = JString::trim($object->value[1]);

						if ($object->value[1] && $object->value[1] != 'http://' && $object->value[1] != 'https://')
						{
							if ($object->value[0] == '')
							{
								$object->value[0] = $object->value[1];
							}
							$value = '<a href="'.$object->value[1].'" '.$attributes.'>'.$object->value[0].'</a>';
						}
						else
						{
							$value = false;
						}
					}

				}

			}

			if ($rows[$i]->type == 'header')
			{
				$tmp = json_decode($rows[$i]->value);
				if (!$tmp[0]->displayInFrontEnd)
				{
					$value = null;
				}
				else
				{
					$value = $tmp[0]->value;
				}
			}

			// Detect alias
			$tmpValues = $json->decode($rows[$i]->value);
			if (isset($tmpValues[0]) && isset($tmpValues[0]->alias) && !empty($tmpValues[0]->alias))
			{
				$rows[$i]->alias = $tmpValues[0]->alias;
			}
			else
			{
				$filter = JFilterInput::getInstance();
				$rows[$i]->alias = $filter->clean($rows[$i]->name, 'WORD');
				if (!$rows[$i]->alias)
				{
					$rows[$i]->alias = 'extraField'.$rows[$i]->id;
				}
			}

			if (JString::trim($value) != '')
			{
				$rows[$i]->value = $value;
				if (!is_null($item))
				{
					if (!isset($item->extraFields))
					{
						$item->extraFields = new stdClass;
					}
					$tmpAlias = $rows[$i]->alias;
					$item->extraFields->$tmpAlias = $rows[$i];
				}
			}
			else
			{
				unset($rows[$i]);
			}
		}

		if ($item)
		{
			$K2ItemExtraFieldsInstances[$item->id] = $rows;
		}
		$this->buildAliasBasedExtraFields($K2ItemExtraFieldsInstances[$item->id], $item);
		return $K2ItemExtraFieldsInstances[$item->id];
	}
	
	function buildAliasBasedExtraFields($extraFields, &$item)
	{
		if (is_null($item))
		{
			return false;
		}
		if (!isset($item->extraFields))
		{
			$item->extraFields = new stdClass;
		}
		foreach ($extraFields as $extraField)
		{
			$tmpAlias = $extraField->alias;
			$item->extraFields->$tmpAlias = $extraField;
		}
	}

}