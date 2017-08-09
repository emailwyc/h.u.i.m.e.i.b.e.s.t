#!/bin/bash

url='/doctor/info/description'

json=$(cat <<JSON
{
    "description": "这是描述内容"
}
JSON
)

./curl.sh "$json" "$url"
