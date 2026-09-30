<?php

declare(strict_types=1);

namespace App\DTO;

use App\Enums\WorkItemPriority;

final readonly class PriorityResultData
{
    public function __construct(
        public WorkItemPriority $priority,
        public float $score,
        public bool $escalated,
        public string $explanation,
    ) {}
}
