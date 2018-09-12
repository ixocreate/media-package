<?php
/**
 * kiwi-suite/media (https://github.com/kiwi-suite/media)
 *
 * @package kiwi-suite/media
 * @see https://github.com/kiwi-suite/media
 * @copyright Copyright (c) 2010 - 2018 kiwi suite GmbH
 * @license MIT License
 */
declare(strict_types=1);

namespace KiwiSuite\Media\Type;

use Doctrine\DBAL\Types\StringType;
use KiwiSuite\Admin\Response\ApiErrorResponse;
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

final class ImageType extends MediaType implements DatabaseTypeInterface, SchemaElementInterface
{
    /**
     * @var array
     */
    private $imageWhitelist;

    /**
     * ImageType constructor.
     * @param MediaRepository $mediaRepository
     * @param Uri $uri
     */
    public function __construct(MediaConfig $mediaConfig, MediaRepository $mediaRepository, Uri $uri)
    {
        $this->imageWhitelist = $mediaConfig->imageWhitelist();
        parent::__construct($mediaRepository, $uri);
    }

    /**
     * @param Media $media
     * @throws  \Exception
     */
    protected function validateType(Media $media)
    {
        $extension = \pathinfo($media->filename(), PATHINFO_EXTENSION);

        if (!\in_array($media->mimeType(), $this->imageWhitelist) || !\array_key_exists($extension, $this->imageWhitelist)) {
            throw new \Exception('not a valid ImageType');
        }
        return $media;
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
            return $this->validateType($value);
        }
        return null;
    }

    /**
     * @param ElementSubManager $elementSubManager
     * @return ElementInterface
     */
    public function schemaElement(ElementSubManager $elementSubManager): ElementInterface
    {
        return $elementSubManager->get(ImageElement::class);
    }

    /**
     * @return string
     */
    public static function serviceName(): string
    {
        return 'image';
    }
}
