<?php

/**
 * mailusername extension for Contao Open Source CMS
 *
 * @copyright  Copyright (c) 2009-2016, terminal42 gmbh
 * @author     terminal42 gmbh <info@terminal42.ch>
 * @license    http://opensource.org/licenses/lgpl-3.0.html LGPL
 * @link       http://github.com/terminal42/contao-mailusername
 */


class MailUsername extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function recordUsername($intId, &$arrData)
    {
        if (!strlen($arrData['username'])) {
            $arrData['username'] = $arrData['email'];
            \Input::setPost('username', $arrData['email']);

            \Database::getInstance()
                ->prepare("UPDATE tl_member SET username=? WHERE id=?")
                ->execute($arrData['email'], $intId)
            ;

            $memberModel = \MemberModel::findByPk($intId);

            // Fix the problem with versions (see #7)
            if (null !== $memberModel) {
                $memberModel->refresh();
            }
        }
    }

    public function saveMemberEmail($strValue, $dc)
    {
        \Database::getInstance()
            ->prepare("UPDATE tl_member SET username=? WHERE id=?")
            ->execute($strValue, $dc->id)
        ;
        
        return $strValue;
    }

    public function setUsernameLabel($name)
    {
        if ('default' === $name) {
            $GLOBALS['TL_LANG']['MSC']['username'] = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
        }
    }
}
