<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

date_default_timezone_set("UTC");

return array(
    'google_api' => array(
        'gcm_key' => 'AIzaSyCXR_Oz_m61iZrrORoDQto8Gm0SL0ct58U'
    ),
    
    'app_settings' => array(
        'delivery_charges_applicable_below_amount' => 2000,
        'delivery_charges' => 0,//50
        'loyality_for_signup' => 100,
        'loyalty_percent' => 0.5,
        'loyalty_referal_bonus' => 100,
        'min_balance_for_using_loyality_points' => 0,
        'CALL_ME_NO' => '+63 9173266219',
        'banner' => 'banner_aug_14_2016.png'
    ),
    
    'app_clients' => array(
        'store_version' => '1.10',
        'store_min_version' => '1.9',
        'store_upgrade_msg' => 'You are using the old version of the app, please upgrade your app from Playstore or http://www.growsari.com/store.apk',
        'delivery' => 1.7
    ),
    
    'session' => array(
        'table_name' => 'session',
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'cookie_lifetime' => 864000, // max 10 days, clears after that whether its active or not
                'gc_maxlifetime' => 864000, // 10days
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
    ),
);
