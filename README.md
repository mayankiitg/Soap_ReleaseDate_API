# Soap_ReleaseDate_API
#####1.Api to get release date of any TV Shows.
2.Require php server.
3.in API.php replace127.0.0.1:3128 by proxy credentials (if required).
4.How to USE:
place this API.php file in /var/www/html folder or htdocs folder and start apache server and u can give url in browser to get api result in json format.
TV_SHOW_CODE is code of that show in www.epguides.com
format of url =>   localhost/filename.php/soap/TV_SHOW_CODE/season_number/episode_number
To get a list of episodes airing today use the url: localhost/API.php/soap/today
