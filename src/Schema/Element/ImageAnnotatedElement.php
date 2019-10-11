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

final class ImageAnnotatedElement extends AbstractSingleElement
{
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
     * @param array $annotationSchema
     * @return \Ixocreate\Schema\Element\ElementInterface
     */
    public function withAnnotationSchema(array $annotationSchema)
    {
        return $this->withAddedMetadata('annotationSchema', $annotationSchema);
    }
}
