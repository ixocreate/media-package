<?php
/**
 * @link https://github.com/ixocreate
 * @copyright IXOCREATE GmbH
 * @license MIT License
 */

declare(strict_types=1);

namespace Ixocreate\Media\Uri;

use Firebase\JWT\JWT;
use Ixocreate\Admin\Config\AdminConfig;
use Ixocreate\Contract\Media\DelegatorInterface;
use Ixocreate\Media\Delegator\Delegators\Image;
use Ixocreate\Media\Delegator\DelegatorSubManager;
use Ixocreate\Media\Entity\Media;
use Ixocreate\Media\MediaPaths;
use Symfony\Component\Asset\Packages;

final class Uri
{
    /**
     * @var Packages
     */
    private $packages;

    /**
     * @var AdminConfig
     */
    private $adminConfig;

    /**
     * @var DelegatorSubManager
     */
    private $delegatorSubManager;

    /**
     * Uri constructor.
     * @param Packages $packages
     * @param AdminConfig $adminConfig
     * @param DelegatorSubManager $delegatorSubManager
     */
    public function __construct(Packages $packages, AdminConfig $adminConfig, DelegatorSubManager $delegatorSubManager)
    {
        $this->packages = $packages;
        $this->adminConfig = $adminConfig;
        $this->delegatorSubManager = $delegatorSubManager;
    }

    /**
     * @param Media $media
     * @return string
     */
    public function url(Media $media): string
    {
        if ($media->publicStatus()) {
            return $this->generateUrl($media->basePath(), $media->filename());
        }
        return $this->generateStreamUrl($media);
    }

    /**
     * @param Media $media
     * @param string|null $imageDefinition
     * @return string
     */
    public function imageUrl(Media $media, string $imageDefinition = null): string
    {
        /** @var DelegatorInterface $imageDelegator */
        $imageDelegator = $this->delegatorSubManager->get(Image::serviceName());
        if (!$imageDelegator->isResponsible($media)) {
            $imageDefinition = null;
        }

        if ($imageDefinition === null) {
            return $this->url($media);
        }

        if ($media->publicStatus()) {
            return $this->generateImageUrl($media->basePath(), $media->filename(), $imageDefinition);
        }

        return $this->generateStreamUrl($media, $imageDefinition);
    }

    /**
     * @param string $basePath
     * @param string $filename
     * @return string
     */
    public function generateUrl(string $basePath, string $filename): string
    {
        return $this->packages->getUrl($basePath . $filename);
    }

    /**
     * @param string $basePath
     * @param string $filename
     * @param string|null $imageDefinition
     * @return string
     */
    public function generateImageUrl(string $basePath, string $filename, string $imageDefinition = null): string
    {
        if ($imageDefinition === null) {
            return $this->generateUrl($basePath, $filename);
        }

        return $this->packages->getUrl(MediaPaths::IMAGE_DEFINITION_PATH . $imageDefinition . '/' . $basePath . $filename);
    }

    /**
     * @param Media $media
     * @param string|null $imageDefinition
     * @return string
     */
    public function generateStreamUrl(Media $media, string $imageDefinition = null): string
    {
        $jwt = null;

        try {
            $payload = [
                'iat' => \time(),
                'exp' => \time() + 50000,
                'data' => [
                    'mediaId' => $media->id(),
                    'imageDefinition' => $imageDefinition,
                ],
            ];

            $jwt = JWT::encode($payload, $this->adminConfig->secret(), 'HS512');
        } catch (\Exception $e) {
            // TODO
        }

        return $this->packages->getUrl($jwt, 'streamMedia');
    }
}
