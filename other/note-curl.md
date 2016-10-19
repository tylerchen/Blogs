CURL Use
====

#### post file

curl -H "Content-Type: multipart/form-data" -X POST -F 'AuthAccountVO=@/Users/zhaochen/Desktop/AuthAccountVO.json;type=application/json' -F 'Page=@/Users/zhaochen/Desktop/Page.json;type=application/json' http://localhost:8080/qdp/webservice/rest/authAccountApplication/pageFindAuthAccount

#### post multi form

curl -H "Content-Type: multipart/form-data" -X POST -F 'AuthAccountVO={};type=application/json' -F 'Page={"pageSize":10,"totalCount":0,"currentPage":1,"offset":0,"offsetPage":false,"rows":[],"orderBy":[]};type=application/json' http://localhost:8080/qdp/webservice/rest/authAccountApplication/pageFindAuthAccount

#### post multi from by jquery

    <!DOCTYPE html>
    <html debug="true">
    <head>
    <meta charset="UTF-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="QDP" />
    <title>Title</title>
    <script src="/qdp/resource/js/jquery-1.11.1.js"></script>
    </head>

    <body>
      <form action="http://localhost:8080/qdp/webservice/rest/authAccountApplication/pageFindAuthAccount" id="test">
        Page:<textarea rows="5" cols="100" name="Page" id="Page"></textarea><br/>
        AuthAccountVO:<textarea rows="5" cols="100" name="AuthAccountVO" id="AuthAccountVO"></textarea>
        <button onclick="test();return false;">submit</button>
      </form>
      <div id="serverResponse"></div>
    </body>
    <script type="text/javascript">
    function test(){
      var data = new FormData();
      data.append("Page",$('#Page').val());
      data.append("AuthAccountVO",$('#AuthAccountVO').val());
        $.ajax({  
            url : "http://localhost:8080/qdp/webservice/rest/authAccountApplication/pageFindAuthAccount",  
            cache: false,
            contentType: false,
            processData: false,
            type: 'POST',
            data: data,
            success : function(data) {  
                 $( '#serverResponse').html(JSON.stringify(data));  
            },  
            error : function(data) {  
                 $( '#serverResponse').html(data.status + " : " + data.statusText + " : " + data.responseText);  
            }  
       });  
    }
    </script>
    </html>
