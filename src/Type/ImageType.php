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
use KiwiSuite\Contract\Schema\ElementInterface;
use KiwiSuite\Contract\Type\DatabaseTypeInterface;
use KiwiSuite\Contract\Type\SchemaElementInterface;
use KiwiSuite\Entity\Type\AbstractType;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Config\MediaConfig;
use KiwiSuite\Media\Repository\MediaRepository;
use KiwiSuite\Media\Uri\Uri;
use KiwiSuite\Schema\Elements\ImageElement;
use KiwiSuite\Schema\ElementSubManager;

final class ImageType extends AbstractType implements DatabaseTypeInterface, SchemaElementInterface
{
    /**
     * @var MediaRepository
     */
    private $mediaRepository;
    /**
     * @var Uri
     */
    private $uri;

    /**
     * ImageType constructor.
     * @param MediaRepository $mediaRepository
     * @param Uri $uri
     */
    public function __construct(MediaRepository $mediaRepository, Uri $uri)
    {
        $this->mediaRepository = $mediaRepository;
        $this->uri = $uri;
    }

    /**
     * @param $value
     * @return mixed|null|object
     */
    protected function transform($value)
    {
        if (is_array($value)) {
            if (empty($value['id'])) {
                return null;
            }

            $value = $value['id'];
        }
        $value = $this->mediaRepository->find($value);
        if (!empty($value)) {
            return $value;
        }

        return null;
    }

    public function __toString()
    {
        if (empty($this->value())) {
            return "";
        }

        return (string) $this->value()->id();
    }

    /**
     * @return mixed|null|string
     */
    public function jsonSerialize()
    {
        if (empty($this->value())) {
            return null;
        }
        $array = $this->value()->toPublicArray();
        $array['thumb'] = $this->getUrl('admin-thumb');
        $array['original'] = $this->getUrl();

        return $array;
    }

    public function convertToDatabaseValue()
    {
        if (empty($this->value())) {
            return null;
        }

        return (string) $this->value()->id();
    }

    public static function baseDatabaseType(): string
    {
        return GuidType::class;
    }

    public function getUrl(string $size = null): string
    {
        /** @var Media $media */
        $media = $this->value();
        if (empty($media) || !($media instanceof Media)) {
            return "";
        }

        return $this->uri->imageUrl($media, $size);
    }

    /**
     * @param ElementSubManager $elementSubManager
     * @return ElementInterface
     */
    public function schemaElement(ElementSubManager $elementSubManager): ElementInterface
    {
        return $elementSubManager->get(ImageElement::class);
    }

    public static function serviceName(): string
    {
        return 'image';
    }
}
