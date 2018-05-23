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

use KiwiSuite\Entity\Type\Convert\Convert;
use KiwiSuite\Entity\Type\TypeInterface;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Repository\MediaRepository;

final class ImageType implements TypeInterface
{
    /**
     * @var Media
     */
    private $value;

    /**
     * @var Media
     */
    private $model;

    /**
     * @var MediaRepository
     */
    private $mediaRepository;

    /**
     * ImageType constructor.
     *
     * @param string          $value
     * @param MediaRepository $mediaRepository
     */
    public function __construct(string $value, MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
        $this->model = $mediaRepository->find($value);
        if (!empty($this->model)) {
            $this->value = (string)$this->model->id();
        }
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     * @return mixed
     */
    public static function convertToInternalType($value)
    {
        if ($value instanceof Media) {
            return (string)$value->id();
        }

        if (is_array($value) && array_key_exists("id", $value)) {
            return (string)$value['id'];
        }

        return Convert::convertString($value);
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        if (empty($this->model)) {
            return null;
        }
        return $this->model->toPublicArray();
    }
}
