<?php

/**
 * mailusername extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2009-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-mailusername
 */


/**
 * Fields
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['email']['save_callback'][] = array('MailUsername', 'saveMemberEmail');
$GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['unique'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['rgxp'] = 'email';
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['disabled'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['mandatory'] = false;
