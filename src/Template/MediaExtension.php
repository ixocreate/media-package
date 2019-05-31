<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Template;

use Ixocreate\Media\Config\MediaConfig;
use Ixocreate\Media\Repository\MediaDefinitionInfoRepository;
use Ixocreate\Template\Extension\ExtensionInterface;

final class MediaExtension implements ExtensionInterface
{

    /**
     * @var MediaConfig
     */
    private $mediaConfig;

    /**
     * @var MediaDefinitionInfoRepository
     */
    private $mediaDefinitionInfoRepository;

    /**
     * MediaExtension constructor.
     * @param MediaConfig $mediaConfig
     * @param MediaDefinitionInfoRepository $mediaDefinitionInfoRepository
     */
    public function __construct(MediaConfig $mediaConfig, MediaDefinitionInfoRepository $mediaDefinitionInfoRepository)
    {
        $this->mediaConfig = $mediaConfig;
        $this->mediaDefinitionInfoRepository = $mediaDefinitionInfoRepository;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'media';
    }

    public function __invoke()
    {
        return $this;
    }


}