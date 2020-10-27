# Document Search Changelog

## Unreleased
### Changed
- Added support for MS Office Documents. (.doc, .docx, .xlsx, .pptx, .txt)

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
