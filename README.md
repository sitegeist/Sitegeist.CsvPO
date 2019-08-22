# Sitegeist.CsvPO

## Neos package to colocate translations in csv files with fusion-components

This package allows to manage translations that as csv-files that are 
managed part of the component folder. This allows to keep translation
identifiers short, makes it very eass to add new translations and allows 
to move/remove translations with their components. In addition csv-files 
can be managed by regular spreadsheet applications.

NOTE: Using xliff was rejected because it requires multiple files, makes 
it exceptionally hard to spot missing translations and has very weak 
tooling support 

## Authors & Sponsors

* Martin Ficzel - ficzel@sitegeist.de

*The development and the public-releases of this package is generously sponsored 
by our employer http://www.sitegeist.de.*

## Usage

The CsvPO helper provides the eel function `CsvPO.create` that expects 
the path to the translation csv file as argument. The returned object 
allows to access the translation keys. 

Example.fusion
```
prototype(Vendor.Site:Example) < prototype(Neos.Fusion:Component) {
    @context.translations = ${CsvPO.create('resource://Vendor.Site/Private/Fusion/Presentation/Example.translation.csv')}

    renderer = afx`
        <div>
            {translations.title} <br/>
            {translations.text({info:'foo'})}<br/>
            {translations.missing}
        </div>
    `
}
```

Example.translation.csv
```
id, en, de
title, title, Titel
text, text with {info} placeholder, Text mit {info} Platzhalter
```

Rules:
- The first row in the csv provides the locale. 
- The first column the identifiers. Passing arguments to the
- To access translations just use the intended key on CsvPO object.
- To apply arguments you can call the key as function and pass an object or array. 
- CsvPO will use the current locale and if this is not found if traverses up the configured localisation fallback chain
- Missing translations will return "i18n(add/translate __key__)" instead.

### Installation 

Sitegeist.CsvPO is available via packagist. Just run `composer require sitegeist/csvpo`.
We use semantic-versioning so every breaking change will increase the major-version number.
