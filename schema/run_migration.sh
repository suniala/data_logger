#!/bin/bash

database=$1
migration=$2

sqlite3 -init $migration $database
