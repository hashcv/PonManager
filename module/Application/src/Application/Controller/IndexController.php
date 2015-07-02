<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {


        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $device = new \Application\Entity\Device();
        $device->setName('OLT1');
        $device->setIp(2886733840);
        $device->setSnmpCommunity('public');
        $device->setConfigUsername('admin');
        $device->setConfigPassword('rfvbrflpt');
        $device->setState(1);
        $device->setModelId(1);

        $objectManager->persist($device);
        $objectManager->flush();

        die(var_dump($device->getId()));

        return new ViewModel();
    }
}
