# Document Search plugin for Craft CMS 3.1
Extracts keywords of PDF documents and adds them to Craft CMS's native search index

## Document Search Usage

Once installed and configured (see configuration section), PDF assets with text in them (does not work with images, such as scans) will be indexed automatically.

Like other fields in Craft, you may tweak the search query to your liking by targeting the field named `contentKeywords`

## Requirements
- Craft CMS 3.1.x
- pdftotext executable

## Installation
### Plugin
To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require venveo/document-search

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Document Search.

### pdftotext Executable
To install on Ubuntu or Debian, the precompiled binaries can be procured from aptitude:

`apt-get install poppler-utils`

To install on RedHat or CentOS, the precompiled binaries can be procured from yum:

`yum install poppler-utils`

**Note:**
If you're looking for a full-text document search solution, this isn't it. The purpose of this plugin is to boil down large documents to consumable sizes for a PHP-based web server.

## Configuring Document Search

Document Search requires a runnable binary of `pdftotext`. The default file location
for the binary is set to `/usr/local/bin/pdftotext` but can be changed through config or
settings options.

To check if you have pdftotext installed on your server, you can run:

`which pdftotext`

See the installation section for notes on installing pdftotext.

## Using Document Search

The search index will populate keywords extracted from assets when they are saved. 
Keywords for existing assets are not automatically generated, but can be generated
using the `./craft document-search/parse-documents/index-all` command.


Brought to you by [Venveo](https://venveo.com)
