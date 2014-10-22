<?php

namespace RentJeeves\ApiBundle\ErrorHandler;


use CreditJeeves\CoreBundle\Translation\Translator;
use Symfony\Component\Form\Form;
use \Exception;

class ExceptionWrapper
{
    private $errors = [];

    /**
     * @var Translator
     */
    protected $trans;

    public function __construct($data, $trans = null)
    {
        $this->trans = $trans;

        switch ($data) {
            case (isset($data['errors']) && ($data['errors'] instanceof Form)):
                $this->errors = $this->getFormErrors($data['errors']);
                break;
            case (isset($data['errors']) && is_array($data['errors'])):
                foreach ($data['errors'] as $error) {
                    if (self::isCanBeConvertedToString($error)) {
                        $this->errors[] = $this->getErrorDescription(strval($error));
                    } else {
                        $this->errors[] = $this->getErrorDescription('Unknown Error Type for wrapping.');
                    }
                }
                break;
            case (isset($data['message'])):
                $this->errors[] = $this->getErrorDescription($data['message']);
                break;
            case ($data instanceof ErrorDescription):
                $this->errors[] = $data;
                break;
            case ($data instanceof Exception):
                $this->errors[] = $this->getErrorDescription($data->getMessage());
                break;
            case self::isCanBeConvertedToString($data):
                $this->errors[] = $this->getErrorDescription(strval($data));
                break;
            default:
                $this->errors[] = $this->getErrorDescription('Unknown Error Type for wrapping.');
        }
    }

    /**
     * @return array<RentJeeves\ApiBundle\ErrorHandler\ErrorDescription>
     */
    public function getErrors()
    {
        return $this->errors;
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
                $errorMessages[] = $this->getErrorDescription(
                    $error->getMessage(),
                    $name,
                    $child->getData()
                );
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

        if ($this->trans instanceof Translator) {
            $message = $this->trans->trans(
                $message,
                [
                    '%parameter%' => $parameter,
                    '%value%' => $value
                ]
            );
        }

        $errorDescription->message = $message;
        $errorDescription->parameter = $parameter;
        $errorDescription->value = $value;

        return $errorDescription;
    }

    /**
     * @param $variable
     * @return bool
     */
    protected static function isCanBeConvertedToString($variable)
    {
        if (!is_array($variable) &&
            ((!is_object($variable) && settype($variable, 'string') !== false) ||
            (is_object($variable) && method_exists($variable, '__toString')))
        ) {
            return true;
        }

        return false;
    }
}
