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

namespace Ixocreate\Media\Uri;

use Firebase\JWT\JWT;
use Ixocreate\Media\Entity\Media;
use Symfony\Component\Asset\Packages;

final class Uri
{
    /**
     * @var Packages
     */
    private $packages;

    public function __construct(Packages $packages)
    {
        $this->packages = $packages;
    }

    public function url(Media $media): string
    {
        if ($media->publicStatus()) {
            return $this->generateUrl($media->basePath(), $media->filename());
        }
        return $this->generateStreamUrl($media);
    }

    public function imageUrl(Media $media, string $imageDefinition = null): string
    {
        if ($imageDefinition === null) {
            return $this->url($media);
        }

        if ($media->publicStatus()) {
            return $this->generateImageUrl($media->basePath(), $media->filename(), $imageDefinition);
        }

        return $this->generateStreamUrl($media, $imageDefinition);
    }

    public function generateUrl(string $basePath, string $filename): string
    {
        return $this->packages->getUrl($basePath . $filename);
    }

    public function generateImageUrl(string $basePath, string $filename, string $imageDefinition = null): string
    {
        if ($imageDefinition === null) {
            return $this->generateUrl($basePath, $filename);
        }

        return $this->packages->getUrl('/img/' . $imageDefinition . '/' . $basePath . $filename);
    }

    public function generateStreamUrl(Media $media, string $imageDefinition = null): string
    {
        try {
            $payload = [
                'iat' => \time(),
                'exp' => \time() + 50000,
                'data' => [
                    'mediaId' => $media->id(),
                    'imageDefinition' => $imageDefinition,
                ],
            ];

            $jwt = JWT::encode($payload, 'secret', 'HS512');
        } catch (\Exception $e) {
            // TODO
        }

        return $this->packages->getUrl($jwt, 'streamMedia');
    }
}
