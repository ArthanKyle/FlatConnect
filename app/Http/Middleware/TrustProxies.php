<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Trusted proxies (use IPs or '*' for all).
     */
    protected $proxies = '*'; // Secure this in production

    /**
     * Headers to trust from the proxy.
     */
    protected $headers = 
    Request::HEADER_X_FORWARDED_FOR |
    Request::HEADER_X_FORWARDED_HOST |
    Request::HEADER_X_FORWARDED_PORT |
    Request::HEADER_X_FORWARDED_PROTO;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Request
     */
}
// Note: In production, you should set $proxies to specific IPs or use a secure method to determine trusted proxies.
// This is a basic implementation. For more complex scenarios, consider using environment variables or configuration files to manage trusted proxies securely.
// Ensure that you secure the $proxies setting in production environments to prevent potential security issues.
// This code is a middleware class that extends the base Middleware class provided by Laravel.
// It is used to trust proxies, allowing the application to correctly handle forwarded requests.
// The $proxies property is set to '*' to trust all proxies, which is not recommended for production.
