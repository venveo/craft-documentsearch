# Document Search plugin for Craft CMS 3.1
Extracts keywords and phrases from PDF documents and adds them to Craft CMS's native search index.

**NOTE:** 
Please try before you buy and make sure this plugin suits your needs. You may not get the results you're expecting!

**NOTE:**
If you're looking for a full-text document search solution, this isn't it. The purpose of this plugin is to boil down large documents to consumable sizes for a PHP-based web server.

## How it works
Document Search exists to augment the exisitng Craft CMS search index. To do this, we want to avoid polluting it with large amount of data, so we will extract only the most important parts.

When a PDF is saved as an asset and the Volume is configured to be searched, the textual content will be extracted by the pdftotext executable. First, this content is sanitized and normalized with "stop words" removed. Stop words are essentially non-useful words such as "and". Stopwords are selected based on the Asset's locale language (with fallback to english.) This content is then processed into the top 30 (30 of each) 1-gram, 2-gram, and 3-grams. In this scenario, 1-grams are going to simply be most commonly occuring words. Two and three grams are going to help prioritize exact phrase matches. For example, processing the Wikipedia page for "cats" yields the following search keywords:
```
cats cat domestic archived original retrieved pmid species feral journal isbn september october animal humans prey animals acid new pdf felis november human veterinary behavior small august press january person diseased ranging hectares housecats range range hundreds hundreds meters meters central central point synonymia et cvm synonymia establish territories territories vary vary considerably considerably size varietates cvm historia animalivm regni animalis oclc erxleben laurentii salvii lying rice rice straw holmiae laurentii tenth reformed energy sleeping synonymis locis differentiis synonymis especially grow et historia time vicinity older daily liberg sandell pads pant heat suggests aredreaming sociability lighted great pomp fires lighted great experience short periods short periods rapid periods rapid eye rapid eye movement eye movement sleep movement sleep accompanied sleep accompanied muscle accompanied muscle twitches muscle twitches suggests twitches suggests aredreaming aredreaming sociability wildcats brief period asleep sociability wildcats solitary midsummer fires lighted metz midsummer fires paris metz midsummer bonfire paris metz midsummer bonfire paris presided midsummer bonfire monarch presided midsummer occasion monarch presided hall occasion monarch variable ranges widely ranges widely dispersed widely dispersed individuals great pomp esplanade
```

Notice the first 30 keywords are things like: cats, cat, species, domestic however they do not have any contextual relation to their adjacent keywords. As we move down the list, you'll notice short phrases that someone might search for to yield a more exact match, such as: "central point", "eye movement", "widely dispersed inviduals", "great pomp esplanade"

These 2 and 3 grams are not simply based on their number of occurrences but are actually derived by a process known as Rapid Automatic Keyword Extraction (RAKE) to infer the importance based on the words in them.

## Document Search Usage

Once installed and configured (see configuration section), PDF assets with text in them (does not work on PDFs with images, such as scans) will be indexed automatically.

Like other fields in Craft, you may tweak the search query to your liking by targeting the field named `contentkeywords`

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
