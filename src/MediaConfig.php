<?php
declare(strict_types=1);

namespace KiwiSuite\Media;

use KiwiSuite\Media\Exceptions\InvalidExtensionException;
use KiwiSuite\Media\Exceptions\InvalidConfigException;
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
        $this->assertDriver();
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
     * @throws \KiwiSuite\Media\Exceptions\InvalidExtensionException
     * @throws \KiwiSuite\Media\Exceptions\InvalidConfigException
     */
    private function assertDriver(): void
    {
        $allowedConfigParameter = ['automatic', 'gd', 'imagick'];

        if (empty($this->config['driver'])) {
            $this->config['driver'] = 'automatic';
        }
        if (in_array($this->config['driver'], $allowedConfigParameter)) {
            switch ($this->config['driver']):
                case 'gd':
                    if (extension_loaded('gd') === false) {
                        throw new InvalidExtensionException("PHP Extension 'gd' could not be found");
                    }
                    break;
                case 'imagick':
                    if (extension_loaded('imagick') === false) {
                        throw new InvalidExtensionException("PHP Extension 'imagick' could not be found");
                    }
                    break;
                case 'automatic':
                    $this->config['driver'] = 'imagick';
                    if (extension_loaded('imagick') === false) {
                        $this->config['driver'] = 'gd';
                        if (extension_loaded('gd') === false) {
                            throw new InvalidExtensionException("Neither 'gd' or 'imagick' PHP Extension could be found");
                        }
                    }
                    break;
            endswitch;
        } else {
            throw new InvalidConfigException(sprintf("Given media config driver: '%s', is not valid", $this->config['driver']));
        }
    }
}