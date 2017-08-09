#!/bin/bash

url='/order/history'

json=$(cat <<JSON
{
    "service": "phonecall",
    "sort": "desc",
    "start_time": "2016-01-01 08:00:00",
    "end_time": "2016-01-20 08:00:00"
}
JSON
)

./curl.sh "$json" "$url"
