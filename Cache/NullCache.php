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

namespace AppInsightsPHP\Symfony\AppInsightsPHPBundle\Cache;

use Psr\SimpleCache\CacheInterface;

final class NullCache implements CacheInterface
{
    public function get($key, $default = null) : void
    {
    }

    public function set($key, $value, $ttl = null) : void
    {
    }

    public function delete($key) : void
    {
    }

    public function clear() : void
    {
    }

    public function getMultiple($keys, $default = null) : void
    {
    }

    public function setMultiple($values, $ttl = null) : void
    {
    }

    public function deleteMultiple($keys) : void
    {
    }

    public function has($key)
    {
        return false;
    }
}
