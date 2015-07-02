<?php
namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


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
	 * @var int
	 * @ORM\Column(type="integer", nullable=false)
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
	
	
}