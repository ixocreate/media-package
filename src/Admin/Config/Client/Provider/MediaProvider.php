<?php
/**
 * @see https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Admin\Config\Client\Provider;

use Ixocreate\Contract\Admin\ClientConfigProviderInterface;
use Ixocreate\Contract\Admin\RoleInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;

final class MediaProvider implements ClientConfigProviderInterface
{
    /**
     * @var ImageDefinitionSubManager
     */
    private $imageDefinitionSubManager;

    public function __construct(ImageDefinitionSubManager $imageDefinitionSubManager)
    {
        $this->imageDefinitionSubManager = $imageDefinitionSubManager;
    }

    /**
     * @param RoleInterface|null $role
     * @return array
     */
    public function clientConfig(?RoleInterface $role = null): array
    {
        if (empty($role)) {
            return [];
        }

        $result = [];

        foreach ($this->imageDefinitionSubManager->getServices() as $serviceName) {
            /** @var ImageDefinitionInterface $imageDefinition */
            $imageDefinition = $this->imageDefinitionSubManager->get($serviceName);

            $result[] = [
                'name' => $imageDefinition::serviceName(),
                'label' => \ucfirst($imageDefinition::serviceName()),
                'width' => $imageDefinition->width(),
                'height' => $imageDefinition->height(),
                'upscale' => $imageDefinition->upscale(),
                'mode' => $imageDefinition->mode(),
            ];
        }

        return $result;
    }

    public static function serviceName(): string
    {
        return 'media';
    }
}
