<?php
declare(strict_types=1);
namespace KiwiSuite\Media\Uri;

use KiwiSuite\Media\Entity\Media;
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
        return $this->generateUrl($media->basePath(), $media->filename());
    }

    public function imageUrl(Media $media, string $imageDefinition = null): string
    {
        if ($imageDefinition === null) {
            return $this->url($media);
        }

        return $this->generateImageUrl($media->basePath(), $media->filename(), $imageDefinition);
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
}
