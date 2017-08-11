<?php
namespace Extranet\Helper;

class ExtranetEvent
{

	protected $name;
	protected $details;

	public function __construct($name, $details = null)
	{
		$this->name = $name;
		$this->details = $details;
	}


	public function getName()
	{
		return $this->name;
	}


	public function getDetails()
	{
		return $this->details;
	}
}