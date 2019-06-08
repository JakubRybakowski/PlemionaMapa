#!/bin/bash
cd /var/www/html
for i in {1..100}
do
	php-cgi map.php id=$i save=1 big=1 noprint=1
done
ffmpeg -i img/%d.png -vf palettegen palette.png -y
ffmpeg -v warning -i img/%d.png -i palette.png -lavfi "paletteuse,setpts=8*PTS" -y out.gif 
chown -R www-data:www-data /var/www/html/img/
