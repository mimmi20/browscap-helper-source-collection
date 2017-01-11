<?php

namespace BrowscapHelper\Source;

use Monolog\Logger;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param \PDO $pdo
     */
    public function __construct(array $collection)
    {
        foreach ($collection as $source) {
            if (! $source instanceof SourceInterface) {
                continue;
            }

            $this->collection[] = $source;
        }
    }

    /**
     * @param \Monolog\Logger                                   $logger
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int                                               $limit
     *
     * @return \Generator
     */
    public function getUserAgents(Logger $logger, OutputInterface $output, $limit = 0)
    {
        $counter   = 0;
        $allAgents = [];

        foreach ($this->collection as $source) {
            if ($limit && $counter >= $limit) {
                return;
            }

            foreach ($source->getUserAgents($logger, $output, $limit) as $ua) {
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
     * @param \Monolog\Logger                                   $logger
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return \Generator
     */
    public function getTests(Logger $logger, OutputInterface $output)
    {
        $allTests = [];

        foreach ($this->collection as $source) {
            foreach ($source->getTests($logger, $output) as $agent => $test) {
                if (empty($agent)) {
                    continue;
                }

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                yield [$agent => $test];
                $allTests[$agent] = 1;
            }
        }
    }
}
