#!/bin/bash

host='http://127.0.0.1:8089/storage'
# host='http://api-staging.huimeibest.com/storage'

/usr/bin/curl --silent -i --form 'attachment=@./demo-image.png' $host/object/upload
