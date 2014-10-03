<?php

namespace RentJeeves\ApiBundle\ErrorHandler;


use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Form\FormInterface;

class ExceptionWrapper
{
    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesApi"})
     */
    private $status;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Groups({"RentJeevesApi"})
     */
    private $statusCode;

    /**
     * @Serializer\Type("string")
     * @Serializer\Groups({"RentJeevesApi"})
     */
    private $messages;

    /**
     * @Serializer\Type("array<string>")
     * @Serializer\Groups({"RentJeevesApi"})
     */
    private $errors;

    public function __construct($data)
    {
        $this->status = 'error';
        $this->statusCode = $data['status_code'];
        if (isset($data['errors']) && ($data['errors'] instanceof FormInterface)) {
            $this->errors = $this->prepareErrors($data['errors']);
        }
        $this->messages = $data['message'];
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function prepareErrors(FormInterface $form)
    {
        $errors = [];
        foreach ($form->getIterator() as $element) {
            /** @var FormInterface $element */
            if (count($element->getErrors()) > 0) {
                $errors[$element->getName()] = $element->getErrorsAsString();
            }
        }
        return $errors;
    }
}
