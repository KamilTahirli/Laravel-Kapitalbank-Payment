<?php

return [
    // KapitalBank payment settings
    'test_url' => 'https://tstpg.kapitalbank.az:5443/Exec',
    'prod_url' => 'https://3dsrv.kapitalbank.az:5443/Exec',
    'merchant_id' => 'E1180156',
    'currency_code' => 944,
    'approve_url' => '/approve',
    'cancel_url' => '/cancel',
    'decline_url' => '/decline',
    'lang_code' => 'AZ',
    'cert_file' => 'payment_files/kapital/testmerchant-1.crt',
    'key_file' => 'payment_files/kapital/merchant_name.key'
];
