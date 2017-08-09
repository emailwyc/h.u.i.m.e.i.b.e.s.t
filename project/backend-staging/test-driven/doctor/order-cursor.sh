#!/bin/bash

url='/order/cursor'

json=$(cat <<JSON
{
    "since_order_id": "",
    "operator": "<"
}
JSON
)

./curl.sh "$json" "$url"
