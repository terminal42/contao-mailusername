<?php

/**
 * mailusername extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2009-2015, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-mailusername
 */


/**
 * Hooks
 */
array_insert($GLOBALS['TL_HOOKS']['createNewUser'], 0, array(array('MailUsername', 'recordUsername')));
$GLOBALS['TL_HOOKS']['loadLanguageFile'][] = array('MailUsername', 'setUsernameLabel');
