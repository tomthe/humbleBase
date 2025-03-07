# SimpleBase idea
This project's goal is to build a universal backend with PHP, SQLite.
Developers can put this on a shared hosting and only need to configure it from the frontend.

## How it works
The clients sends data (json in http-post request) to the URL of the php script and adds a random token as URL parameter to it.
The backend checks if an SQLite database with the name of the token (slightly modified, prevent injection vulnerabilities) already exists and adds the data to it.
The client can also request data and the backend will look for the correct database, query the data and send it back. Updating is also possible.
Creating a database is simple: call the create script with some simple arguments and a token.

## Code structure

createDB.php
    - user can create a db by making a request like {query:"createTable", tablename:"tab1", columns:[{"cname":"tname", "type":"VARCHAR"},"amount",{cname:"year",type:"Integer"},"time1"]}
writeData.php
    - user can send a request like: {"query":"newRow", "tablename":"tab1", newdata:{cname:"tname",value:"NewName23"},{cname:"year",value:2019}} and a token
    - user can also send a list in newdata
    - the script replies either with success or with an informative error like "token not valid", or "tab1 is not a table"
getData.php
    - user can send a request like: {"query":"getall", "tablename":"tab1"} and get the whole table. Or {query:getwhere, tablename:tab1,where:"bla>4", columns:["tname","amount","year"]} and a token
    - user will get json with the data back. Or an informative error
updateData.php
    - user can send a request like: {"query":"updateRow", "tablename":"tab1", where:"year=2019", newdata:{cname:"tname",value:"NewName23"}}
admin.php
    - with the correct token, it shows an admin view of the corresponding database.

There is also a folder "tests" which contains some tests for all these functions.