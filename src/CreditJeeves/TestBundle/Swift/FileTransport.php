<?php
namespace CreditJeeves\TestBundle\Swift;

use CreditJeeves\TestBundle\Swift\Transport\FileTransport as Base;
use JMS\DiExtraBundle\Annotation as DI;
use \Swift_DependencyContainer;

/**
 * @DI\Service("swiftmailer.mailer.transport.file")
 */
class FileTransport extends Base
{
    /**
     * Create a new MailTransport, optionally specifying $extraParams.
     *
     * @param string $extraParams
     */
    public function __construct()
    {
        call_user_func_array(
            array($this, '\\CreditJeeves\\TestBundle\\Swift\\Transport\\FileTransport::__construct'),
            Swift_DependencyContainer::getInstance()->createDependenciesFor('transport.mail')
        );
    }

    /**
     * Create a new MailTransport instance.
     *
     * @param string $extraParams To be passed to mail()
     *
     * @return FileTransport
     */
    public static function newInstance()
    {
        return new self();
    }
}
