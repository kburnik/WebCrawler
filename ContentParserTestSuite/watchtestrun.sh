#! /bin/bash
touch temp.txt
touch last.txt
while true; do
	sleep 1
	
	php testrun.php > temp.txt
	cmp temp.txt last.txt 1> /dev/null 2> /dev/null && continue;
	clear;
	cat /proc/loadavg
	cat temp.txt | head -n 25
	cp temp.txt last.txt	
done;

