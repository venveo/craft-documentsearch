# Document Search plugin for Craft CMS 3.x
**NOTE:** This plugin is very much still in development. Don't use it.

Extract the contents of text documents and add to Craft's search index

![Screenshot](resources/img/plugin-logo.png)

## Requirements

This plugin requires Craft CMS 3.0.0-beta.23 or later.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require venveo/document-search

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Document Search.

## Document Search Overview

This plugin allows users to automatically extract keywords from document assets and add
them to Craft's search index.

-Insert text here-

## Configuring Document Search

Document Search requires a runnable binary of `pdftotext`. The default file location
for the binary is set to `/usr/local/bin/pdftotext` but can be changed through config or
settings options.

-Insert text here-

## Using Document Search

The search index will populate keywords extracted from assets when they are saved. 
Keywords for existing assets are not automatically generated, but can be generated
using the `./craft .......` command.

-Insert text here-

## Document Search Roadmap

Some things to do, and ideas for potential features:

* Release it
* Additional file format support (.DWG)

Brought to you by [Venveo](https://venveo.com)
