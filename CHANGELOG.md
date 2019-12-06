# Release Notes

## [Unreleased](https://github.com/ixocreate/media-package/compare/0.4.17...develop)

## [v0.4.17 (2019-12-06)](https://github.com/ixocreate/media-package/compare/0.4.16...0.4.17)
### Added
- Include`audio/mpeg` in audio whitelist

## [v0.4.16 (2019-11-21)](https://github.com/ixocreate/media-package/compare/0.4.15...0.4.16)
### Fixed
- fix serialization of MediaType

## [v0.4.15 (2019-11-20)](https://github.com/ixocreate/media-package/compare/0.4.14...0.4.15)
### Fixed
- fix mimeType detection on upload
- catch errors in image regeneration

## [v0.4.14 (2019-11-08)](https://github.com/ixocreate/media-package/compare/0.4.13...0.4.14)
### Added
- MediaCacheable
- MediaInfo Class
- MediaCacheable in MediaType
- MediaCacheable in ImageType

## [v0.4.13 (2019-11-05)](https://github.com/ixocreate/media-package/compare/0.4.12...0.4.13)
### Changed
- check in UpdateAction
- filterNewFilename method in UpdateCommand

## [v0.4.12 (2019-11-05)](https://github.com/ixocreate/media-package/compare/0.4.12...0.4.13)
### Changed
- url variant helper

## [v0.4.11 (2019-10-22)](https://github.com/ixocreate/media-package/compare/0.4.10...0.4.11)
### Fixed
- fixed url

## [v0.4.10 (2019-10-22)](https://github.com/ixocreate/media-package/compare/0.4.9...0.4.10)
### Changed
- trivialize variant suffix in url

## [v0.4.9 (2019-10-21)](https://github.com/ixocreate/media-package/compare/0.4.8...0.4.9)
### Added
- Added suffix in Url with MediaDefinitionInfo - CropParameters

## [v0.4.8 (2019-10-18)](https://github.com/ixocreate/media-package/compare/0.4.7...0.4.8)
### Changed
- Improve Duplicate Upload

## [v0.4.7 (2019-10-11)](https://github.com/ixocreate/media-package/compare/0.4.6...0.4.7)
### Fixed
- Annotation schema not passed to element's metadata

## [v0.4.6 (2019-10-11)](https://github.com/ixocreate/media-package/compare/0.4.5...0.4.6)
### Added
- ImageAnnotated Element & Type
### Changed
- improve memory usage
- restrict parallel image processing to cli

## [v0.4.5 (2019-07-18)](https://github.com/ixocreate/media-package/compare/0.4.4...0.4.5)
### Changed
- improve Filesystem dependencies
### Fixed
- fix input validation of RegenerateDefinitionCommand

## [v0.4.0 (2019-05-23)](https://github.com/ixocreate/media-package/compare/0.3.5...0.4.0)
### Added
- add parallel image processing
- 'metaData' field to 'media_media' data table
- 'metaData' to MediaInterface 
- 'width' field to 'media_image_info'
- 'height' field to 'media_image_info'
- 'fileSize' field to 'media_image_info'
- fileSizeLimit to CreateCommand
- FileSizeException

### Changed
- data table name 'media_media_crop' to 'media_definition_info'
- renamed MediaCrop to MediaDefinitionInfo
- renamed MediaCropRepository to MediaDefinitionInfoRepository
- column name 'size' to 'fileSize' in 'media_definition_info'
- renamed HandlerInterface to MediaHandlerInterface
- moved MediaHandlerInterface (Namespace changed)
- MediaHandlerInterface->process() now requires FilesystemInterface as well
- renamed MediaCreateHandlerInterface->move() to MediaCreateHandlerInterface->write()
- unified variable names
- renamed EditorAction->media() to EditorAction->fetchMedia()
- renamed 'RecreateImageDefinition' to 'RegenerateDefinitionCommand'
  - changed CommandName to 'media:regenerate-definition'
  - Command now tries to keep existing Crop Parameters if valid
  - Command now always validates / creates / overwrites .json File 

## [v0.3.5 (2019-05-28)](https://github.com/ixocreate/media-package/compare/0.3.4...0.3.5)
### Fixed
- Travis build clover output

## [v0.3.4 (2019-05-28)](https://github.com/ixocreate/media-package/compare/0.3.3...0.3.4)
### Added
- Media Link integration
 
## [v0.3.3 (2019-05-20)](https://github.com/ixocreate/media-package/compare/0.3.2...0.3.3)
### Added
- Comments to MediaPackageConfig
- return type in ImageDefinitionConfigurator
### Changed
- unified variable names in MediaConfig & MediaUri
### Fixed
- fix LocalFileHandler

## [v0.3.2 (2019-05-08)](https://github.com/ixocreate/media-package/compare/0.3.1...0.3.2)
### Fixed
- RecreateImageDefinition now removes MediaCrop Entries as well

## [v0.3.1 (2019-05-06)](https://github.com/ixocreate/media-package/compare/0.3.0...0.3.1)
### Fixed
- Wrong variable was used for storage in RecreateImageDefinition

## [v0.3.0 (2019-05-06)](https://github.com/ixocreate/media-package/compare/0.2.1...0.3.0)
### Added
- Media specific Types and Elements
### Changed
- Upgrade Publish config to Application v0.2
- Upgrade to Admin Package v0.3
- Upgrade to Filesystem Package v0.3
### Fixed
- fix -c flag in RecreateImageDefinition

## [v0.2.2 (2019-05-20)](https://github.com/ixocreate/media-package/compare/0.2.1...0.2.2)
### Fixed
- fix LocalFileHandler

## [v0.2.1 (2019-05-03)](https://github.com/ixocreate/media-package/compare/0.2.0...0.2.1)
### Fixed
- Allow leading slash in Uri

## [v0.2.0 (2019-04-23)](https://github.com/ixocreate/media-package/compare/0.1.0...0.2.0)
### Changed
- Upgrade to Admin Package v0.2

## [v0.1.0 (2019-04-19)](https://github.com/ixocreate/media-package/compare/master...0.1.0)
### Changed
- Consolidate Package
