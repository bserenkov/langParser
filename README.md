# langParser

There are set of tools which help to manage language files.
Tools which are used:
* exporter.phar
* importer.phar
* TODO : ~~jira.phar~~ 

# Installation
1. git clone git@github.com:mkardakov/langParser.git myfolder/
2. 
```bash
cd myfolder/ && \
    chmod o+x importer.phar && \
    ln -s `pwd`/importer.phar /usr/local/bin/importer 
```    

# Build process
Once you want to contribute you need to rebuild a new phar script. php-phar package must be installed be proceed!
to Re-build exporter script:
```bash
  cd  myfolder/ && \
  rm exporter.phar && \
  php build.php exporter && \
   chmod o+x exporter.phar
```
    

## Exporter
  Extracts current git diff of **en/str_**\* files into export sheet file (CSV or XLSX).
  XSLX is preffered and will be used if client machine contains [libreoffice](https://www.libreoffice.org/download/download/) tool otherwise .csv file will be generated.
  Path to the libreoffice binary must be filled at parser.ini:
```ini
    [libreoffice]
    path="/usr/bin/libreoffice"
```            
Once export sheet generated it can be added to the ticket and assigned on an interpreter user
#### Depends:
* git
* php \>= 7.0
* libreoffice
#### Example: 
  cd your/app/folder && /path/to/exporter.phar sprint_17-8-1 
  
  compare diff between local copy and origin/sprint_17-8-1

## Importer
  Injects translated labes into French **fr/str_**\* translation files. XSLT and CSV sheets are allowed only.
  TODO: does not process correctly lines with multiple variables, like: __strtable = strRecord = 'some text'__ 
          
#### Depends:
* php \>= 7.0
* libreoffice

#### Example: 
  cd your/app/folder && /path/to/importer.phar /path/to/importsheet.xlsx 
  
  Applies translations to the French labels.
  * str_\* file will be created if not yet exist. 
  * If label key was found than the new text will replace old one on the same line  
  * If label key was **NOT** found than the new text will be append at the end of the file
    
    

