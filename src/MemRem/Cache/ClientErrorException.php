<?php
/**
 * Exception thrown when a back-end cache implementation responds with a client error code
 */

namespace MemRem\Cache;

use LogicException;

class ClientErrorException extends LogicException {};
