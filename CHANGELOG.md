# Document Search Changelog

## 1.0.3 - 2020-08-06
### Changed
- Adds support for extracting text from additional document types including .doc, .docx, .txt, .xlsx, .pptx
- Change the pdf extract to use smalot/pdfparser instead of pdftotext binary.
- Removed references to pdfToTextExecutable from settings.
- Updated LICENSE.txt to adhere to the MIT license specified in the composer.json file.

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
