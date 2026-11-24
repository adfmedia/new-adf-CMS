<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tracking Parameters & Labels
    |--------------------------------------------------------------------------
    |
    | These options determine the parameters that should be tracked, if they
    | are present in either the current request URL, or the referring URL.
    |
    | The labels option determines what is displayed to the client in both
    | the internal email sent & in the CMS.
    |
    */

    'tracking_parameters'       => [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'gclid'
    ],
    'tracking_parameter_labels' => [
        'utm_source'    => 'UTM source',
        'utm_medium'    => 'UTM medium',
        'utm_campaign'  => 'UTM campaign',
        'utm_term'      => 'UTM term',
        'gclid'         => 'GCLID'
    ],

    /*
    |--------------------------------------------------------------------------
    | Referring Platforms
    |--------------------------------------------------------------------------
    |
    | These options determine how the referring URL is matched to a platform.
    | When this is calculated, the priority is based on:
    |   1. referring_platforms
    |   2. paid_ad_platforms
    |   3. organic_search_platforms
    |
    | The options are all grouped into label => details, where the label is
    | the value tracked, and the details are used to match against the
    | referring URL.
    |
    | Referring platforms are matched exactly against the referring domain
    | Paid ad platforms are matched exactly against the referring URL query 
    | string
    | Organic search platforms are matched as wildcards against the referring
    | domain
    |
    */

    'referring_platforms'       => [
        'Facebook'  => [
            'facebook.com',  // If the referring URL has a domain of facebook.com, Facebook is returned
            'fb.com'
        ],
        'LinkedIn'  => [
            'linkedin.com',
            'lnkd.in'
        ],
        'Twitter/X'   => [
            'twitter.com',
            'x.com',
            't.co'
        ],
        'Instagram' => [
            'instagram.com'
        ],
        'YouTube'   => [
            'youtube.com',
            'youtu.be'
        ],
        'Pinterest' => [
            'pinterest.com',
            'pinterest.co.uk'
        ],
        'TikTok'    => [
            'tiktok.com'
        ]
    ],
    'paid_ad_platforms'         => [
        'Google'    => [
            'utm_source',   // If the referring URL has a utm_source query string parameter, Google is returned
            'utm_medium',
            'utm_campaign',
            'utm_term',
            'gclid'
        ],
        'Microsoft / Bing'  => [
            'msclkid'
        ],
        'Facebook'  => [
            'fbclid'
        ]
    ],
    'organic_search_platforms'  => [
        'Google'    => [
            'google.'   // If the referring domain has "google." in it anywhere, Google is returned
        ],
        'Bing'      => [
            'bing.com'
        ],
        'Yahoo'     => [
            'yahoo.com',
            'search.yahoo.com'
        ],
        'DuckDuckGo'    => [
            'duckduckgo.com'
        ],
        'Brave'     => [
            'brave.com',
            'search.brave.com'
        ],
        'Startpage' => [
            'startpage.com'
        ],
        'Ecisia'    => [
            'ecosia.org'
        ],
        'Yandex'    => [
            'yandex.com',
            'yandex.ru'
        ]
    ],

    'ai_platforms'  => [
        'ChatGPT'       => [
            'chatgpt.com',
            'chat.openai.com',
            'openai.com'
        ],
        'Microsoft Copilot' => [
            'copilot.microsoft.com'
        ],
        'Google Gemini' => [
            'gemini.google.com'
        ],
        'Perplexity'    => [
            'perplexity.ai'
        ],
        'Claude'        => [
            'claude.ai'
        ]
    ],

    'email_domains' => [
        'sendibm1.com',
        'sendinblue.com',
        'brevo.com',
        'list-manage.com',
        'mailchimp.com',
        'createsend.com',
        'hubspotlinks.com',
        'hs-sites.com',
        'mail.google.com',
        'outlook.live.com',
        'outlook.office.com',
    ],

    'directory_domains' => [
        'yell.com',
        'thomsonlocal.com',
        'scoot.co.uk',
        '192.com'
    ],

    'android_app_platforms' => [
        'com.google.android.gm'                   => 'Email',
        'com.google.android.googlequicksearchbox' => 'Organic search (Google)',
        'com.facebook.katana'                     => 'Facebook',
        'com.facebook.lite'                       => 'Facebook',
        'com.facebook.orca'                       => 'Facebook',
        'com.linkedin.android'                    => 'LinkedIn',
        'com.twitter.android'                     => 'Twitter/X',
        'com.instagram.android'                   => 'Instagram',
        'com.microsoft.office.outlook'            => 'Email',
    ]
    
];