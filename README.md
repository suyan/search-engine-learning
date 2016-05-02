# search-engine-learning
2016 Spring CSCI 572 homework

PS: All codes listed only tested in Mac 10.11.

## Homework 2

HW2 is to use a crawler4j to crawl the pages of a school of University of Southern California and generate the statistic of those pages.

Output file are located in /tmp/sycralwer:
- CrawlReport.txt statistic of pages
- pagerank.csv page rank data for HW3
- visit.csv pages that have been visited
- fetch.csv pages that crawler has tried to fetch
- urls.csv all urls that has been found
- files/ all fetched files (html, pdf and doc/docx)

## Homework 3

HW3 is to use solr to index and search the pages fetched in HW2 and compare pagerank algorithms.

We use a Python library named networkx to compute page rank and use PHP to request search result from solr. So in this folder, there are two sub folders name python and PHP.

## Homework 4

Implement spell check and spell correction in search input field. Spell check is implemented by SpellCorrector PHP library(fixed several bugs), spell correction is powered by solr's suggestor(check the solr config file).




