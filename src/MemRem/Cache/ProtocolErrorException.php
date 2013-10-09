<?php
/**
 * Exception thrown when communication between a cache implementation's front- and back-ends fails
 */

namespace MemRem\Cache;

use RuntimeException;

class ProtocolErrorException extends RuntimeException {};
