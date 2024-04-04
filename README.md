# Author Listing

This plugin creates a new page for a journal designed to list all of the authors and link to search results for said author.

OJS at some point had an authors page but it was apparently built with really complex queries and Google wasn't too happy about this
when the author listing page was slower than the rest and was removed. So, we built our own.

It creates a new table `journal_authors` which contains a cache of a unique value, a singular author ID to use for information and
the journal ID they belong to.

The unique value is calculated by using preferrably the author's ORCID and falls back towards their email address.

License TBC

## Author

Joe Simpson @ Invisible Dragon on behalf of OICC Press
