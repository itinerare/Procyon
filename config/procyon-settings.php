<?php

return [
    // Whether or not to enable the web interface for managing
    // subscriptions. Note that if your Procyon instance is web-accessible,
    // you should set a password for this in your .env file (not here!)
    // or else anyone will be able to modify your subscription settings!
    'web-interface' => false,
    'web-password'  => env('PROCYON_WEB_PASSWORD'),

    // Whether digests are generated with summaries only
    // or the full texts of included entries. Default true.
    'summary-only' => true,

    'feeds' => [
        // Put feeds you wish to follow in single quotes, separated by commas; e.g.
        //'https://itinerare.net/feeds/programming',
        // You can use this and/or the web interface to manage feeds. If the web
        // interface is enabled, feeds can only be removed via the web interface!
    ],

    // Do not change this!
    'version' => '2.0.0',
];
