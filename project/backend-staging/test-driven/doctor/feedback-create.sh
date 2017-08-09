#!/bin/bash

url='/feedback/create'

json=$(cat <<JSON
{
    "content": "中文 abc ABC"
}
JSON
)

./curl.sh "$json" "$url"
