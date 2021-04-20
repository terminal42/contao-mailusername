
# terminal42/contao-mailusername

With this Contao extension, a members username will be set to its email
address. A member no longer needs to give a username when registering
through the front end registration module.

Usernames for existing members are not changed, but their username will
be converted to the email address the next time their data is updated
(e.g. through back end or personal data front end module).

**Attention:** Before installing this extension into an existing system
with members, make sure there are no members with duplicate email
addresses!


## Installation

Choose the installation method that matches your workflow!

### Installation via Contao Manager

Search for `terminal42/contao-mailusername` in the Contao Manager and add it to your installation. Finally, update the
packages.

### Manual installation

Add a composer dependency for this bundle. Therefore, change in the project root and run the following:

```bash
composer require terminal42/contao-mailusername
```

Depending on your environment, the command can differ, i.e. starting with `php composer.phar …` if you do not have
composer installed globally.


## License

This bundle is released under the [MIT](LICENSE)
