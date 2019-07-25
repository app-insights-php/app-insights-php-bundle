<?php

declare(strict_types=1);

/*
 * This file is part of the App Insights PHP project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Tests;

use AppInsightsPHP\Symfony\AppInsightsPHPBundle\FlatArray;
use PHPUnit\Framework\TestCase;

final class FlatArrayTest extends TestCase
{
    /**
     * @dataProvider arrays
     */
    public function test_flat_array(array $flatArray, array $array): void
    {
        $this->assertEquals(
            $flatArray,
            (new FlatArray($array))()
        );
    }

    public function arrays(): \Generator
    {
        yield [[], []];
        yield [
            [
                'id' => 'user-id',
                'dimensions.length.value' => 10,
                'dimensions.length.uom' => 'inches',
                'dimensions.width.value' => 20,
                'dimensions.width.uom' => 'inches',
                'dimensions.height.value' => 30,
                'dimensions.height.uom' => 'inches',
                'names' => 'John Snow',
                'tags.0' => 'primary',
                'tags.1' => 'default',
                'tags.2' => 'principle',
            ],
            [
                'id' => 'user-id',
                'dimensions' => [
                    'length' => [
                        'value' => 10,
                        'uom' => 'inches',
                    ],
                    'width' => [
                        'value' => 20,
                        'uom' => 'inches',
                    ],
                    'height' => [
                        'value' => 30,
                        'uom' => 'inches',
                    ],
                ],
                'names' => ['John Snow'],
                'tags' => ['primary', 'default', 'principle'],
            ],
        ];
    }
}
