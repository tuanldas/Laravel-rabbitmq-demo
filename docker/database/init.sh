#!/bin/bash
set -e

# Kiểm tra nếu database chưa tồn tại thì tạo
psql -U "$POSTGRES_USER" -tc "SELECT 1 FROM pg_database WHERE datname = '$POSTGRES_DB'" | grep -q 1 || \
    psql -U "$POSTGRES_USER" -c "CREATE DATABASE \"$POSTGRES_DB\";"
