#!/bin/bash

url='/order/voip/calling'

json=$(cat <<JSON
{
    "id": "$1"
}
JSON
)

./curl.sh "$json" "$url"
