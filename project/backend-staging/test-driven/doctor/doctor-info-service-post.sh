#!/bin/bash

url='/doctor/info/service_provided'

json=$(cat <<JSON
{
    "service_provided": {
        "clinic": {
            "on": true,
            "price": 800
        },
        "consult": {
            "on": true,
            "price": 600
        },
        "phonecall": {
            "on": true,
            "price_05": 50,
            "price_10": 100,
            "price_15": 150,
            "price_20": 200
        }
    }
}
JSON
)

./curl.sh "$json" "$url"
