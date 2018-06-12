<?php
/**
 * kiwi-suite/admin (https://github.com/kiwi-suite/media)
 *
 * @package   kiwi-suite/media
 * @see       https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license   MIT License
 */

declare(strict_types=1);

namespace KiwiSuite\Media\Type;

use Doctrine\DBAL\Types\GuidType;
use KiwiSuite\Contract\Type\DatabaseTypeInterface;
use KiwiSuite\Entity\Type\AbstractType;
use KiwiSuite\Media\Repository\MediaRepository;

final class ImageType extends AbstractType implements DatabaseTypeInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    protected function transform($value)
    {
        $value = $this->mediaRepository->find($value);
        if (!empty($value)) {
            return $value;
        }
    }

    public function __toString()
    {
        if (empty($this->value())) {
            return "";
        }

        return $this->value()->id();
    }

    /**
     * @return mixed|null|string
     */
    public function jsonSerialize()
    {
        if (empty($this->value())) {
            return null;
        }
        return $this->value()->toPublicArray();
    }

    public function convertToDatabaseValue()
    {
        return (string) $this;
    }

    public static function baseDatabaseType(): string
    {
        return GuidType::class;
    }
}
