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
     * @param $translator
     * @param $supportEmail
     * @param $externalUrls
     */
    public function __construct(Translator $translator, $supportEmail, $externalUrls)
    {
        $this->translator = $translator;
        $this->supportEmail = $supportEmail;
        $this->supportUrl = $externalUrls['user_voice'];
    }

    /**
     * @param $status
     * @return string
     */
    public function generateMessage($status)
    {
        if (PidkiqStatus::FAILURE === $status) {
            return $this->translator->trans(
                'pidkiq.error.incorrect.answer-%SUPPORT_EMAIL%',
                [
                    '%SUPPORT_EMAIL%' => $this->supportEmail,
                    '%MAIN_LINK%'     => $this->supportUrl,
                ]
            );
        } elseif (PidkiqStatus::LOCKED === $status) {
             return $this->translator->trans(
                'pidkiq.error.lock-%SUPPORT_EMAIL%',
                ['%SUPPORT_EMAIL%' => $this->supportEmail]
            );
        } elseif (PidkiqStatus::BACKOFF === $status) {
            return $this->translator->trans('pidkiq.error.attempts');
        } elseif (PidkiqStatus::UNABLE === $status) {
            return $this->translator->trans(
                'pidkiq.error.questions-%SUPPORT_EMAIL%',
                [
                    '%SUPPORT_EMAIL%' => $this->supportEmail,
                    '%MAIN_LINK%'     => $this->supportUrl,
                ]
            );
        }

        return '';
    }
}
