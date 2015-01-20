<?php

namespace RentJeeves\CoreBundle\Traits;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator;

trait ValidateEntities
{
    /**
     * @var Validator
     */
    protected $validator;

    protected $errors = [];

    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !!count($this->errors);
    }

    public function resetErrors()
    {
        $this->errors = [];
    }

    /**
     * @return array<string>
     */
    public function getErrors()
    {
        return $this->errors;
    }

    protected function validate($entities)
    {
        is_array($entities) || $entities = [$entities];

        foreach ($entities as $entity) {
            if (empty($entity)) {
                continue;
            }

            $groups = null;

            if (is_array($entity)) {
                count($entity) == 1 || $groups = array_values($entity)[1];
                $entity = reset($entity);
            }

            $errors = $this->validator->validate($entity, $groups);

            if ($errors->count()) {
                foreach ($errors as $error) {
                    /** @var ConstraintViolation $error */
                    $this->errors[] = $error->getMessage();
                }
            }
        }
    }
}
