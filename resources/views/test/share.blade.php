<html>
    <head>
        <link rel="stylesheet" href="style.css" />
        <title>Facebook Share Web Example</title>
        
    </head>

    <body>
        
        <h1>Facebook Share using Web - JavaScript</h1>
        
        <div
            class="fb-share-button"
            data-href="{{ $url }}"
            data-layout="button_count"
            data-size="small"
            data-quote="This is a test"
            data-display="page"
            data-mobile-iframe="true">
                <a
                    target="_blank"
                    href="{{ $url }}"
                    class="fb-xfbml-parse-ignore">
                        Share
                </a>
            </div>
            
            
        <div id="fb-root"></div>
    </body>
        

    
    <script>
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = 'https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.0&appId={{ ENV('FACEBOOK_APP_ID') }}&autoLogAppEvents=1';
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
</html>