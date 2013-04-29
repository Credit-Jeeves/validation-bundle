<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ton
 * Date: 4/29/13
 * Time: 11:20 PM
 * To change this template use File | Settings | File Templates.
 */

namespace CreditJeeves\ExperianBundle;


class Model
{
    /**
     * Ability for external class to get data from protected
     * properties via getters instead just call public properties
     *
     * @param $string
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function __get($name)
    {
        $getter = 'get' . ucfirst($name);
        if (is_callable(array($this, $getter))) {
            return $this->$getter();
        } else {
            throw new Exception(
                "Getter is missed for property [ {$name} ] in class [" .  get_class($this) . "]"
            );
        }
    }
}
