#!/bin/bash

url='/user/sign_up'

json=$(cat <<JSON
{
    "mobile": "13810261155",
    "password": "password",
    "captcha": "123456"
}
JSON
)

./curl.sh "$json" "$url"
