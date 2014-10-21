<?php

namespace RentJeeves\ApiBundle\ErrorHandler;


use Symfony\Component\Form\Form;

class ExceptionWrapper
{
    private $errors;

    public function __construct($data, $trans = null)
    {
        if (isset($data['errors']) && ($data['errors'] instanceof Form)) {
            $this->errors = $this->prepareErrors($data['errors']);
        } elseif (isset($data['errors']) && is_array($data['errors'])) {
            foreach ($data['errors'] as $error) {
                $errorDescription = new ErrorDescription();
                $errorDescription->message = $error;
                $this->errors[] = $errorDescription;
            }
        } else {
            $errorDescription = new ErrorDescription();
            $errorDescription->message = $data['message'];
            $this->errors[] = $errorDescription;
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    protected function prepareErrors(Form $form)
    {
        $errors = $this->getFormErrors($form);

        return $errors;
    }

    /**
     * @param Form $child
     * @param string $name
     * @return array
     */
    protected function getFormChildErrors(Form $child, $name = '_globals')
    {
        $errorMessages = array();
        if (!$child->isValid()) {
            foreach ($child->getErrors() as $error) {
                $errorMessages[] = $this->getErrorDescription($error->getMessage(), $name, $child->getData());
            }
        }
        return $errorMessages;
    }

    /**
     * @param Form $form
     * @param string $name
     *
     * @return array
     */
    protected function getFormErrors(Form $form, $name = null)
    {
        $errorMessages = [];
        if (!$form->isValid()) {
            if (null === $name) {
                $name = $form->getName();
                $errorMessages = $this->getFormChildErrors($form);
            } else {
                $errorMessages = $this->getFormChildErrors($form, $name);
            }

            $name = $name ? $name . '_' : $name;
            /** @var Form $child */
            foreach ($form as $child) {
                $errorMessages = arrayMergeRecursive(
                    $errorMessages,
                    $this->getFormErrors($child, $name . $child->getName())
                );
            }
        }
        return $errorMessages;
    }

    protected function getErrorDescription($message, $parameter = null, $value = null)
    {
        $errorDescription = new ErrorDescription();

        $errorDescription->message = $message;

        $errorDescription->parameter = $parameter;

        $errorDescription->value = $value;

        return $errorDescription;
    }
}
