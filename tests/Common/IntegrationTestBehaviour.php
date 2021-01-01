<?php
declare(strict_types=1);

namespace App\Tests\Common;

trait IntegrationTestBehaviour
{
    use KernelTestBehaviour;
    use DatabaseTransactionBehaviour;
    use AuthorizationTrait;
}
