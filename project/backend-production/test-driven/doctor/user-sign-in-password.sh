#!/bin/bash

url='/user/sign_in'

json=$(cat <<JSON
{
    "mobile": "13810261155",
    "password": "3de8cefdffe3cc1c8bd45ada6c417bdc"
}
JSON
)

# "mobile": "13810261155",
# "password": "3de8cefdffe3cc1c8bd45ada6c417bdc"
# "mobile": "18513852351",
# "password": "343b1c4a3ea721b2d640fc8700db0f36"

x=$(./curl.sh "$json" "$url")

token=$(echo "$x" | /bin/grep -P -o '"session_token": ".*?"' | xargs | awk '{print $2}')
echo $token > token
echo -n "$x"
