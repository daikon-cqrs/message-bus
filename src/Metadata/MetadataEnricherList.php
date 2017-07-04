<?php
/**
 * This file is part of the daikon/message-bus project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Daikon\MessageBus\Metadata;

use Daikon\DataStructures\TypedListTrait;

final class MetadataEnricherList implements \IteratorAggregate, \Countable
{
    use TypedListTrait;

    public function __construct(array $enrichers = [])
    {
        $this->init($enrichers, MetadataEnricherInterface::CLASS);
    }
}
