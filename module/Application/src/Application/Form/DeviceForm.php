<?php
namespace Application\Form;

use Zend\Form\Form;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;

class DeviceForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('device');
        $this->setAttribute('method', 'post');
      //  $this->setInputFilter(new \Application\Form\DeviceInputFilter());
        $this->add(array(
            'name' => 'security',
            'type' => 'Zend\Form\Element\Csrf',
        ));
        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));
        $this->add(array(
            'name' => 'name',
            'type' => 'Text',
            'options' => array(
                'min' => 3,
                'max' => 25,
                'label' => 'Name',
            ),
        ));
        $this->add(array(
            'name' => 'ip',
            'type' => 'Text',
            'options' => array(
                'label' => 'IP address',
            ),
        ));
        $this->add(array(
            'name' => 'snmpCommunity',
            'type' => 'Text',
            'options' => array(
                'label' => 'SNMP Community',
            ),
        ));
        $this->add(array(
            'name' => 'configUsername',
            'type' => 'Text',
            'options' => array(
                'label' => 'Config username',
            ),
        ));
        $this->add(array(
            'name' => 'configPassword',
            'type' => 'Text',
            'options' => array(
                'label' => 'Config password',
            ),
        ));
        $this->add(array(
            'name' => 'state',
            'type' => 'Checkbox',
            'options' => array(
                'label' => 'Enable',
            ),
        ));
        $this->add(array(
            'name' => 'modelId',
            'type' => 'Text',
            'options' => array(
                'label' => 'Model',
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Save',
                'id' => 'submitbutton',
            ),
        ));
    }
}
