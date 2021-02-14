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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle;

final class FlatArray
{
    private $array;

    public function __construct(array $array)
    {
        $this->array = $array;
    }

    public function __invoke() : array
    {
        return $this->flatterArray($this->array);
    }

    private function flatterArray(array $array, string $prefix = '') : array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (\is_array($value) && \count($value) > 1) {
                $result += $this->flatterArray($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = \is_array($value) ? \current($value) : $value;
            }
        }

        return $result;
    }
}
