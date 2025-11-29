<?php

return [
    'adminEmail' => 'admin@example.com',
    'senderEmail' => 'noreply@example.com',
    'senderName' => 'Example.com mailer',

    // Customers list cache settings (can be overridden by environment variables)
    // CUSTOMERS_LIST_CACHE_ENABLED: 'true'|'false' (default: false)
    // CUSTOMERS_LIST_CACHE_TTL: integer seconds (default: 300)
    // CUSTOMERS_LIST_PAGE_SIZE_DEFAULT: integer (default: 20)
    'customersList' => [
        'cacheEnabled' => filter_var(getenv('CUSTOMERS_LIST_CACHE_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
        'cacheTtl' => (int)(getenv('CUSTOMERS_LIST_CACHE_TTL') ?: 300),
        'pageSizeDefault' => (int)(getenv('CUSTOMERS_LIST_PAGE_SIZE_DEFAULT') ?: 20),
    ],
];
