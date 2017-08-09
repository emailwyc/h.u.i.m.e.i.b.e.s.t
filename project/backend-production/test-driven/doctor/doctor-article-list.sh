#!/bin/bash

url='/doctor/article/list'

json=$(cat <<JSON
{
    "since_article_id": "$1"
}
JSON
)

./curl.sh "$json" "$url"
