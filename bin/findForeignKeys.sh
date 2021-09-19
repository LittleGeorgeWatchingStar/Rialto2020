#!/bin/bash

table=$1
if [[ ! $table ]]; then
    echo "Usage: $0 table_name"
    exit
fi

mysql -vvv --execute "select CONSTRAINT_NAME, TABLE_NAME, UPDATE_RULE, DELETE_RULE from information_schema.REFERENTIAL_CONSTRAINTS where REFERENCED_TABLE_NAME = '$1'"
