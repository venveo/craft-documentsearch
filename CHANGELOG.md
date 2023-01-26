# Document Search Changelog

## 4.0.2 - 2023/01/26

### Fixed
- Fixed error indexing assets on multiple volumes from console (Thanks @mofman)
- Fixed CP settings not showing up properly

## 4.0.1 - 2022/07/26

### Fixed
- Removed erroneous PHP 9 reference
- Fix references to old "master" branch in composer.json


## 4.0.0 - 2022/07/24

### Added
- Added support for MS Office Documents. (.doc, .docx, .xlsx, .pptx, .txt)

### Changed
- Volume settings now store the volume uuid instead of ID - **ensure sure you update your settings**
- Document Search now requires Craft 4

## 1.0.3 - 2020-10-27
### Fixed
- Fix environment variable for pdftotext path not working (#5)

## 1.0.2 - 2019-11-09
### Changed
- Loosen version constraints on dependencies
- If the text size can be stored in its original form, it now will
- If the text size cannot be stored in the original form, it will be
analyzed for top ranked n-grams.

### Fixed
- Fix bug where non-public volumes were not appearing in settings

## 1.0.1 - 2019-06-06
### Fixed
- Empty token array error on asset save

## 1.0.0 - 2019-02-26
### Added
- Initial release
