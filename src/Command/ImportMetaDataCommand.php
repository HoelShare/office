<?php
declare(strict_types=1);

namespace App\Command;

use SimpleSAML\Metadata\SAMLParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportMetaDataCommand extends Command
{
    protected static $defaultName = 'saml:metadata-import';

    public function __construct(
        private string $metaDataUrl,
        private string $metaDataFileName,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $xml = file_get_contents($this->metaDataUrl);
        $samlParser = SAMLParser::parseString($xml);

        $fileContent = <<<'FILE'
<?php

$metadata['%s'] = %s;
FILE;

        $metadata = $samlParser->getMetadata20IdP();
        $fileContent = sprintf($fileContent, $metadata['entityid'], var_export($metadata, true));
        file_put_contents($this->metaDataFileName, $fileContent);

        return 0;
    }
}
