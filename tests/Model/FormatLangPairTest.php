<?php

/**
 * @author Lukas Stermann
 * @copyright ng-voice GmbH (2018)
 */

declare(strict_types=1);

namespace AriStasisApp\Tests\Model;


require_once __DIR__ . '/../shared_test_functions.php';

use AriStasisApp\Model\{FormatLangPair};
use PHPUnit\Framework\TestCase;
use function AriStasisApp\Tests\mapAriResponseParametersToAriObject;

/**
 * Class FormatLangPairTest
 *
 * @package AriStasisApp\Tests\Model
 */
final class FormatLangPairTest extends TestCase
{
    /**
     * @throws \JsonMapper_Exception
     */
    public function testParametersMappedCorrectly(): void
    {
        /**
         * @var FormatLangPair $formatLangPair
         */
        $formatLangPair = mapAriResponseParametersToAriObject(
            'FormatLangPair',
            [
                'language' => 'en',
                'format' => 'ExampleFormat'
            ]
        );
        $this->assertSame('en', $formatLangPair->getLanguage());
        $this->assertSame('ExampleFormat', $formatLangPair->getFormat());
    }
}