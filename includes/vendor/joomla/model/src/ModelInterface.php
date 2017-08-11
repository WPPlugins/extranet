<?php
/**
 * Part of the Joomla Framework Model Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Model;

use Joomla\Registry\Registry;

/**
 * Joomla Framework Model Interface
 *
 * @since       1.0
 * @deprecated  2.0  Use the StatefulModelInterface instead
 */
interface ModelInterface extends StatefulModelInterface
{
	/**
	 * Set the model state.
	 *
	 * @param   Registry  $state  The state object.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function setState(Registry $state);
}
