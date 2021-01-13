<?php

return array(
    // catatan buat linux
    // 1. composer install dlu buat install wkhtmltopdf di folder vendor
    // 2. cp vendor/h4cc/wkhtmltoimage-amd64/bin/wkhtmltoimage-amd64 /usr/local/bin/
    //      cp vendor/h4cc/wkhtmltopdf-amd64/bin/wkhtmltopdf-amd64 /usr/local/bin/
    //      chmod +x /usr/local/bin/wkhtmltoimage-amd64
    //      chmod +x /usr/local/bin/wkhtmltopdf-amd64
    // 3. 'pdf'  => '/usr/local/bin/wkhtmltopdf-amd64',
    //      'image' => '/usr/local/bin/wkhtmltoimage-amd64'

    // catatan utk windows
    // jgn lupa install wkhtmltopdf pilih exe yg versi windows vista ke atas >> https://wkhtmltopdf.org/downloads.html baru kmdn samain kyk di config sesuai path exe nya

    'pdf' => array(
        'enabled' => true,
        // 'binary'  => '"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"',
        'binary'  => '/usr/local/bin/wkhtmltopdf-amd64',
        'timeout' => false,
        'options' => array(),
        'env'     => array(),
    ),
    'image' => array(
        'enabled' => false,
        'binary'  => '/usr/local/bin/wkhtmltoimage-amd54',
        'timeout' => true,
        'options' => array(),
        'env'     => array(),
    ),


);
