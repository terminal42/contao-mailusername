<?php

declare(strict_types=1);

namespace Terminal42\MailusernameBundle\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Input;
use Contao\MemberModel;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

class MailUsernameListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[AsHook('createNewUser', priority: 100)]
    public function recordUsername(int|string $id, array &$data): void
    {
        if (!empty($data['username'])) {
            return;
        }

        $data['username'] = $data['email'];
        Input::setPost('username', $data['email']);

        $this->saveMemberEmail($data['email'], (object) ['id' => $id]);

        // Fix the problem with versions (see #7)
        if (null !== ($memberModel = MemberModel::findById($id))) {
            $memberModel->refresh();
        }
    }

    #[AsCallback('tl_member', 'fields.email.save')]
    public function saveMemberEmail(string|null $value, object|null $dc): string|null
    {
        // Use the save_callback in ModuleRegistration to prevent duplicate usernames in registration
        if (null === $dc) {
            $exists = $this->connection->fetchOne('SELECT TRUE FROM tl_member WHERE username = ?', [$value]);

            if (false !== $exists) {
                throw new \RuntimeException($this->translator->trans('ERR.unique', [], 'contao_default'));
            }

            return $value;
        }

        // Set the username to NULL if email is empty
        if (!$value) {
            $this->connection->update('tl_member', ['username' => null], ['id' => $dc->id]);

            return $value;
        }

        try {
            $this->connection->executeQuery('LOCK TABLES tl_member WRITE');

            // Check if the username already exists
            $exists = $this->connection->fetchOne(
                'SELECT TRUE FROM tl_member WHERE username = ? AND id != ?',
                [$value, $dc->id],
            );

            if (false !== $exists) {
                throw new \RuntimeException($this->translator->trans('ERR.unique', [], 'contao_default'));
            }

            $this->connection->update('tl_member', ['username' => $value], ['id' => $dc->id]);
        } finally {
            $this->connection->executeQuery('UNLOCK TABLES');
        }

        return $value;
    }

    #[AsHook('loadLanguageFile')]
    public function setUsernameLabel(string $name): void
    {
        if ('default' === $name) {
            $GLOBALS['TL_LANG']['MSC']['username'] = $GLOBALS['TL_LANG']['MSC']['emailAddress'];
        }
    }

    #[AsCallback('tl_member', 'config.onload')]
    public function setUsernamePostValue(): void
    {
        if (
            0 !== \func_num_args()
            || null !== Input::post('username')
            || !str_starts_with((string) Input::post('FORM_SUBMIT'), 'tl_registration')
        ) {
            return;
        }

        Input::setPost('username', Input::post('email'));
    }
}
