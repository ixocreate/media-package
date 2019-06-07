<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOLIT GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Admin\Config\Client\Provider;

use Ixocreate\Admin\ClientConfigProviderInterface;
use Ixocreate\Admin\UserInterface;
use Ixocreate\Media\ImageDefinition\ImageDefinitionSubManager;
use Ixocreate\Media\ImageDefinition\ImageDefinitionInterface;

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

    public static function serviceName(): string
    {
        return 'media';
    }

    /**
     * @param UserInterface|null $user
     * @return array
     */
    public function clientConfig(?UserInterface $user = null): array
    {
        if (empty($user)) {
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
}
