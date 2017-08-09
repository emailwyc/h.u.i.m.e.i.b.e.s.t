#!/bin/bash

url='/release/latest'

json=$(cat <<JSON
{
    "device_type": "android"
}
JSON
)

./curl.sh "$json" "$url"
