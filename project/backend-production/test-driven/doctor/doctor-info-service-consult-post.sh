#!/bin/bash

url='/doctor/info/service_provided/consult'

json=$(cat <<JSON
{
    "service_provided": {
        "consult": {
            "on": true,
            "price": 600
        }
    }
}
JSON
)

./curl.sh "$json" "$url"
