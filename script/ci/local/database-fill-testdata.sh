cd ../../..
# APPLICATION_ENV=development php code/public_html/index.php mock data --orgcount=20 --appcount=30
# APPLICATION_ENV=development php code/public_html/index.php mock data --xss --orgcount=20 --appcount=30
# APPLICATION_ENV=development php code/public_html/index.php mock user --usercount=20
# APPLICATION_ENV=development php code/public_html/index.php mock user --xss --usercount=20
APPLICATION_ENV=development php code/public_html/index.php mock data --orgcount=2 --appcount=2
APPLICATION_ENV=development php code/public_html/index.php mock user --usercount=200