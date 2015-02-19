#!/bin/bash

# Package the Maesrtrano custom modules
php maestrano/modules/export_modules.php
mv test/vtlib/*.zip maestrano/modules/
