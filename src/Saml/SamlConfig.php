<?php declare(strict_types=1);

namespace App\Saml;

use SimpleSAML\Configuration;

class SamlConfig extends Configuration
{
    public function __construct()
    {
        parent::__construct([], 'file');
    }
}