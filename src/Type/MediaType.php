<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Type;

use Ixocreate\Contract\Type\DatabaseTypeInterface;
use Ixocreate\Contract\Type\SchemaElementInterface;
use Ixocreate\Entity\Type\AbstractType;
use Doctrine\DBAL\Types\GuidType;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Schema\Elements\MediaElement;
use Ixocreate\Contract\Schema\ElementInterface;
use Ixocreate\Media\Repository\MediaRepository;
use Ixocreate\Schema\ElementSubManager;
use Ixocreate\Media\Uri\Uri;

class MediaType extends AbstractType implements DatabaseTypeInterface, SchemaElementInterface
{
    /**
     * @var MediaRepository
     */
    protected $mediaRepository;

    /**
     * @var Uri
     */
    protected $uri;

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
        if (\is_array($value)) {
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

    public function getUrl(): string
    {
        /** @var Media $media */
        $media = $this->value();
        if (empty($media) || !($media instanceof Media)) {
            return "";
        }

        return $this->uri->imageUrl($media);
    }

    /**
     * @param ElementSubManager $elementSubManager
     * @return ElementInterface
     */
    public function schemaElement(ElementSubManager $elementSubManager): ElementInterface
    {
        return $elementSubManager->get(MediaElement::class);
    }

    public static function serviceName(): string
    {
        return 'media';
    }
}
