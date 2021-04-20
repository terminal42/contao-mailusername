<?php

declare(strict_types=1);

namespace Terminal42\MailusernameBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Input;
use Contao\MemberModel;
use Doctrine\DBAL\Connection;

class MailUsernameListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @Hook("createNewUser", priority=100)
     */
    public function recordUsername($intId, &$arrData): void
    {
        if (!empty($arrData['username'])) {
            return;
        }

        $arrData['username'] = $arrData['email'];
        Input::setPost('username', $arrData['email']);

        $this->connection->update(
            'tl_member',
            ['username' => $arrData['email']],
            ['id' => $intId]
        );

        $memberModel = MemberModel::findByPk($intId);

        // Fix the problem with versions (see #7)
        if (null !== $memberModel) {
            $memberModel->refresh();
        }
    }

    /**
     * @Callback(table="tl_member", target="fields.email.save")
     */
    public function saveMemberEmail($strValue, $dc)
    {
        // See #15
        if ('' === $strValue) {
            $strValue = null;
        }

        $this->connection->update(
            'tl_member',
            ['username' => $strValue],
            ['id' => $dc->id]
        );

        return $strValue;
    }

    /**
     * @Hook("loadLanguageFile")
     */
    public function setUsernameLabel($name): void
    {
        if ('default' === $name) {
            $GLOBALS['TL_LANG']['MSC']['username'] = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
        }
    }

    /**
     * @Callback(table="tl_member", target="config.onload")
     */
    public function setUsernamePostValue(): void
    {
        if (
            0 !== \func_num_args()
            || null !== Input::post('username')
            || 0 !== strpos((string) Input::post('FORM_SUBMIT'), 'tl_registration')
        ) {
            return;
        }

        Input::setPost('username', Input::post('email'));
    }
}
