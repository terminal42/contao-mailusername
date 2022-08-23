<?php

declare(strict_types=1);

namespace Terminal42\MailusernameBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\FrontendUser;
use Contao\Input;
use Contao\MemberModel;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailUsernameListener
{
    private Connection $connection;
    private TranslatorInterface $translator;

    public function __construct(Connection $connection, TranslatorInterface $translator)
    {
        $this->connection = $connection;
        $this->translator = $translator;
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

        $this->saveMemberEmail($arrData['email'], (object) ['id' => $intId]);
        $memberModel = MemberModel::findByPk($intId);

        // Fix the problem with versions (see #7)
        if (null !== $memberModel) {
            $memberModel->refresh();
        }
    }

    /**
     * @Callback(table="tl_member", target="fields.email.save")
     *
     * @param DataContainer|FrontendUser|object|null $dc
     */
    public function saveMemberEmail($strValue, $dc)
    {
        // Use the save_callback in ModuleRegistration to prevent duplicate usernames in registration
        if (null === $dc) {
            $exists = $this->connection->fetchOne('SELECT TRUE FROM tl_member WHERE username = ?', [$strValue]);

            if (false !== $exists) {
                throw new \RuntimeException($this->translator->trans('ERR.unique', [], 'contao_default'));
            }

            return $strValue;
        }

        // Set the username to NULL if email is empty
        if ('' === $strValue) {
            $this->connection->update('tl_member', ['username' => null], ['id' => $dc->id]);
            return $strValue;
        }

        try {
            $this->connection->executeQuery('LOCK TABLES tl_member WRITE');

            // Check if the username already exists
            $exists = $this->connection->fetchOne(
                'SELECT TRUE FROM tl_member WHERE username = ? AND id != ?',
                [$strValue, $dc->id]
            );

            if (false !== $exists) {
                throw new \RuntimeException($this->translator->trans('ERR.unique', [], 'contao_default'));
            }

            $this->connection->update('tl_member', ['username' => $strValue], ['id' => $dc->id]);
        } finally {
            $this->connection->executeQuery('UNLOCK TABLES');
        }

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
