#!/bin/bash

url='/user/noop'

json=$(cat <<JSON
{
    "noop": "ping"
}
JSON
)

./curl.sh "$json" "$url"
