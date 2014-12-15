<?php

namespace RentJeeves\ExternalApiBundle\Soap;

use JMS\DiExtraBundle\Annotation\Inject;
use JMS\DiExtraBundle\Annotation\InjectParams;
use JMS\DiExtraBundle\Annotation\Service;
use RentJeeves\ExternalApiBundle\Services\Interfaces\SettingsInterface;

/**
 * @Service("soap.wsdl.twig.renderer")
 */
class SoapWsdlTwigRenderer
{
    protected $twig;

    protected $templating;

    /**
     * @InjectParams({
     *     "twig" = @Inject("twig"),
     *     "templating" = @Inject("templating")
     * })
     */
    public function __construct($twig, $templating)
    {
        $this->twig = $twig;
        $this->templating = $templating;
    }

    /**
     * @param $template
     * @return bool
     */
    public function isTwigTemplate($template)
    {
        if (!$this->templating->exists($template)) {
            return false;
        }

        return true;
    }

    /**
     * @param SettingsInterface $settings
     * @param $template
     * @return bool|string
     */
    public function render(SettingsInterface $settings, $template)
    {
        if (!$this->isTwigTemplate($template)) {
            return false;
        }

        file_put_contents(
            $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.uniqid().'.wsdl',
            $this->twig->render(
                $template,
                $settings->getParameters()
            )
        );

        return $path;
    }
}
