#!/bin/bash

url='/order/unread'

json=$(cat <<JSON
{
    "clinic_order_id": "$1",
    "phonecall_order_id": "$2"
}
JSON
)

./curl.sh "$json" "$url"
