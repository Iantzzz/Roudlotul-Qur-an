<?php

use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;

require_once __DIR__.'/../vendor/autoload.php';

// vercel-php forwards HTTPS requests to PHP over loopback HTTP.
TrustProxies::at(['127.0.0.1', '::1']);
TrustProxies::withHeaders(Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT);

require __DIR__.'/../public/index.php';
