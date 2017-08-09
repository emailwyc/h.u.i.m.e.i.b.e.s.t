#!/bin/bash

url='/user/sign_in'

json=$(cat <<JSON
{
    "mobile": "13810261155",
    "captcha": "2015"
}
JSON
)

x=$(./curl.sh "$json" "$url")

token=$(echo "$x" | /bin/grep -P -o '"session_token": ".*?"' | xargs | awk '{print $2}')
echo $token > token
echo -n "$x"
