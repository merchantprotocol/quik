```
============================================================================= 
 Gzip
============================================================================= 
 Usage: quik gzip [options]
 Gzip is a great tool for sending files to a client that are compressed. This
 reduces the size of the file and the download time of your webpages. While
 Gzip is fast it will slow down your server when you're serving 100 files every
 page load for every visitor. To reduce the compression time and reduce the server
 load you can precompress your files. Then tell Apache to serve the precompressed files
 before attempting the non-compressed originals.

 Options:
  -h, --help          Show this message
  -y                  Preapprove the confirmation prompt.
```

### After you run this command you will need to update your .htaccess file.
```
# AddEncoding allows you to have certain browsers uncompress information on the fly.
AddEncoding gzip .gz

#Serve gzip compressed CSS files if they exist and the client accepts gzip.
RewriteCond %{HTTP:Accept-encoding} gzip
RewriteCond %{REQUEST_FILENAME}\.gz -s
RewriteRule ^(.*)\.css $1\.css\.gz [QSA]

# Serve gzip compressed JS files if they exist and the client accepts gzip.
RewriteCond %{HTTP:Accept-encoding} gzip
RewriteCond %{REQUEST_FILENAME}\.gz -s
RewriteRule ^(.*)\.js $1\.js\.gz [QSA]

# Serve correct content types, and prevent mod_deflate double gzip.
RewriteRule \.css\.gz$ - [T=text/css,E=no-gzip:1]
RewriteRule \.js\.gz$ - [T=text/javascript,E=no-gzip:1]
```