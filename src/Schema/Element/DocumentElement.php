<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Schema\Element;

use Ixocreate\Media\Schema\Type\DocumentType;
use Ixocreate\Schema\Element\AbstractSingleElement;

final class DocumentElement extends AbstractSingleElement
{
    public function type(): string
    {
        return DocumentType::class;
    }

    public function inputType(): string
    {
        return 'document';
    }

    public static function serviceName(): string
    {
        return 'document';
    }
}
