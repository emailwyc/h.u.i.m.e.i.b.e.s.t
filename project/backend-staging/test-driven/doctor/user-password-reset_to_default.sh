#!/bin/bash

url='/user/password/reset_to_default'

json=$(cat <<JSON
{
    "token": "zDCI4drA-7PtH5goY-GfD1BCEc-E05jpprq-P2St"
}
JSON
)

./curl.sh "$json" "$url"
