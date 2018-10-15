<?php

#
#
# FL.RU SENDER BOT
#
# (c) Doc-sayme
#
# For the full license information, view the LICENSE file that was distributed
# with this source code.
#
#

class freelance_bot_sender 
{   
    const version = '0.1';

    # "LOGIN" for autorisation in fl.ru
        public $login = '';

    # "PASSWORD" for autorisation in fl.ru
        public $password = '';

    # URL-adress to form login
        private $url_loginform = 'https://freelance.ru/login/';

    # URL-adress projects
        private $url_projects = 'https://freelance.ru/projects/';
    
    # URL for comment
        private $url_comment = 'https://freelance.ru/discussion/?cmd=msg_new&topic=';

    # Cookies autorisation data
        public $session_cookies = '';

    # Filters for orders
        public $projects_category = 4; // from URL ?cat=4&spec=434 
        public $projects_subcategory = 434; // 4 - category; 434 - subcategory

	/*
	***************************************
    * Autorisation
    ***************************************
    */
    public function auth () {

        $postdata = array( 
            'login' => $this->login,  
            'passwd' => $this->password,
            'remember_me' => FALSE,  
            'check_ip' => FALSE,
            'auth' => 'auth',
            'submit' => 'Вход',
            'return_url' => '/login/'
        );  

        $output = $this ->curl (
            $this->url_loginform,
            $postdata,
            TRUE,
            TRUE,
            $this->url_loginform
        );

        preg_match_all(
            '/^Set-Cookie:\s*([^;]*)/mi',
            $output,
            $arr
        );

        $this ->session_cookies .= $arr[1][0].';';

        $output = $this ->curl (
            $this->url_loginform,
            $postdata,
            TRUE,
            TRUE,
            $this->url_loginform,
            'POST'
        );

        if( !$output )
            return FALSE;

        preg_match_all(
            '/^Set-Cookie:\s*([^;]*)/mi',
            $output,
            $arr
        );

        foreach ($arr[1] as $value) {
           $this ->session_cookies .= $value.';';
        }
        //$output = mb_convert_encoding($output, 'utf-8', 'windows-1251');

        return $this ->session_cookies;

    }

    /*
	***************************************
    * Send comment
    ***************************************
    */
    public function send_comment( $ids, $cost, $term, $msg_body, $use_signature, $need_prepay ) {

        $postdata = array( 
            'cost' => $cost,  
            'term' => $term,  
            'msg_body' => mb_convert_encoding($msg_body, 'windows-1251', 'utf-8'),
            'use_signature' => $use_signature,
            'need_prepay' => $need_prepay,
            'cmd' => 'msg_insert',
            'topic' => $ids,
            'in_office' => 0,
        );

        $output = $this ->curl(
            $this ->url_comment.$ids,
            $postdata,
            FALSE,
            TRUE,
            $this ->url_comment.$ids,
            'POST'
        );
        preg_match_all(
            '#<title>(.+?)</title>#',
            $output, 
            $arr
        );

        return $arr[1][0] == '200 OK' ? TRUE : FALSE;
    }

    /*
    ***************************************
    * Get url-adreses for send comment
    * $a = TRUE or FALSE; - scan full pages/scan first page. 
    * If you nessesary scan new orders, you can set FALSE;
    ***************************************
    */
    public function get_ids ( $a=FALSE ) {

        $url = $this ->url_projects;

        if( $this ->projects_category and $this ->projects_subcategory )
            $url = $this ->url_projects.'?cat='.$this ->projects_category.'&spec='.$this ->projects_subcategory;

        if( $this ->projects_category and !$this ->projects_subcategory )
            $url = $this ->url_projects.'?spec='.$this ->projects_category;

        $res[] = $this ->get_url($url);
       
        if( $a ) {

           for ($i=2; $i < 5; $i++) { 
               
                if( !$res )
                    break;
                
                $b = $this ->get_url($url, $i);
                
                if( !$b )
                    break;

                $res[] = $b;

            }   

        }

        if( !$res )
           return FALSE; 

        foreach ($res as $v1) {

            foreach ($v1 as $v2) {

                $output = $this ->curl(
                    $this ->url_projects.'/'.$v2.'.html',
                    FALSE,
                    FALSE,
                    TRUE
                );

                preg_match_all(
                    '#<a.*href="\/discussion\/\?cmd=msg_new&topic=(.+?)\#.*"#',
                    $output, 
                    $arr
                );

                if( isset( $arr[1][0] ) )
                    $r[] = $arr[1][0];

            }

        } 

        return isset( $r ) ? $r : FALSE;

    }

    /*
    ***************************************
    * Get url-adreses
    ***************************************
    */
    public function get_url ( $url=NULL, $page=NULL ) {

        if( !$url )
            return FALSE;

        $output = $this ->curl(
            $page ? $url.'&page='.$i : $url,
            FALSE,
            FALSE,
            TRUE
        );
        preg_match_all(
            '#<a.*href="\/projects\/(.+?).html".*>#',
            $output, 
            $url_arr
        );

        return isset( $url_arr[1][0] ) ? $url_arr[1] : FALSE;
    }

    /*
    ***************************************
    * Curl
    ***************************************
    */
    private function curl ( $url, $postdata=NULL, $header=NULL, $nobody=NULL, $url_referer='', $method=NULL ) {
        
        if( !$method )
            $method = 'GET';

        // CURL initialization

            $ch = curl_init();  
            curl_setopt( 
                $ch, 
                CURLOPT_URL, $url 
            );  
            curl_setopt( 
                $ch, 
                CURLOPT_RETURNTRANSFER, 
                1 
            ); 
/*
            curl_setopt( 
                $ch, 
                CURLOPT_HTTPHEADER, 
                 array(
                    'Accept: text/html; charset=windows-1251',                                 
                )
            ); 
*/
            curl_setopt( 
                $ch, 
                CURLOPT_USERAGENT, 
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36'
            );

            if( $url_referer )
                curl_setopt( 
                    $ch, 
                    CURLOPT_REFERER, 
                    $url_referer 
                );
            
            curl_setopt( 
                $ch, 
                CURLOPT_CONNECTTIMEOUT, 
                30 
            );

            curl_setopt( 
                $ch, 
                CURLOPT_POST, 
                $method == 'POST' ? 
                    TRUE :
                    FALSE 
            );  
            curl_setopt( 
                $ch, 
                CURLOPT_POSTFIELDS, 
                $postdata 
            ); 

            curl_setopt( 
                $ch,
                CURLOPT_SSL_VERIFYPEER,
                FALSE 
            );

            curl_setopt( 
                $ch,
                CURLOPT_COOKIE,  
                $this ->session_cookies
            );

            curl_setopt( 
                $ch, 
                CURLOPT_HEADER, 
                $header ? 
                    TRUE : 
                    FALSE 
            );

            curl_setopt( 
                $ch, 
                CURLOPT_NOBODY, 
                $nobody ? 
                    FALSE : 
                    TRUE
            );
            

        return curl_exec( $ch );

    }

}