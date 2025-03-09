# humbleBase

The simplest universal backend possible... almost.

humbleBase consists only of around 4 php files. Installation: copy them to your shared hosting provider. Configuration of the backend: not necessary, you can do everything from the frontend.

### What is this? How does it work?

These are just 4 php scripts that create, access or update SQLite files. Authentication is done via random tokens: These are the filenames of these databases. There are no usernames or email addresses. With the correct token, clients can save or modify anything in these databases. Without, they can't.



#### createDB
clients can create a db by making a request like 
```
{query:"createTable", tablename:"tab1", columns:[{"cname":"tname", "type":"VARCHAR"},"amount",{cname:"year",type:"Integer"},"time1"]} 
```
(columns default to VARCHAR type)

#### writeData
clients can send a request like: {"query":"newRow", "tablename":"tab1", newdata:{cname:"tname",value:"NewName23"},{cname:"year",value:2019}} and a token 
    - clients can also send a list in newdata
    - the script replies either with success or with an informative error like "token not valid", or "tab1 is not a table"

#### getData
clients can send a request like: `{"query":"getall", "tablename":"tab1"}` and get the whole table. Or `{query:getwhere, tablename:tab1,where:"bla>4", columns:["tname","amount","year"]}` and a token.

The client will get json with the data back. Or an informative error.

#### updateData
clients can send a request like: 
```
{"query":"updateRow", "tablename":"tab1", where:"year=2019", newdata:{cname:"tname",value:"NewName23"}}
```
to modify rows in the database.


#### admin view
With the correct token, it shows an admin view of the corresponding database. It is possible to view and modify the whole database from there.
