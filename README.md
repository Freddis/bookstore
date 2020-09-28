## Installation

1. Add configuration to .env file
<pre>
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookstore
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120
</pre>

2\. Create database **bookstore** 

3\. Install php 7.3, composer and node

4\. Bootstrap the project
<pre>
composer install
npm install
npm run dev
php artisan migrate
</pre>
5\. Run queue 
<pre>
php artisan queue:listen --timeout=0 #the task can take few hours, since all the downloading
</pre>

6\. Go to homepage and upload xml file


**NOTE:** The app has been tested with memory_limit **120M**. With **50M** it can choke on image resizing.

## Thoughts

One of the most interesting thing to figure out was the uploading fo huge files. I decided to use streams, 
since increasing **upload_file_size** or **memory_limit** can make the project more vulnerable to attacks.

Another advantage is that **it will work straight from the box**, slowly but surely. I viewed it as a part of the task, 
since otherwise you could attach a smaller file. Honestly I have no idea on what server it will be tested and i decided to 
develop as safely as possible.

I know it was not a part of the task, but I also implemented **job tacking**. When you upload the xml file you can see how 
many products already have been added and you will see when job is done or if it has failed.

For images I decided to first resize them first and then resize otherwise the **aspect ratio** will be wrong.
