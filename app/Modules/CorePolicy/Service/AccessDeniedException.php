<?php
namespace Continuum\CorePolicy\Service;

/** Safe error — message is one of the 14_API error codes; never leaks candidate existence. */
final class AccessDeniedException extends \RuntimeException {}
