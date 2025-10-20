#!/bin/bash

echo ""
echo "📊 Running PHPUnit Tests..."
composer test

echo ""
echo "🔍 Running Static Analysis..."
composer stan
