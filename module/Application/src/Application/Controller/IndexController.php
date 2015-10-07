<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/PonManager for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Application\Entity;
use Zend\Db\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

        $devices = $objectManager
            ->getRepository('\Application\Entity\Device')
            ->findBy(array('state' => 1), array('ip' => 'DESC'));

        $devices_array = array();
        foreach ($devices as $device) {
            $devices_array[] = $device->getArrayCopy();
        }
        $view = new ViewModel(array(
            'devices' => $devices_array,
        ));
        return $view;
    }

    public function viewAction()
    {
        // Check if id and blogpost exists.
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Device id doesn\'t set');
            return $this->redirect()->toRoute('device');
        }
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $device = $objectManager
            ->getRepository('\Application\Entity\Device')
            ->findOneBy(array('id' => $id));
        if (!$device) {
            $this->flashMessenger()->addErrorMessage(sprintf('Device with id %s doesn\'t exists', $id));
            return $this->redirect()->toRoute('device');
        }

        $Array_descr = snmprealwalk($device->getIp(), $device->getSnmpCommunity(), "ifDescr");
        $Array_iftype = snmprealwalk($device->getIp(), $device->getSnmpCommunity(), "ifType");
        $Array_ifOperStatus = snmprealwalk($device->getIp(), $device->getSnmpCommunity(), "ifOperStatus");


        if (count($Array_descr) > 0) {
            // var_dump($Array_descr);
            foreach ($Array_descr as $key => $type) {
                $key = str_replace("IF-MIB::ifDescr.", "", $key);
                $type = trim(str_replace("STRING: ", "", $type));

                $olt[] = strtok($type, ":");

            }
        }

        $pon_cnt = array_count_values($olt);


        if (count($Array_iftype) > 0) {
            // var_dump($Array_iftype);
            foreach ($Array_iftype as $key => $type) {

                if ($type == "INTEGER: ethernetCsmacd(6)") {

                    $arr_eth[] = ["key" => str_replace("IF-MIB::ifType.", "", $key), "descr" => trim(str_replace("STRING: ", "", $Array_descr[str_replace("ifType", "ifDescr", $key)]))];

                }

            }
        }


        if (count($Array_ifOperStatus) > 0) {
            // var_dump($Array_ifOperStatus);
            foreach ($Array_ifOperStatus as $key => $status) {

                $tmp_descr = strtok(trim(str_replace("STRING: ", "", $Array_descr[str_replace("ifOperStatus", "ifDescr", $key)])), ':');
                if ((substr($tmp_descr, 0, 4) == 'EPON') && (trim(str_replace("INTEGER: ", "", $status))) == 'up(1)') {

                    $arr_status[] = $tmp_descr;
                }
            }
        }

        $pon_cnt_act = array_count_values($arr_status);
//var_dump($pon_cnt_act);


        foreach ($arr_eth as $key => $row) {
            $ind[$key] = $row['key'];
            $descr[$key] = $row['descr'];
        }
        array_multisort($descr, SORT_ASC, $arr_eth);

