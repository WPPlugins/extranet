<?php
/**
 * Part of the Joomla! Framework Form Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Form\Field;

use Joomla\Database\DatabaseDriver;
use Joomla\Form\FormHelper;

FormHelper::loadFieldClass('list');

/**
 * Database Connection Form Field class for the Joomla! Framework.
 *
 * Provides a list of available database connections, optionally limiting to a given list.
 *
 * @see    \Joomla\Database\DatabaseDriver
 * @since  1.0
 */
class DatabaseConnectionField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0
	 */
	public $type = 'DatabaseConnection';

	/**
	 * Method to get the list of database options.
	 *
	 * This method produces a drop down list of available databases supported
	 * by {@link \Joomla\Database\DatabaseDriver} classes that are also supported by the application.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   1.0
	 * @see		\Joomla\Database\DatabaseDriver
	 */
	protected function getOptions()
	{
		// This gets the connectors available in the platform and supported by the server.
		$available = DatabaseDriver::getConnectors();

		// Initialise the array to return
		$options = array();

		/**
		 * This gets the list of database types supported by the application.
		 * This should be entered in the form definition as a comma separated list.
		 * If no supported databases are listed, it is assumed all available databases
		 * are supported.
		 */
		$supported = (string) $this->element['supported'];

		if (!empty($supported))
		{
			$supported = explode(',', $supported);

			foreach ($supported as $support)
			{
				if (in_array(ucfirst($support), $available))
				{
					$options[lcfirst($support)] = $this->translateOptions ? $this->getText()->translate(ucfirst($support)) : ucfirst($support);
				}
			}
		}
		else
		{
			foreach ($available as $support)
			{
				$options[lcfirst($support)] = $this->translateOptions ? $this->getText()->translate(ucfirst($support)) : ucfirst($support);
				$options[lcfirst($support)] = $this->getText()->translate(ucfirst($support));
			}
		}

		// This will come into play if an application is installed that requires
		// a database that is not available on the server.
		if (empty($options))
		{
			$options[''] = $this->translateOptions ? $this->getText()->translate('JNONE') : 'None';
		}

		return $options;
	}
}
