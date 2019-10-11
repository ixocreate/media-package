<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Element;

use Ixocreate\Media\Schema\Type\ImageAnnotatedType;
use Ixocreate\Schema\Element\AbstractSingleElement;
use Ixocreate\Schema\Element\CollectionElement;

final class ImageAnnotatedElement extends AbstractSingleElement
{
    /**
     * @var CollectionElement
     */
    protected $annotationSchema;

    public function type(): string
    {
        return ImageAnnotatedType::class;
    }

    public function inputType(): string
    {
        return 'imageAnnotated';
    }

    public static function serviceName(): string
    {
        return 'imageAnnotated';
    }

    /**
     * @return CollectionElement|null
     */
    public function annotationSchema(): ?array
    {
        return $this->annotationSchema;
    }

    /**
     * @param CollectionElement $annotationSchema
     * @return ImageElement
     */
    public function withAnnotationSchema($annotationSchema): ImageAnnotatedElement
    {
        $element = clone $this;
        $element->annotationSchema = $annotationSchema;

        return $element;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $array = parent::jsonSerialize();
        $array['annotationsSchema'] = $this->annotationSchema();

        return $array;
    }
}
