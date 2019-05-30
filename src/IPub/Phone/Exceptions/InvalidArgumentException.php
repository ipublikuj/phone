<?php
/**
 * InvalidArgumentException.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:Phone!
 * @subpackage     Exceptions
 * @since          1.0.0
 *
 * @date           13.12.15
 */

declare(strict_types = 1);

namespace IPub\Phone\Exceptions;

class InvalidArgumentException extends \InvalidArgumentException implements IException
{
}
