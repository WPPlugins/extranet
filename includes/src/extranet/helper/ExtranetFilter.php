<?php
namespace Extranet\Helper;


class ExtranetFilter 
{
	public static function cleanFileName($title)
	{
		return preg_replace('#[\/\\\<\>\:\;\|\?\*"]#', '', $title);
	}
}

