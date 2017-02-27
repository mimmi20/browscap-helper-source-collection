<?php
/**
 * This file is part of the browscap-helper-source-collection package.
 *
 * Copyright (c) 2016-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class CollectionSource implements SourceInterface
{
    /**
     * @var \BrowscapHelper\Source\SourceInterface[]
     */
    private $collection = null;

    /**
     * @param array $collection
     */
    public function __construct(array $collection)
    {
        foreach ($collection as $source) {
            if (!$source instanceof SourceInterface) {
                continue;
            }

            $this->collection[] = $source;
        }
    }

    /**
     * @param int $limit
     *
     * @return string[]
     */
    public function getUserAgents($limit = 0)
    {
        $counter   = 0;
        $allAgents = [];

        foreach ($this->collection as $source) {
            if ($limit && $counter >= $limit) {
                return;
            }

            foreach ($source->getUserAgents($limit) as $ua) {
                if ($limit && $counter >= $limit) {
                    return;
                }

                if (empty($ua)) {
                    continue;
                }

                if (array_key_exists($ua, $allAgents)) {
                    continue;
                }

                yield $ua;
                $allAgents[$ua] = 1;
                ++$counter;
            }
        }
    }

    /**
     * @return \UaResult\Result\Result[]
     */
    public function getTests()
    {
        $allTests = [];

        foreach ($this->collection as $source) {
            foreach ($source->getTests() as $agent => $test) {
                if (empty($agent)) {
                    continue;
                }

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                yield $agent => $test;
                $allTests[$agent] = 1;
            }
        }
    }
}
