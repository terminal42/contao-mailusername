<?php

$GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['unique'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['maxlength'] = 64;
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['rgxp'] = 'email';
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['disabled'] = true;
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['mandatory'] = false;
$GLOBALS['TL_DCA']['tl_member']['fields']['username']['sql'] = "varchar(64) COLLATE utf8mb4_unicode_ci NULL";
