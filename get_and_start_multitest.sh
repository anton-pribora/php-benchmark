#!/usr/bin/env bash

WGET=$(which wget)
CURL=$(which curl)

if [[ -x $WGET ]]; then
    LOADER="$WGET -qO-"
fi

if [[ -x $CURL ]]; then
    LOADER="$CURL -s"
fi

if [[ -z $LOADER ]]; then
    echo The script needs wget or curl utility. Please install one of them and try adain. >&2
    exit -1
fi

MULTITEST=/tmp/multitest.php
BENCHMARK=/tmp/benchmark.php

[[ -f $MULTITEST ]] || ($LOADER https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/multitest.php > $MULTITEST)
[[ -f $BENCHMARK ]] || ($LOADER https://raw.githubusercontent.com/anton-pribora/php-benchmark/master/benchmark.php > $BENCHMARK)

chmod +x $MULTITEST

$MULTITEST $@ | column -ts :

# rm $MULTITEST $BENCHMARK