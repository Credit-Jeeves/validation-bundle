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

    /**
     * @return array<string>
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * This method can validate different data with different scenario.
     * @param $dataForValidation
     * Can be single object like entity. Can be simple array with object for validation. Can be complex array like
     * [
     *   [$entity, 'Default'],
     *   ['entity' => $entity],
     *   ['entity' => $entity, ['Default', 'Default2']],
     *   ['entity' => $entity, 'groups' => ['Default', 'Default2']],
     *   ['object' => $entity, 'group' => 'Default'],
     *   [$entity, 'groups' => ['Default', 'Default2']],
     * ]
     * and etc.
     * @param $baseValidationGroups
     * Base Validation Groups will be apply for each object that will be validated. Can be single string like "Default"
     * or array with validations groups.
     */
    protected function validate($dataForValidation, $baseValidationGroups = null)
    {
        $this->errors = [];

        if (!is_array($dataForValidation)) {
            $dataForValidation = [$dataForValidation];
        }

        if ($baseValidationGroups && !is_array($baseValidationGroups)) {
            $baseValidationGroups = [$baseValidationGroups];
        }

        foreach ($dataForValidation as $data) {
            if (empty($data)) {
                continue;
            }

            $validationGroups = null;
            $object = $data;

            if (is_array($data)) {
                $object = reset($data);
                $validationGroups = next($data);

                if ($validationGroups !== false && !is_array($validationGroups)) {
                    $validationGroups = [$validationGroups];
                }
            }

            if ($baseValidationGroups) {
                $validationGroups = $validationGroups ?
                    array_merge($baseValidationGroups, $validationGroups) :
                    $baseValidationGroups;
            }

            $errors = $this->validator->validate($object, $validationGroups);

            if ($errors->count()) {
                foreach ($errors as $error) {
                    /** @var ConstraintViolation $error */
                    $this->errors[] = $error->getMessage();
                }
            }
        }
    }
}
