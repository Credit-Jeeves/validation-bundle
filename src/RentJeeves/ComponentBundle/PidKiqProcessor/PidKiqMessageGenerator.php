<?php

namespace RentJeeves\ComponentBundle\PidKiqProcessor;

use CreditJeeves\DataBundle\Enum\PidkiqStatus;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class PidKiqMessageGenerator
{
    /**
     * @var Translator
     */
    protected $translator;

    /**
     * @var string
     */
    protected $supportEmail;

    /**
     * @var string
     */
    protected $supportUrl;

    /**
     * @param Translator $translator
     * @param string $supportEmail
     * @param array $externalUrls
     */
    public function __construct(Translator $translator, $supportEmail, $externalUrls)
    {
        $this->translator = $translator;
        $this->supportEmail = $supportEmail;
        $this->supportUrl = isset($externalUrls['user_voice']) ? $externalUrls['user_voice'] : '';
    }

    /**
     * @param string $status
     * @return string
     */
    public function generateMessage($status)
    {

        switch ($status) {
            case PidkiqStatus::FAILURE:
                return $this->translator->trans(
                    'pidkiq.error.answers-%SUPPORT_EMAIL%',
                    [
                        '%SUPPORT_EMAIL%' => $this->supportEmail,
                    ]
                );
            case PidkiqStatus::LOCKED:
                return $this->translator->trans(
                    'pidkiq.error.lock-%SUPPORT_EMAIL%',
                    [
                        '%SUPPORT_EMAIL%' => $this->supportEmail
                    ]
                );
            case PidkiqStatus::BACKOFF:
                return $this->translator->trans(
                    'pidkiq.error.attempts'
                );
            case PidkiqStatus::UNABLE:
                return $this->translator->trans(
                    'pidkiq.error.questions-%SUPPORT_EMAIL%',
                    [
                        '%SUPPORT_EMAIL%' => $this->supportEmail,
                        '%MAIN_LINK%' => $this->supportUrl,
                    ]
                );
            default:
                return '';
        }
    }
}
