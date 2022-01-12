@echo off

set one=%1

git add --all
git commit -a -m %one%
git push -u origin master