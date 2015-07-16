<?php
namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/** @ORM\Entity */
class Device
{
	/**
	 * @var int
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @var string
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $name;

    /**
     * @var string
     * @ORM\Column(type="string", length=15, nullable=false)
     */
	protected $ip;
	
	/**
	 * @var string
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $snmpCommunity;
	
	/**
	 * @var string
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $configUsername;
	
	/**
	 * @var string
	 * @ORM\Column(type="string", length=255, nullable=false)
	 */
	protected $configPassword;
	
	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $modelId;
	
	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=true)
	 */
	protected $state;




    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param int $ip
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getSnmpCommunity()
    {
        return $this->snmpCommunity;
    }

    /**
     * @param string $snmpCommunity
     */
    public function setSnmpCommunity($snmpCommunity)
    {
        $this->snmpCommunity = $snmpCommunity;
    }

    /**
     * @return string
     */
    public function getConfigUsername()
    {
        return $this->configUsername;
    }

    /**
     * @param string $configUsername
     */
    public function setConfigUsername($configUsername)
    {
        $this->configUsername = $configUsername;
    }

    /**
     * @return string
     */
    public function getConfigPassword()
    {
        return $this->configPassword;
    }

    /**
     * @param string $configPassword
     */
    public function setConfigPassword($configPassword)
    {
        $this->configPassword = $configPassword;
    }

    /**
     * @return int
     */
    public function getModelId()
    {
        return $this->modelId;
    }

    /**
     * @param int $modelId
     */
    public function setModelId($modelId)
    {
        $this->modelId = $modelId;
    }

    /**
     * @return int
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param int $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }


    /**
     * Helper function.
     */
    public function exchangeArray($data)
    {
        foreach ($data as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = ($val !== null) ? $val : null;
            }
        }
    }
    /**
     * Helper function
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
	
}