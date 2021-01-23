<?php declare(strict_types=1);

$config = [
    'default-sp' => [
        'saml:SP',
        'entityID' => 'http://localhost:18080/simplesaml/saml2/idp/metadata.php',
        'idp' => 'http://localhost:18080/simplesaml/saml2/idp/metadata.php',
        'privatekey' => 'test-saml.pem',
        'certificate' => 'test-saml.crt',
    ]
];