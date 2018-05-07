<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\Media\Exceptions\InvalidExtensionException;
use Psr\Http\Message\UriInterface;

final class MediaConfig
{
    /**
     * @var array
     */
    private $config;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * MediaConfig constructor.
     * @param array $config
     * @param UriInterface $uri
     */
    public function __construct(array $config, UriInterface $uri)
    {
        $this->config = $config;
        $this->uri = $uri;
        $this->checkDriver();
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getDriver(): string
    {
        return $this->config['driver'];
    }

    /**
     * @return InvalidExtensionException|true
     */
    private function checkDriver():? InvalidExtensionException
    {
        if (empty($this->config['driver'])) {
            $this->config['driver'] = 'imagick';
        }
        if (extension_loaded($this->config['driver']) === false) {
            $this->config['driver'] = 'gd';
            if(extension_loaded($this->config['driver']) === false) {
                throw new InvalidExtensionException("Neither 'gd' or 'imagick' PHP Extension could be found");
            }
        }
        return true;
    }
}