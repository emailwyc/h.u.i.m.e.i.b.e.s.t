#!/bin/bash

url='/template/create'

if [[ $# -lt 1 ]]; then
    echo "usage: $0 content"
    exit 1
fi

json=$(cat <<JSON
{
    "content": "$1"
}
JSON
)

./curl.sh "$json" "$url"
