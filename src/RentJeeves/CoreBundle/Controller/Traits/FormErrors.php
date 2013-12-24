<?php
namespace RentJeeves\CoreBundle\Controller\Traits;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\JsonResponse;

trait FormErrors
{

    /**
     * @param FormInterface $form
     * @param string $name
     *
     * @return array
     */
    private function getFormChildErrors(FormInterface $child, $name)
    {
        $errorMessages = array();
        if (!$child->isValid()) {
            foreach ($child->getErrors() as $error) {
                $errorMessages[] = $this->get('translator.default')->trans(
                    $error->getMessage()
                );
            }
        }
        return $errorMessages;
    }

    /**
     * @param FormInterface $form
     * @param string $name
     *
     * @return array
     */
    private function getFormErrors(FormInterface $form, $name = null)
    {
        $return = array();
        if (!$form->isValid()) {
            $errorMessages = array();
            if (null == $name) {
                $name = $form->getName();
                $globalErrors = $this->getFormChildErrors($form, null);
                if (!empty($globalErrors)) {
                    $errorMessages['_globals'] = $globalErrors;
                }
            } else {
                $errorMessages = $this->getFormChildErrors($form, null);
            }

            /** @var FormInterface $child */
            foreach ($form as $child) {
                $errorMessages = arrayMergeRecursive(
                    $errorMessages,
                    $this->getFormErrors($child, "{$name}_" . $child->getName())
                );
            }
            $return[$name] = $errorMessages;
        }
        return $return;
    }

    /**
     * @param array|FormInterface $params
     *
     * @return JsonResponse
     */
    protected function renderErrors($forms, $statusCode = 200)
    {
        if (!is_array($forms)) {
            $forms = array($forms);
        }
        $return = array();
        /** @var FormInterface $form */
        foreach ($forms as $form) {
            if (!($form instanceof FormInterface)) {
                throw new \ErrorException('Passed parameter is not form');
            }
            $return = arrayMergeRecursive($return, $this->getFormErrors($form));
        }
        return new JsonResponse($return, $statusCode);
    }

}
