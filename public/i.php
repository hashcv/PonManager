<?php
phpinfo();

if  (in_array  ('curl', get_loaded_extensions())) {
    echo "cURL is installed on this server";
}
else {
    echo "cURL is not installed on this server";
}