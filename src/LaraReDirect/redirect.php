<?php
$httpStatus = array(
    100 => 'Continue',
    101 => 'Switching Protocols',
    102 => 'Processing',
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    203 => 'Non-authoritative Information',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    207 => 'Multi-Status',
    208 => 'Already Reported',
    226 => 'IM Used',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    305 => 'Use Proxy',
    307 => 'Temporary Redirect',
    308 => 'Permanent Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Payload Too Large',
    414 => 'Request-URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Requested Range Not Satisfiable',
    417 => 'Expectation Failed',
    418 => 'I\'m a teapot',
    421 => 'Misdirected Request',
    422 => 'Unprocessable Entity',
    423 => 'Locked',
    424 => 'Failed Dependency',
    426 => 'Upgrade Required',
    428 => 'Precondition Required',
    429 => 'Too Many Requests',
    431 => 'Request Header Fields Too Large',
    444 => 'Connection Closed Without Response',
    451 => 'Unavailable For Legal Reasons',
    499 => 'Client Closed Request',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
    506 => 'Variant Also Negotiates',
    507 => 'Insufficient Storage',
    508 => 'Loop Detected',
    510 => 'Not Extended',
    511 => 'Network Authentication Required',
    599 => 'Network Connect Timeout Error',
);
if (!isset($_SERVER['REQUEST_URI'])) {
    return;
}

$rewriteTable = array(
    '/' => '/blog'
);

$rewriteRegexes = array();
$rewrite = function ($uri) {
    header(sprintf('Location: %s', $uri), true, 301);
    exit(0);
};

$test = function () use ($rewriteTable, $rewriteRegexes, $rewrite) {
    $uri = $_SERVER['REQUEST_URI'];
    $uri = parse_url($uri, PHP_URL_PATH);

    if (!$uri) {
        return;
    }

    if (isset($rewriteTable[strtolower($uri)])) {
        $rewriteTo = $rewriteTable[strtolower($uri)];
        if (is_callable($rewriteTo)) {
            $rewriteTo = $rewriteTo();
        }
        $rewrite($rewriteTo);
        return;
    }

    foreach ($rewriteRegexes as $regex => $rewriteUri) {
        $matches = array();
        if (!preg_match($regex, $uri, $matches)) {
            continue;
        }

        if (is_callable($rewriteUri)) {
            $rewriteUri = $rewriteUri($uri, $matches);
            if (false !== $rewriteUri) {
                $rewrite($rewriteUri);
            }
            return;
        }

        foreach ($matches as $key => $value) {
            if (is_int($key) || is_numeric($key)) {
                continue;
            }
            $rewriteUri = str_replace(sprintf('%%%s%%', $key), $value, $rewriteUri);
        }
        $rewrite($rewriteUri);
        return;
    }
};
$test();
unset($rewriteTable, $rewriteRegexes, $rewrite, $test);
