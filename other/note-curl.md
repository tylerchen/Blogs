CURL Use
====

#### post file

curl -H "Content-Type: multipart/form-data" -X POST -F 'AuthAccountVO=@/Users/zhaochen/Desktop/AuthAccountVO.json;type=application/json' -F 'Page=@/Users/zhaochen/Desktop/Page.json;type=application/json' http://localhost:8080/qdp/webservice/rest/authAccountApplication/pageFindAuthAccount

#### post multi form

curl -H "Content-Type: multipart/form-data" -X POST -F 'AuthAccountVO={};type=application/json' -F 'Page={"pageSize":10,"totalCount":0,"currentPage":1,"offset":0,"offsetPage":false,"rows":[],"orderBy":[]};type=application/json' http://localhost:8080/qdp/webservice/rest/authAccountApplication/pageFindAuthAccount
