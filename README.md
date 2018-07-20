# Web-Search-Engine

## Front-End
* Created a single web page by used HTML and PHP. 

* Implemented autocompleted keywords function, spell corrector function, and displayed
the search results and snippets of webpage.

## Back-End
* CoundWord.java is to implements spell corrector function

* Simple_html_dom.php is to get the content of corresponding html file and match with keyword query

* Included [PageRank](#jump) result file into Apache Solr to implements the sort function

<span id="jump">Compute Pagerank</span>
```python
    import networkx as nx
    G = nx.read_edgelist("./edgelist.txt",create_using=nx.DiGraph()) // create directed graph
    pr = nx.pagerank(G,alpha=0.85,personalization=None, max_iter=30, tol=1e-06, nstart=None, weight='weight',dangling=None) //computer pagerank.
```
