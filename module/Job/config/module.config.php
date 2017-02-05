<?php


namespace Job;

return array(
    'controllers' => array(
        'invokables' => array(
            'Job\Console\GlobeSMSProcesser' => 'Job\Console\GlobeSMSProcesserController',
            'Job\Console\SMSSender' => 'Job\Console\SMSSenderController',
            'Job\Console\GCMSender' => 'Job\Console\GCMSenderController',
            'Job\Console\ReferralBonus' => 'Job\Console\ReferralBonusController',
            'Job\Console\CreditsExpiry' => 'Job\Console\CreditsExpiryController',
        ),
    ),
   
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
                'globe-sms-processer' => array(
                    'options' => array(
                        'route' => 'globe-sms-processer [--id=] ',
                        'defaults' => array(
                            'controller' => 'Job\Console\GlobeSMSProcesser',
                            'action' => 'index'
                        )
                    )
                ),
                'sms-sender' => array(
                    'options' => array(
                        'route' => 'sms-sender [--sender=]  [--message=]',
                        'defaults' => array(
                            'controller' => 'Job\Console\SMSSender',
                            'action' => 'index'
                        )
                    )
                ),
                'gcm-sender' => array(
                    'options' => array(
                        'route' => 'gcm-sender [--order_id=]  [--device_token=] [--pns_type=]  [--message=]',
                        'defaults' => array(
                            'controller' => 'Job\Console\GCMSender',
                            'action' => 'index'
                        )
                    )
                ),
                'referral-bonus' => array(
                    'options' => array(
                        'route' => 'referral-bonus [--order_id=]',
                        'defaults' => array(
                            'controller' => 'Job\Console\ReferralBonus',
                            'action' => 'index'
                        )
                    )
                ),
                'credits-expiry' => array(
                    'options' => array(
                        'route' => 'credits-expiry',
                        'defaults' => array(
                            'controller' => 'Job\Console\CreditsExpiry',
                            'action' => 'index'
                        )
                    )
                ),
            ),
        ),
    ),
);
