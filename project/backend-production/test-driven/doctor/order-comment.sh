#!/bin/bash

url='/order/comment'

json=$(cat <<JSON
{
    "since_comment_id": "$1"
}
JSON
)

./curl.sh "$json" "$url"
