<b>Cubicfox Phalcon PHP demo project (My first Phalcon project)</b>
<br>
Requirements:
<ul>
<li>build a REST API</li>
<li>full framework, not micro, but no userinterface needed</li>
<li>no real user authentication (eg. login) needed, current client user id sent in header (cubicfox-user)</li>
<li>tables: products(id, user_id, code, name, description, price), users(id, name, email), rates(user_id, product_id, rating)</li>
</ul>
<br>
API endpoints (responses are in JSON format, PUT/POST requests parameters in the request body in JSON):
<ul>
<li>GET: /products
<br>
Full product list with average user rating of the product, pagable, filterable. Page size is 2 entries. (for easy testing). Parameters in url. Default sorting is by name, default direction is ASC. (?filter=laptop&page=2&sort=price&direction=asc)</li>
<li>GET: /product/{id}
<br>
Detailed product info for the given (id) product, all user ratings for the product is included</li>
<li>PUT: /product/{id}
<br>
Update product (by id), but only if the current user (cubic-user in header) is the owner. Parameters (fields) in the requests body in JSON</li>
<li>POST: /rate/{ProductId}
<br>
Save the current user (cubic-user in header) rating (1-10 in json in the request body) for the given product (url id)</li>
</ul>
<br>
API can be tested eg. with curl (eg. at localhost:8000), like this:
<br>
<ul>
<li>curl -v -H 'cubicfox-user:1' -X GET http://localhost:8000/products</li>
<li>curl -v -H 'cubicfox-user:1' -X GET "http://localhost:8000/products?filter=product&sort=price&direction=asc&page=1"</li>
<li>curl -v -H 'cubicfox-user:1' -X GET http://localhost:8000/product/1</li>
<li>curl -v -H 'cubicfox-user:1' -H 'Content-Type: application/json' -X PUT -d '{"code":123, "name":"product123", "description":"product123 description", "price":4.5}' http://localhost:8000/product/1</li>
<li>curl -v -H 'cubicfox-user:1' -H 'Content-Type: application/json' -X POST -d '{"rating":8}' http://localhost:8000/rate/1</li>
</ul>
<br>
<br>
Example database dump at ./resources/sql/cubicfox_dump.sql
<br>
<br>
Also made docker images for easy testing (with imported example database). Usage (needs docker to be installed):
<ul>
<li>docker-compose build</li>
<li>docker-compose up</li>
</ul>
