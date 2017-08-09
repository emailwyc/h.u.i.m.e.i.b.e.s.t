#!/bin/bash

url='/spinner/price'

json=$(cat <<JSON
{
    "service": "clinic"
}
JSON
)

./curl.sh "$json" "$url"
