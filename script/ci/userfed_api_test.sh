#!/bin/bash

if [ $# -ne 8 ]; then
	echo "Usage: $0 SERVER_ADDRESS WS_SERVER_ID WS_SERVER_IP SERVER_ID APPLICATION_ID USER_LOGIN USER_EMAIL INVITATION_TOKEN

Note that you may need to create a server manually in the web front end.

Example: $0 http://userfed.hometradies.com f100d8c5c68684f4770ba66bf90be2c9 123.123.123.123 5 7 username user_email@example.com fjncjdjwskkf32dfw"
	exit 1
fi

SERVER_ADDRESS=$1
WS_SERVER_ID=$2
WS_SERVER_IP=$3
SERVER_ID=$4
APPLICATION_ID=$5

# not all testcase use USER_LOGIN, USER_EMAIL, INVITATION_TOKEN, only create user test case use
USER_LOGIN=$6
USER_EMAIL=$7
INVITATION_TOKEN=$8

CODE=0

test_title ()
{
	echo -e "\e[1;33m$TEST_TITLE\e[0m"
}

test_subtitle ()
{
	echo -e "\e[33m$TEST_SUBTITLE\e[0m"
}

pass ()
{
	echo -e "\e[1;32mPass\e[0m"
}

fail ()
{
	echo -e "\e[1;31mFail\e[0m"
	CODE=1
}

TEST_TITLE="Test that we can communicate with the server" test_title
URL=$SERVER_ADDRESS
TEST_SUBTITLE="Sending GET request to $URL" test_subtitle
RESPONSE=$(curl "$SERVER_ADDRESS")
[ $? -eq 0 ] && pass || fail

TEST_TITLE="Test that we can create an organisation" test_title
URL="$SERVER_ADDRESS/api/org/create"
DATA="WS_server_id=$WS_SERVER_ID&WS_server_ip=$WS_SERVER_IP&organisation_name=Truckworld"
TEST_SUBTITLE="Sending POST request to $URL with data $DATA" test_subtitle
RESPONSE=$(curl --data "$DATA" "$URL")
[ $? -eq 0 ] && pass || fail
TEST_SUBTITLE="Got the following response" test_subtitle
echo "$RESPONSE"
TEST_SUBTITLE="Checking result was 1" test_subtitle
echo "$RESPONSE" | grep '\"result\":1' && pass || fail

TEST_TITLE="Test that the created organisation exists" test_title
URL="$SERVER_ADDRESS/api/org/exist?organisation_name=Truckworld&WS_server_id=$WS_SERVER_ID&WS_server_ip=$WS_SERVER_IP"
TEST_SUBTITLE="Sending GET request to $URL" test_subtitle
RESPONSE=$(curl "$URL")
[ $? -eq 0 ] && pass || fail
TEST_SUBTITLE="Got the following response" test_subtitle
echo "$RESPONSE"
TEST_SUBTITLE="Checking result was 1" test_subtitle
echo "$RESPONSE" | grep '\"result\":1' && pass || fail

TEST_TITLE="Test that we can create a user" test_title
URL="$SERVER_ADDRESS/api/user/create"
DATA="WS_server_id=$WS_SERVER_ID&WS_server_ip=$WS_SERVER_IP&organisation_name=Truckworld&user_login=$USER_LOGIN&user_password=asdfghjk&user_email=$USER_EMAIL&application_id=$APPLICATION_ID&server_id=$SERVER_ID&invitation_token=$INVITATION_TOKEN"
TEST_SUBTITLE="Sending POST request to $URL with data $DATA" test_subtitle
RESPONSE=$(curl --data "$DATA" "$URL")
[ $? -eq 0 ] && pass || fail
TEST_SUBTITLE="Got the following response" test_subtitle
echo "$RESPONSE"
TEST_SUBTITLE="Checking result was 1" test_subtitle
echo "$RESPONSE" | grep '\"result\":1' && pass || fail

if [ $CODE -eq 0 ]; then
	echo -e "\e[1;32mAll tests passed\e[0m"
else
	echo -e "\e[1;31mThere were failures during test running\e[0m"
fi
exit $CODE
