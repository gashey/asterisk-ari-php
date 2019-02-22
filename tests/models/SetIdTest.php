<?php

/**
 * @author Lukas Stermann
 * @copyright ng-voice GmbH (2018)
 */

declare(strict_types=1);

namespace AriStasisApp\Tests\models;


require_once __DIR__ . '/../shared_test_functions.php';

use AriStasisApp\models\{SetId};
use PHPUnit\Framework\TestCase;
use function AriStasisApp\Tests\mapAriResponseParametersToAriObject;

/**
 * Class SetIdTest
 *
 * @package AriStasisApp\Tests\models
 */
final class SetIdTest extends TestCase
{
    /**
     * @throws \JsonMapper_Exception
     */
    public function testParametersMappedCorrectly(): void
    {
        /**
         * @var SetId $setId
         */
        $setId = mapAriResponseParametersToAriObject(
            'SetId',
            [
                'user' => 'ExampleUser',
                'group' => 'ExampleGroup'
            ]
        );
        $this->assertSame('ExampleUser', $setId->getUser());
        $this->assertSame('ExampleGroup', $setId->getGroup());
    }
}