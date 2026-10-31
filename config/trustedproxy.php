<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | The IP addresses and CIDR subnets that are trusted as reverse proxies.
    | Set via TRUSTED_PROXIES in .env (e.g., "127.0.0.1,10.0.0.0/8").
    | Trust-all wildcard ('*') is strictly disallowed before proxy topology is verified.
    |
    */

    'proxies' => (function () {
        $envProxies = env('TRUSTED_PROXIES');
        if (empty($envProxies) || $envProxies === '*' || $envProxies === '**') {
            return null;
        }

        return str_contains($envProxies, ',')
            ? array_map('trim', explode(',', $envProxies))
            : trim($envProxies);
    })(),

];
