#!/bin/bash

url='/doctor/security_code/check'

json=$(cat <<JSON
{
    "security_code": "68b329da9893e34099c7d8ad5cb9c940"
}
JSON
)

./curl.sh "$json" "$url"
