#!/usr/bin/sh

# Install all NPM Things
npm install

# Grunt
grunt publish

chown -R wwwrun.www ./

# Copy fonts
rm ../../../web/dist/Theme/Default/fonts
ln -s ../../../../src/Themes/Default/src/Themes/Default/SCSS/fontawesome/4.7.0/fonts ../../../web/dist/Theme/Default/fonts
