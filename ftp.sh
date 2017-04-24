lftp $FTP_HOST -u $FTP_USER,$FTP_PASSWORD -e "cd warehouse; pwd; rm -r deploy; mkdir deploy; cd deploy; pwd; lcd dist; mirro -R; ls; exit"
