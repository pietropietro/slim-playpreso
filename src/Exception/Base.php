<?php
declare(strict_types=1);

namespace App\Exception;

abstract class Base extends \Exception
{
    public function __construct(string $message, int $code)
    {
        parent::__construct($message, $code);
        header('Access-Control-Allow-Origin: ' . $_ENV['ALLOW_URL_REQUEST']);
        header('Access-Control-Allow-Headers: Content-Type, Accept, Origin, Authorization, X-Frontend-Version');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
    }
}
