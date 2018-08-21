<?php

declare(strict_types=1);
namespace KiwiSuite\Media\Type;


use KiwiSuite\Contract\Type\DatabaseTypeInterface;
use KiwiSuite\Contract\Type\SchemaElementInterface;
use KiwiSuite\Entity\Type\AbstractType;
use Doctrine\DBAL\Types\GuidType;
use KiwiSuite\Media\Entity\Media;
use KiwiSuite\Media\Config\MediaConfig;
use Doctrine\DBAL\Types\StringType;
use KiwiSuite\Schema\ElementSubManager;
use KiwiSuite\Contract\Schema\ElementInterface;

final class VideoType extends AbstractType implements DatabaseTypeInterface, SchemaElementInterface
{
    /**
     * @var array
     */
    private $videoWhitelist;

    /**
     * ImageType constructor.
     * @param MediaRepository $mediaRepository
     * @param Uri $uri
     */
    public function __construct(MediaConfig $mediaConfig)
    {
        $this->videoWhitelist = $mediaConfig->videoWhitelist();
    }

    /**
     * @param $value
     * @return mixed|null|object
     */
    protected function transform($value)
    {
        $mimeType = mime_content_type($value);
        $pathInfo = pathinfo($value);
        $extension = $pathInfo['extension'];

        if (!\array_key_exists($extension, $this->videoWhitelist) && !\in_array($mimeType, $this->videoWhitelist)) {
            return new \Exception('invalid video format');
        }
        return $value;
    }

    public function __toString()
    {
        return (string) $this->value();
    }

    public function convertToDatabaseValue()
    {
        return (string) $this->value();
    }

    public static function baseDatabaseType(): string
    {
        return StringType::class;
    }

    /**
     * @param ElementSubManager $elementSubManager
     * @return ElementInterface
     */
    public function schemaElement(ElementSubManager $elementSubManager): ElementInterface
    {
        return $elementSubManager->get(VideoElement::class);
    }

    public static function serviceName(): string
    {
        return 'video';
    }
}