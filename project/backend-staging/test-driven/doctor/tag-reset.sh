#!/bin/bash

url='/tag/reset'

json=$(cat <<JSON
{
    "force": true
}
JSON
)

./curl.sh "$json" "$url"