// var_dump($arr_eth);


        $device0 = snmpget($device->getIp(), $device->getSnmpCommunity(), "SNMPv2-MIB::sysDescr.0");
        $device0 = str_replace("\n", "<br>", (str_replace("STRING: ", "", $device0)));

        $sysname = snmpget($device->getIp(), $device->getSnmpCommunity(), "SNMPv2-MIB::sysName.0");
        $sysname = str_replace("\n", "<br>", (str_replace("STRING: ", "", $sysname)));

        $sysuptime = snmpget($device->getIp(), $device->getSnmpCommunity(), "SNMPv2-MIB::sysORUpTime.1");
        $sysuptime = str_replace("\n", "<br>", (str_replace("STRING: ", "", $sysuptime)));


        // Render template.
        $view = new ViewModel(array(
            'device' => $device->getArrayCopy(),
            'device0' => $device0,
            'sysname' => $sysname,
            'sysuptime' => $sysuptime,
            'arr_eth' => $arr_eth,
            'pon_cnt' => $pon_cnt,
            'pon_cnt_act' => $pon_cnt_act,


        ));
        return $view;
    }

    public function addAction()
    {
        $form = new \Application\Form\DeviceForm();
        $form->get('submit')->setValue('Add');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $device = new \Application\Entity\Device();
                $device->exchangeArray($form->getData());
                //$device->setCreated(time());
                //$device->setUserId(0);
                $objectManager->persist($device);
                $objectManager->flush();
                $message = 'Device succesfully saved!';
                $this->flashMessenger()->addMessage($message);
                // Redirect to list of blogposts
                return $this->redirect()->toRoute('device');
            } else {
                $message = 'Error while saving device';
                $this->flashMessenger()->addErrorMessage($message);
            }
        }
        return array('form' => $form);
    }

    public function onuDetailsAction()
    {

        $key = (int)$this->params()->fromRoute('key', 0);
        if (!$key) {
            $this->flashMessenger()->addErrorMessage('ONU key id doesn\'t set');
            return $this->redirect()->toRoute('device');
        }

        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Device id doesn\'t set');
            return $this->redirect()->toRoute('device');
        }
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $device = $objectManager
            ->getRepository('\Application\Entity\Device')
            ->findOneBy(array('id' => $id));
        if (!$device) {
            $this->flashMessenger()->addErrorMessage(sprintf('Device with id %s doesn\'t exists', $id));
            return $this->redirect()->toRoute('device');
        }

        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

        $mac_onu = snmpget($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.101.10.4.1.1.$key");
        $mac_onu = str_replace(" ", ":", strtolower(trim(str_replace("Hex-STRING: ", "", $mac_onu))));


        $url = "http://api.macvendors.com/" . urlencode($mac_onu);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        if ($response) {

            $response = "Vendor: $response";
        } else {
            $response = "Not Found";
        }


        try {
            $opt_level_up = snmpget($device->getIp(), $device->getSnmpCommunity(), "enterprises.3320.101.10.5.1.5.$key");
            $opt_level_up = trim(str_replace("INTEGER: ", "", $opt_level_up) / 10);
        } catch (Exception $e) {
        }


        $active = snmpget($device->getIp(), $device->getSnmpCommunity(), "1.3.6.1.4.1.3320.101.10.1.1.26.$key");
        $active = str_replace(" ", ":", strtolower(trim(str_replace("INTEGER: ", "", $active))));

        $onu_vendor = snmpget($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.101.10.1.1.1.$key");
        $onu_vendor = str_replace(" ", ":", strtolower(trim(str_replace("STRING: ", "", $onu_vendor))));

        $onu_model = snmpget($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.101.10.1.1.2.$key");
        $onu_model = str_replace(" ", ":", strtolower(trim(str_replace("STRING: ", "", $onu_model))));

        $ifdescr = snmpget($device->getIp(), $device->getSnmpCommunity(), "IF-MIB::ifDescr.$key");
        $ifdescr = str_replace(" ", ":", strtolower(trim(str_replace("STRING: ", "", $ifdescr))));

        $soft = snmpget($device->getIp(), $device->getSnmpCommunity(), "SNMPv2-SMI::enterprises.3320.101.10.1.1.5.$key");
        $soft = str_replace(" ", ":", strtolower(trim(str_replace("Hex-STRING: ", "", $soft))));

        $soft_s = '';
        for ($i = 0; $i < strlen($soft) - 1; $i += 2) {
            $soft_s .= chr(hexdec($soft[$i] . $soft[$i + 1]));
        }


        $hard = snmpget($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.101.10.1.1.4.$key");
        $hard = str_replace(" ", ":", strtolower(trim(str_replace("Hex-STRING: ", "", $hard))));

        $hard_s = '';
        for ($i = 0; $i < strlen($hard) - 1; $i += 2) {
            $hard_s .= chr(hexdec($hard[$i] . $hard[$i + 1]));
        }


        $lastchange = snmpget($device->getIp(), $device->getSnmpCommunity(), "IF-MIB::ifLastChange.$key");
        $lastchange = str_replace(" ", ":", strtolower(trim(str_replace("Timeticks: ", "", $lastchange))));

        $lastchange = snmpget($device->getIp(), $device->getSnmpCommunity(), "IF-MIB::ifLastChange.$key");
        $lastchange = str_replace(" ", ":", strtolower(trim(str_replace("Timeticks: ", "", $lastchange))));


        $onu_distance = snmpget($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.101.10.1.1.27.$key");
        $onu_distance = str_replace(" ", ":", strtolower(trim(str_replace("INTEGER: ", "", $onu_distance))));

        $Array_mac = snmprealwalk($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.152.1.1.3.$key");
        foreach ($Array_mac as $fdb_key => $fdb_val) {
            $fdb_mac = str_replace(" ", ":", strtolower(trim(str_replace("Hex-STRING: ", "", $fdb_val))));
            $fdb_vlan = snmpget($device->getIp(), $device->getSnmpCommunity(), str_replace("3320.152.1.1.3", "3320.152.1.1.2", $fdb_key));
            $fdb_vlan = str_replace(" ", ":", strtolower(trim(str_replace("INTEGER: ", "", $fdb_vlan))));
            $fdb[$fdb_mac] = $fdb_vlan;
        }

        $Array_ports = "";
        $Array_ports_ = "";
        $Array_ports__ = "";
        $Array_ports = snmprealwalk($device->getIp(), $device->getSnmpCommunity(), "enterprises.3320.101.12.1.1.8.$key");
        foreach ($Array_ports as $key_ => $state) {
            $port = str_replace("SNMPv2-SMI::enterprises.3320.101.12.1.1.8.$key.", "", $key_);
            $vid = snmpget($device->getIp(), $device->getSnmpCommunity(), "SNMPv2-SMI::enterprises.3320.101.12.1.1.3.$key.$port");
            $vid = str_replace(" ", ":", strtolower(trim(str_replace("INTEGER: ", "", $vid))));

            $Array_state = explode(":", $state);
            $state = trim($Array_state[1]);

            if ($state == 1) {
                $state = "up";
            }
            if ($state == 2) {
                $state = "down";
            }
            $Array_ports__[] = $port . " : " . $state . " ( VlanID: $vid )";
        }

        /* $adapter = new Zend\Db\Adapter\Adapter(array(
             'driver' => 'Mysqli',
             'hostname' => '192.168.100.222';
             'database' => 'zend_db_example',
             'username' => 'developer',
             'password' => 'developer-password'
         ));

         $db = new DbAdapter(
             array(
                 'driver'        => 'Pdo',
                 'dsn'            => 'mysql:dbname=gwdb;host=localhost',
                 'username'       => 'root',
                 'password'       => '',
             )
         );

         $sql = 'select * from customer
         where cust_nbr > ? and cust_nbr < ?';

     $rs = $db->query($sql)->execute(array(125000, 125200));*/


        $view = new ViewModel(array(
            'device' => $device->getArrayCopy(),
            'mac_address' => $mac_onu,
            'ifdescr' => $ifdescr,
            'active' => $active,
            'onu_vendor' => $onu_vendor,
            'onu_model' => $onu_model,
            'response' => $response,
            'hard_s' => $hard_s,
            'soft_s' => $soft_s,
            'opt_level_up' => $opt_level_up,
            'onu_distance' => $onu_distance,
            'Array_ports__' => $Array_ports__,
            'lastchange' => $lastchange,
            'fdb' => $fdb,
        ));
        return $view;

    }

    public function onuListAction()
    {
        // Check if id and blogpost exists.
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Device id doesn\'t set');
            return $this->redirect()->toRoute('device');
        }
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $device = $objectManager
            ->getRepository('\Application\Entity\Device')
            ->findOneBy(array('id' => $id));
        if (!$device) {
            $this->flashMessenger()->addErrorMessage(sprintf('Device with id %s doesn\'t exists', $id));
            return $this->redirect()->toRoute('device');
        }


        error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

        $Array_descr = snmprealwalk($device->getIp(), $device->getSnmpCommunity(), "ifDescr");
        if (count($Array_descr) > 0) {
            foreach ($Array_descr as $key => $type) {
                $key = str_replace("IF-MIB::ifDescr.", "", $key);
                $type = trim(str_replace("STRING: ", "", $type));
                $olt = strtok($type, ":");

                if (preg_match("#:#", $type)) {
                    $active = "";
                    try {
                        $opt_level_up = snmpget($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.101.10.5.1.5.$key");
                        $opt_level_up = trim(str_replace("INTEGER: ", "", $opt_level_up) / 10);
                    } catch (Exception $e) {
                    }

                    $mac_onu = snmpget($device->getIp(), $device->getSnmpCommunity(), ".1.3.6.1.4.1.3320.101.10.4.1.1.$key");
                    $mac_onu = str_replace(" ", ":", strtolower(trim(str_replace("Hex-STRING: ", "", $mac_onu))));


//$active = snmpget("$ip", $communit, "1.3.6.1.4.1.3320.101.10.1.1.26.$key");
//                    $active = str_replace(" ", ":", strtolower(trim(str_replace("INTEGER: ", "", $active))));

//$onu_vendor = snmpget("$ip", $communit, ".1.3.6.1.4.1.3320.101.10.1.1.1.$key");
//                    $onu_vendor = str_replace(" ", ":", strtolower(trim(str_replace("STRING: ", "", $onu_vendor))));

//$onu_model = snmpget("$ip", $communit, ".1.3.6.1.4.1.3320.101.10.1.1.2.$key");
//                    $onu_model = str_replace(" ", ":", strtolower(trim(str_replace("STRING: ", "", $onu_model))));

//$onu_distance = snmpget("$ip", $communit, ".1.3.6.1.4.1.3320.101.10.1.1.27.$key");
//                    $onu_distance = str_replace(" ", ":", strtolower(trim(str_replace("INTEGER: ", "", $onu_distance))));

                    $Array_ports = "";
                    $Array_ports_ = "";
                    $Array_ports__ = "";
//$Array_ports = snmprealwalk("$ip", $communit, "enterprises.3320.101.12.1.1.8.$key");


//print_r($Array_ports);

                    /*foreach($Array_ports as $key_ => $state)
                    {
                        $Array_state=explode(":", $state);
                        $state = trim($Array_state[1]);
                        if($state == 1 ){ $state="up"; }
                        if($state == 2 ){ $state="down"; }
                        $Array_ports__[]= $port." : ".$state;
                    }*/

                    /*if(count($Array_ports__)>0){
                        $Array_olt[$olt][$mac_onu]['ports'] = implode("<br>", $Array_ports__);
                    }*/
                    $Array_olt[$olt][$mac_onu]['type'] = $type;
                    $Array_olt[$olt][$mac_onu]['level_up'] = $opt_level_up;
                    $Array_olt[$olt][$mac_onu]['active'] = $active;
                    $arr0[] = ["key" => $key, "olt" => $olt, "port" => $type, "mac_onu" => $mac_onu, "rxp" => $opt_level_up, "active" => $active];
                }
            }
            ksort($Array_olt);
            foreach ($Array_olt as $key => $type) {
                $olt = $key;
                $n = 0;
                foreach ($type as $key1 => $type1) {
                    $mac_onu = $key1;
                    $level_up = $type1['level_up'];
                    $type = $type1['type'];
                    //  $active = $type1['active'];
                    // $ports = $type1['ports'];

                    $n++;

                    if (($olt != $old_olt) && ($n != 1))
                        $m = 0;
                    $m++;
                    $old_olt = $olt;
                }
            }
        }


        // print_r($arr0);

        $view = new ViewModel(array(
            'device' => $device->getArrayCopy(),
            'obj' => $arr0,
        ));
        return $view;
    }

    public function editAction()
    {

        // Create form.
        $form = new \Application\Form\DeviceForm();
        $form->get('submit')->setValue('Save');
        $request = $this->getRequest();
        if (!$request->isPost()) {
            $id = (int)$this->params()->fromRoute('id', 0);

            if (!$id) {
                $this->flashMessenger()->addErrorMessage('Device id doesn\'t set0000');
                return $this->redirect()->toRoute('device');
            }
            $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
            $device = $objectManager
                ->getRepository('\Application\Entity\Device')
                ->findOneBy(array('id' => $id));
            if (!$device) {
                $this->flashMessenger()->addErrorMessage(sprintf('Device with id %s doesn\'t exists', $id));
                return $this->redirect()->toRoute('device');
            }
            // Fill form data.
            $form->bind($device);
            return array('form' => $form, 'id' => $id, 'device' => $device);
        } else {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
                $data = $form->getData();
                $id = $data['id'];
                try {
                    $pdevice = $objectManager->find('\Application\Entity\Device', $id);
                } catch (\Exception $ex) {
                    return $this->redirect()->toRoute('device', array(
                        'action' => 'index'
                    ));
                }
                $pdevice->exchangeArray($form->getData());
                $objectManager->persist($pdevice);
                $objectManager->flush();
                $message = 'Device succesfully saved!';
                $this->flashMessenger()->addMessage($message);
                // Redirect to list of devices
                return $this->redirect()->toRoute('device');
            } else {
                $message = 'Error while saving device';
                $this->flashMessenger()->addErrorMessage($message);
                return array('form' => $form, 'id' => $id);
            }
        }
    }

    public function deleteAction()
    {
        $id = (int)$this->params()->fromRoute('id', 0);
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Device id doesn\'t set');
            return $this->redirect()->toRoute('device');
        }
        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
            if ($del == 'Yes') {
                $id = (int)$request->getPost('id');
                try {
                    $device = $objectManager->find('Application\Entity\Device', $id);
                    $objectManager->remove($device);
                    $objectManager->flush();
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage('Error while deleting data');
                    return $this->redirect()->toRoute('device', array(
                        'action' => 'index'
                    ));
                }
                $this->flashMessenger()->addMessage(sprintf('Device %d was succesfully deleted', $id));
            }
            return $this->redirect()->toRoute('device');
        }
        return array(
            'id' => $id,
            'device' => $objectManager->find('Application\Entity\Device', $id)->getArrayCopy(),
        );
    }


    public function getAdapter()
    {
        if (!$this->adapter) {
            $sm = $this->getServiceLocator();
            $this->adapter = $sm->get('Zend\Db\Adapter\Adapter');
        }
        return $this->adapter;
    }


    /*        $objectManager = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');

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

            return new ViewModel();*/

}
