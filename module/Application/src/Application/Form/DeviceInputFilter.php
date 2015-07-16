<?php
namespace Application\Form;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
class DeviceInputFilter extends InputFilter
{
    public function __construct()
    {
        $this->add(array(
            'name' => 'name',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 3,
                        'max' => 100,
                    ),
                ),
            ),
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        $this->add(array(
            'name' => 'ip',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 5,
                    ),
                ),
            ),
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
        $this->add(array(
            'name' => 'snmpCommunity',
            'required' => true,
            'validators' => array(
                array(
                    'name' => 'StringLength',
                    'options' => array(
                        'min' => 5,
                    ),
                ),
            ),
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
            ),
        ));
    }
}