
# youtube-convertor
------
Youtube-convertor is powerful, full-featured convertor for youtube videos, you can get compatiable common media files, such as mp4, mp3, etc. You can also set the video frame speeds or audio bitrates. 

## 1. Install youtube-dl:

> sudo apt install python

**Notice:** `python3` is NOT supported by `youtube-dl` so far.

> https://github.com/rg3/youtube-dl#installation

## 2. Install php-curl

> sudo apt install php-curl

You will need to restart the server afterwards:

> sudo service apache2 restart

Alternatively, if you are using php-fpm, you'll need to restart php5-fpm instead

> sudo service php5-fpm restart

## 3. Install ffmpeg

> sudo apt install ffmpeg

## 4. Create store path

> mkdir -p STORE_PATH 
> chmod 777 STORE_PATH

## 5, Create symbol link for store path in source code

> ln -s STORE_PATH files

## 6. Add config file

> vi config.ini

    ## Path
    
    # youtube-dl path
    env-path=
    
    # Media files store path
    save-path=
    
## 7. Link to www

> ln -s SOURCE_PATH /var/www/html/youtube-convertor
