<?php
/*
	recursive 	| 	delete folder 	|	create folder 	|	delete 	| 	upload 	| 	download 	| 	preview 	| 	list 	| 
	0			|	0				| 	0		 		| 	0	 	| 	0 		| 	0			| 	0 			| 	0		| 	
	0			|	0				| 	0		 		| 	0	 	| 	0 		| 	0			| 	0 			| 	1		|	perm & 1 == 1 			--> list files in folder allowed
	0			|	0				| 	0		 		| 	0	 	| 	0 		| 	0			| 	1 			| 	0		|	((perm >> 1) & 1) == 1	--> preview file allowed
	0			|	0				| 	0		 		| 	0	 	| 	0 		| 	1			| 	0 			| 	0		| 	((perm >> 2) & 1) == 1	--> download file allowed
	0			|	0				| 	0		 		| 	0	 	| 	1 		| 	0			| 	0 			| 	0		| 	((perm >> 3) & 1) == 1	--> upload file allowed
	0			|	0				| 	0		 		| 	1	 	| 	0		| 	0			| 	0 			| 	0		|	((perm >> 4) & 1) == 1	--> delete file allowed
	0			|	0				| 	1		 		| 	0	 	| 	0		| 	0			| 	0 			| 	0		| 	((perm >> 5) & 1) == 1  --> create folder allowed
	0			|	1				| 	0		 		| 	0	 	| 	0		| 	0			| 	0 			| 	0		| 	((perm >> 6) & 1) == 1	--> delete folder allowed
	1			|	0				| 	0		 		| 	0	 	| 	0		| 	0			| 	0 			| 	0		| 	((perm >> 7) & 1) == 1	--> recursive rule
	128				64					32					16			8			4				2				1
*/

namespace Extranet\Helper;

class ExtranetPermissionRule
{
	protected $value;

	public function __construct($value = '00000000')
	{
		$this->value = $value;
	}

	public function value()
	{
		return $this->value;
	}
	
	public function is_list()
	{
		return ($this->value[7] == 1);
	}

	public function is_preview()
	{
		return ($this->value[6] == 1);
	}

	public function is_download()
	{
		return ($this->value[5] == 1);
	}

	public function is_upload()
	{
		return ($this->value[4] == 1);
	}

	public function is_unlink()
	{
		return ($this->value[3] == 1);
	}

	public function is_mkdir()
	{
		return ($this->value[2] == 1);
	}

	public function is_rmdir()
	{
		return ($this->value[1] == 1);
	}

	public function is_recursive()
	{
		return ($this->value[0] == 1);
	}

	public function set_list()
	{
		$this->value[7] = 1;return $this;
	}

	public function set_preview()
	{
		$this->value[6] = 1;return $this;
	}

	public function set_download()
	{
		$this->value[5] = 1;return $this;
	}

	public function set_upload()
	{
		$this->value[4] = 1;return $this;
	}

	public function set_unlink()
	{
		$this->value[3] = 1;return $this;
	}

	public function set_mkdir()
	{
		$this->value[2] = 1;return $this;
	}

	public function set_rmdir()
	{
		$this->value[1] = 1;return $this;
	}

	public function set_recursive()
	{
		$this->value[0] = 1;return $this;
	}
}