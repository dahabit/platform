<?php
/**
 * Part of the Platform application.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Platform
 * @version    1.0
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2012, Cartalyst LLC
 * @link       http://cartalyst.com
 */

return array(

	/* Forms */
	'prepare' => array(
		'legend'      => 'Prepare for Installation',
		'description' => 'We\'ll need to make sure we can write to a few directories first. After installation, we\'ll have you change them back, safe and secure, warm and cozy.',
	),

	'database' => array(
		'legend'          => 'Database Credentials',
		'description'     => 'Now its time to create a database, then give us the details and we\'ll do the rest.',
		'driver'          => 'Database Driver',
		'driver_help'     => 'Select a driver.',
		'server'          => 'Server',
		'server_help'     => 'Input your database host, e.g. localhost',
		'username'        => 'User Name',
		'username_help'   => 'Input your database user.',
		'password'        => 'Password',
		'password_help'   => 'Your database users password',
		'database'        => 'Database',
		'database_help'   => 'Input the name of your database.',
		'disclaimer'      => 'Warning',
		'disclaimer_help' => 'If the database has existing tables that conflict with Platform, they will be dropped during the Platform Installation process. You may want to back up your existing database.',
	),

	'user' => array(
		'legend'                => 'Database Credentials',
		'description'           => 'Now you need an administrator, create your initial user and you\'re almost ready to rock.',
		'first_name'            => 'First Name',
		'first_name_help'       => 'First name of admin.',
		'last_name'             => 'Last Name',
		'last_name_help'        => 'Last name of admin.',
		'email'                 => 'Email Address',
		'email_help'            => 'Email address of admin.',
		'password'              => 'Password',
		'password_help'         => 'Password for admin.',
		'password_confirm'      => 'Confirm Password',
		'password_confirm_help' => 'Password confirmation for admin.',

	),

);