<?php
declare(strict_types=1);

namespace ServerApp\lib\core\interface_;

/**
 * Interface TransportLayer
 */
interface TransportLayer
{
    /**
     * @return int
     */
    public function getSockType(): int;
}