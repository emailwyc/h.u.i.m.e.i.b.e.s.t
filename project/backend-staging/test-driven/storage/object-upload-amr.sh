#!/bin/bash

host='http://127.0.0.1:8089/storage'
# host='http://api-staging.huimeibest.com/storage'

/usr/bin/curl --silent -i --form 'attachment=@./demo-voice.amr' $host/object/upload
