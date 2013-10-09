<?php
/**
 * Exception thrown when a back-end cache implementation responds with a server error code
 */

namespace MemRem\Cache;

use RuntimeException;

class ServerErrorException extends RuntimeException {};
