#!/bin/bash

url='/spinner/quantity'

json=$(cat <<JSON
{
    "service": "clinic"
}
JSON
)

./curl.sh "$json" "$url"
