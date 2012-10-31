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
 * @version    1.0.3
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2012, Cartalyst LLC
 * @link       http://cartalyst.com
 */


/*
 * --------------------------------------------------------------------------
 * What we can use in this class.
 * --------------------------------------------------------------------------
 */
use Platform\Menus\Menu;


/**
 * --------------------------------------------------------------------------
 * Install Class v1.1.0
 * --------------------------------------------------------------------------
 *
 * Filesystem bundle settings.
 *
 * @package    Platform
 * @author     Cartalyst LLC
 * @copyright  (c) 2011 - 2012, Cartalyst LLC
 * @license    BSD License (3-clause)
 * @link       http://cartalyst.com
 */
class Platform_Settings_v1_1_0
{
    /**
     * --------------------------------------------------------------------------
     * Function: up()
     * --------------------------------------------------------------------------
     *
     * Make changes to the database.
     *
     * @access   public
     * @return   void
     */
    public function up()
    {
        /*
         * --------------------------------------------------------------------------
         * # 1) Configuration settings.
         * --------------------------------------------------------------------------
         */
        $settings = array(
            // Frontend fallback message.
            //
            array(
                'extension' => 'settings',
                'type'      => 'filesystem',
                'name'      => 'frontend_fallback_message',
                'value'     => 'off',
            ),

            // Frontend failed message.
            //
            array(
                'extension' => 'settings',
                'type'      => 'filesystem',
                'name'      => 'frontend_failed_message',
                'value'     => 'off',
            ),

            // Backend fallback message.
            //
            array(
                'extension' => 'settings',
                'type'      => 'filesystem',
                'name'      => 'backend_fallback_message',
                'value'     => 'warning',
            ),

            // Backend failed message.
            //
            array(
                'extension' => 'settings',
                'type'      => 'filesystem',
                'name'      => 'backend_failed_message',
                'value'     => 'warning',
            ),
        );

        // Insert the settings into the database.
        //
        DB::table('settings')->insert($settings);
    }


    /**
     * --------------------------------------------------------------------------
     * Function: down()
     * --------------------------------------------------------------------------
     *
     * Revert the changes to the database.
     *
     * @access   public
     * @return   void
     */
    public function down()
    {
        /*
         * --------------------------------------------------------------------------
         * # 1) Delete Configuration settings.
         * --------------------------------------------------------------------------
         */
        DB::table('settings')->where('extension', '=', 'settings')->where('type', '=', 'filesystem')->delete();
    }
}
