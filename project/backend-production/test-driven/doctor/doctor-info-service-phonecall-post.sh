#!/bin/bash

url='/doctor/info/service_provided/phonecall'

json=$(cat <<JSON
{
    "service_provided": {
        "phonecall": {
            "on": true,
            "price_05": 500,
            "price_10": 1000,
            "price_15": 1500,
            "price_20": 2000
        }
    }
}
JSON
)

./curl.sh "$json" "$url"
