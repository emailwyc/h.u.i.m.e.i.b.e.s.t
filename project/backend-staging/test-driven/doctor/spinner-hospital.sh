#!/bin/bash

url='/spinner/hospital'

json=$(cat <<JSON
{
    "keywords": null
}
JSON
)

./curl.sh "$json" "$url"
