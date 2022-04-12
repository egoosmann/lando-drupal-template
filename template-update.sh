#!/bin/bash

echo "Hi"


awk '/template_version:/{print $NF;flag=""}' .lando.yml
